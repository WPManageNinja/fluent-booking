<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\Framework\Support\Arr;

class BookingService
{
    public static function createBooking($data = [], $calendarSlot = null, $customFieldsData = [])
    {
        if (empty($data['email']) || empty($data['start_time']) || empty($data['person_time_zone'])) {
            throw new \Exception('Email, Start Time and timezone are required to create a booking', 422);
        }

        if (!$calendarSlot) {
            $calendarSlot = CalendarSlot::findOrFail($data['event_id']);
        }

        if (empty($data['first_name']) && !empty($data['name'])) {
            $nameArray = explode(' ', trim($data['name']));
            $data['first_name'] = array_shift($nameArray);
            $data['last_name'] = implode(' ', $nameArray);
        }

        $defaults = [
            'event_id'     => $calendarSlot->id,
            'calendar_id'  => $calendarSlot->calendar_id,
            'host_user_id' => $calendarSlot->user_id,
        ];

        if (empty($data['slot_minutes'])) {
            $defaults['slot_minutes'] = $calendarSlot->duration;
        }

        if (empty($data['end_time'])) {
            $defaults['end_time'] = date('Y-m-d H:i:s', strtotime($data['start_time']) + ($calendarSlot->duration * 60));
        }

        if (!isset($data['person_user_id'])) {
            $userId = get_current_user_id();

            if ($userId) {
                $user = get_user_by('ID', $userId);
            } else {
                $user = get_user_by('email', $data['email']);
            }

            if ($user) {
                $data['person_user_id'] = $userId;
                $data['email'] = $user->user_email;

                if (empty($data['first_name'])) {
                    $data['first_name'] = $user->first_name;
                    $data['last_name'] = $user->last_name;
                }
            }
        }

        $bookingData = Arr::only(wp_parse_args($data, $defaults), (new Booking())->getFillable());

        $bookingAddress = Arr::get($data, 'address');

        $bookingData['location_details'] = LocationService::getLocationDetails($calendarSlot->location_settings, $bookingAddress);

        $event = Booking::select('group_id')
            ->where('event_id', $calendarSlot->id)
            ->where('calendar_id', $calendarSlot->calendar_id)
            ->where('start_time', $bookingData['start_time'])
            ->first();

        $bookingData['group_id'] = $event ? $event->group_id : null;

        $bookingData = apply_filters('fluent_booking/booking_data', $bookingData, $calendarSlot, $customFieldsData);

        if (is_wp_error($bookingData)) {
            return $bookingData;
        }

        do_action('fluent_booking/before_booking', $bookingData, $calendarSlot);

        $booking = Booking::create($bookingData);

        if ($customFieldsData) {
            Helper::updateBookingMeta($booking->id, 'custom_fields_data', $customFieldsData);
        }

        $booking->hosts()->attach($calendarSlot->user_id, [
            'status' => 'confirmed'
        ]);

        $booking->load('calendar');

        // this pre hook is for early actions that require for remote calendars and locations
        do_action('fluent_booking/pre_after_booking_' . $booking->status, $booking, $calendarSlot, $bookingData);

        // We are just renewing this as this may have been changed by the pre hook
        $booking = Booking::find($booking->id);
        do_action('fluent_booking/after_booking_' . $booking->status, $booking, $calendarSlot, $bookingData);

        return $booking;
    }

    public static function getBookingConfirmationHtml(Booking $booking, $actionType = 'confirmation')
    {
        $validActions = [
            'confirmation',
            'cancel',
            'reschedule'
        ];

        if (!in_array($actionType, $validActions)) {
            $actionType = 'confirmation';
        }

        $calendarSlot = $booking->calendar_event;

        $author = $calendarSlot->getAuthorProfile(true);

        $guestName = trim($booking->first_name . ' ' . $booking->last_name);

        $sections = [
            'what'  => [
                'title'   => __('What', 'fluent-booking'),
                'content' => sprintf('%1s Meeting between %2s and %3s', $calendarSlot->title, $guestName, $author['name'])
            ],
            'when'  => [
                'title'   => __('When', 'fluent-booking'),
                'content' => $booking->getFullBookingDateTimeText($booking->person_time_zone)
            ],
            'who'   => [
                'title'   => __('Who', 'fluent-booking'),
                'content' => '<span class="fcal_host_name">' . $author['name'] . '<span class="fcal_host_badge">Host</span></span><span class="fcal_guest_name">' . $guestName . '</span>'
            ],
            'where' => [
                'title'   => __('Where', 'fluent-booking'),
                'content' => $booking->getLocationDetailsHtml()
            ]
        ];

        if ($booking->message) {
            $sections['note'] = [
                'title'   => __('Additional Note', 'fluent-booking'),
                'content' => wpautop($booking->message)
            ];
        }

        $confirmationData = [
            'author'      => $author,
            'title'       => __(sprintf('Your meeting has been %s', $booking->status), 'fluent-booking'),
            'sub_heading' => sprintf(__('You are scheduled with %s', 'fluent-booking'), $author['name']),
            'sections'    => $sections,
            'slot'        => $calendarSlot,
            'booking'     => $booking,
            'message'     => 'A confirmation has been sent to your email address along with meeting location details.',
            'action_type' => $actionType
        ];

        if ($actionType == 'cancel') {
            $confirmationData['action_url'] = add_query_arg([
                'action'       => 'fcal_cancel_meeting',
                'meeting_hash' => $booking->hash,
                'scope'        => Arr::get($_REQUEST, 'scope')
            ], admin_url('admin-ajax.php'));
        }

        return (string)App::make('view')->make('public.booking_confirmation', $confirmationData);
    }

}
