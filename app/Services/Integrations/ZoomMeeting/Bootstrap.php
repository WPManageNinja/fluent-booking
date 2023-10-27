<?php

namespace FluentBooking\App\Services\Integrations\ZoomMeeting;


use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarHelper;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\Framework\Support\Arr;

class Bootstrap
{
    public function register()
    {
        /*
         * Global Settings
         */
        add_filter('fluent_booking/settings_menu_items', [$this, 'addGlobalMenu'], 11, 1);

        /*
        * Calendar Slot
        */
        add_filter('fluent_booking/calendar_setting_menu_items', [$this, 'addConnectMenu'], 10, 2);


        /*
         * Booking Level Hooks
         */
        add_action('fluent_booking/pre_after_booking_scheduled', [$this, 'maybeCreateZoomMeeting'], 10, 2);
        add_action('fluent_booking/booking_schedule_cancelled', [$this, 'maybeCancelZoomMeeting'], 10, 1);
        add_action('fluent_booking/after_booking_rescheduled', [$this, 'maybeRescheduleZoomMeeting'], 10, 1);

        /*
         * Location Hooks
         */
        add_filter('fluent_booking/get_location_fields', function ($fields, $calendar) {
            if (!ZoomHelper::isZoomConfigured($calendar->user_id)) {
                return $fields;
            }

            $fields['conferencing']['options']['zoom_meeting'] = [
                'title'         => __('Zoom Video', 'fluent-booking-pro'),
                'disabled'      => false,
                'location_type' => 'conferencing'
            ];

            return $fields;
        }, 10, 2);
    }

    public function addGlobalMenu($menuItems)
    {
        $app = App::getInstance();
        $menuItems['zoom_meeting'] = [
            'title'          => __('Zoom', 'fluent-booking-pro'),
            'icon_url'       => $app['url.assets'] . 'images/zoom.svg',
            'component_type' => 'StandAloneComponent',
            'route'          => [
                'name' => 'zoom_integrations'
            ]
        ];
        return $menuItems;
    }

    public function addConnectMenu($menuItems, $calendar)
    {
        $menuItems['zoom_meeting'] = [
            'type'    => 'route',
            'route'   => [
                'name' => 'user_zoom_integration'
            ],
            'label'   => __('Zoom Integration', 'fluent-booking-pro'),
            'svgIcon' => '<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 48 48" width="48px" height="48px"><circle cx="24" cy="24" r="20" fill="#2196f3"/><path fill="#fff" d="M29,31H14c-1.657,0-3-1.343-3-3V17h15c1.657,0,3,1.343,3,3V31z"/><polygon fill="#fff" points="37,31 31,27 31,21 37,17"/></svg>'
        ];

        return $menuItems;
    }

    public function maybeCreateZoomMeeting($booking, $calendarSlot)
    {
        if (Arr::get($booking, 'location_details.type') !== 'zoom_meeting') {
            return false; // not our location
        }

        $apiClient = ZoomHelper::getZoomClient($booking->host_user_id);

        if (is_wp_error($apiClient)) {
            return false;
        }

        $bookingMeta = $booking->getMeta('__zoom_meeting_details');
        if ($bookingMeta) {
            return false; // Already created
        }

        if ($booking->event_type == 'group') {
            // Handling Group Meeting
            $bookingExist = Booking::where('group_id', $booking->group_id)
                ->where('status', 'scheduled')
                ->count();

            if ($bookingExist > 1) {
                return $this->updateAttendees($booking);
            }
        }

        // let's prepare the booking data
        $data = apply_filters('fluent_booking/zoom_meeting_data', [
            'agenda'       => $calendarSlot->title,
            'duration'     => 30,
            'type'         => 2,
            'settings'     => [
                'meeting_invitees' => [
                    [
                        'email' => $booking->email
                    ]
                ],
            ],
            'schedule_for' => Arr::get($apiClient, 'origin_email'),
            'start_time'   => date('Y-m-d\TH:i:s\Z', strtotime($booking->start_time)),
            'topic'        => sprintf(__('%1s meeting with %2s', 'fluent-booking-pro'), $calendarSlot->title, trim($booking->first_name . ' ' . $booking->last_name)),
        ], $booking, $calendarSlot);

        $response = $apiClient->createMeeting($data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Zoom API Error', 'fluent-booking-pro'),
                'description' => __(sprintf(__('Failed to create meeting with Zoom API. API Response: %1s', 'fluent-booking-pro'), esc_attr($response->get_error_message())), 'fluent-booking-pro')
            ]);
            return false;
        }

        $responseData = Arr::only($response, ['id', 'start_url', 'join_url', 'password']);
        $booking->updateMeta('__zoom_meeting_details', $responseData);

        $location = $booking->location_details;
        $location['online_platform_link'] = Arr::get($responseData, 'join_url');
        $location['online_platform_start_link'] = Arr::get($responseData, 'start_url');
        $booking->location_details = $location;
        $booking->save();

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Zoom Meeting has been created', 'fluent-booking-pro'),
            'description' => __(sprintf(__('Zoom Meeting has been scheduled. %1s', 'fluent-booking-pro'), '<a target="_blank" href="' . $location['online_platform_start_link'] . '">' . __('Start Meeting URL', 'fluent-booking-pro') . '</a>'), 'fluent-booking-pro')
        ]);

        return true;
    }

    public function maybeCancelZoomMeeting($booking)
    {
        if (Arr::get($booking->location_details, 'type') !== 'zoom_meeting') {
            return false; // not our location
        }

        if ($booking->status != 'cancelled') {
            return false;
        }

        $apiClient = ZoomHelper::getZoomClient($booking->host_user_id);
        if (is_wp_error($apiClient)) {
            return;
        }

        if ($booking->event_type == 'group') {
            $bookingExist = Booking::where('group_id', $booking->group_id)->count();

            if ($bookingExist > 1) {
                $this->updateAttendees($booking);
                return;
            }
        }

        $bookingMeta = $booking->getMeta('__zoom_meeting_details');
        if (!$bookingMeta) {
            return false; // Nothing to cancel as there is no previous record
        }

        $zoomMeetingId = Arr::get($bookingMeta, 'id');

        if (!$zoomMeetingId) {
            return false;
        }

        $response = $apiClient->deleteMeeting($zoomMeetingId);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Zoom API Error', 'fluent-booking-pro'),
                'description' => __('Failed to delete meeting with Zoom API', 'fluent-booking-pro')
            ]);
            return false;
        }

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Zoom Meeting has been deleted', 'fluent-booking-pro'),
            'description' => __('Zoom Meeting has been deleted', 'fluent-booking-pro')
        ]);

        return true;
    }

    public function maybeRescheduleZoomMeeting($updatedBooking)
    {
        if (Arr::get($updatedBooking->location_details, 'type') !== 'zoom_meeting') {
            return false; // not our location
        }

        if (!ZoomHelper::isZoomConfigured($updatedBooking->host_user_id)) {
            return false;
        }

        $data = [
            'start_time' => date('Y-m-d\TH:i:s\Z', strtotime($updatedBooking->start_time))
        ];

        $this->updateZoomMeeting($updatedBooking, $data);
    }

    private function updateAttendees($booking)
    {
        $existingBooking = Booking::where('group_id', $booking->group_id)
            ->where('status', 'scheduled')
            ->first();

        $bookingMeta = $existingBooking->getMeta('__zoom_meeting_details');

        if (!$bookingMeta) {
            return false;
        }

        $attendeesEmails = Booking::where('group_id', $booking->group_id)
            ->where('status', 'scheduled')
            ->pluck('email')
            ->toArray();

        $attendees = [];
        foreach ($attendeesEmails as $email) {
            $attendees[] = ['email' => $email];
        }

        $data = [
            'settings' => [
                'meeting_invitees' => $attendees
            ],
        ];

        $this->updateZoomMeeting($booking, $data);
    }

    public function updateZoomMeeting($booking, $data)
    {
        $bookingMeta = $booking->getMeta('__zoom_meeting_details');

        if (!$bookingMeta) {
            return false; // Nothing to cancel as there is no previous record
        }

        $api = ZoomHelper::getZoomClient($booking->host_user_id);

        if (is_wp_error($api)) {
            return false;
        }


        $meetingId = Arr::get($bookingMeta, 'id');

        if (!$meetingId) {
            return false;
        }

        $response = $api->patchMeeting($meetingId, $data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Zoom API Error', 'fluent-booking-pro'),
                'description' => __('Failed to update meeting with Zoom API', 'fluent-booking-pro')
            ]);
            return false;
        }

        $location = $booking->location_details;
        $location['online_platform_link'] = Arr::get($bookingMeta, 'join_url');
        $location['online_platform_start_link'] = Arr::get($bookingMeta, 'start_url');
        $booking->location_details = $location;
        $booking->save();

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Zoom Meeting has been updated', 'fluent-booking-pro'),
            'description' => __('Zoom Meeting has been updated with the new data', 'fluent-booking-pro')
        ]);

        return true;
    }
}
