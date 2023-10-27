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
            throw new \Exception(__('Email, Start Time and timezone are required to create a booking', 'fluent-booking-pro'), 422);
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
                if(empty($data['email'])) {
                    $data['email'] = $user->user_email;
                }
                if (empty($data['first_name'])) {
                    $data['first_name'] = $user->first_name;
                    $data['last_name'] = $user->last_name;
                }
            }
        }

        if(empty($data['location_details'])) {
            $data['location_details'] = LocationService::getLocationDetails($calendarSlot, [], []);
        }

        $bookingData = Arr::only(wp_parse_args($data, $defaults), (new Booking())->getFillable());

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

        $author = $booking->getHostDetails(false);

        $guestName = trim($booking->first_name . ' ' . $booking->last_name);

        $meetingTitle = sprintf(__('%1s Meeting between %2s and %3s', 'fluent-booking-pro'), $calendarSlot->title, $guestName, $author['name']);

        $sections = [
            'what'  => [
                'title'   => __('What', 'fluent-booking-pro'),
                'content' => $meetingTitle
            ],
            'when'  => [
                'title'   => __('When', 'fluent-booking-pro'),
                'content' => $booking->getFullBookingDateTimeText($booking->person_time_zone, true) . ' (' . $booking->person_time_zone . ')'
            ],
            'who'   => [
                'title'   => __('Who', 'fluent-booking-pro'),
                'content' => '<ul class="fcal_listed"><li class="fcal_host_name">' . $author['name'] . '<span class="fcal_host_badge">'.__('Host', 'fluent-booking-pro').'</span></li><li class="fcal_guest_name">' . $guestName . '</li></ul>'
            ],
            'where' => [
                'title'   => __('Where', 'fluent-booking-pro'),
                'content' => $booking->getLocationDetailsHtml()
            ]
        ];

        if ($booking->status == 'cancelled') {
            // add cancellation reason at the beginning
            $sections = array_merge([
                'cancellation_reason' => [
                    'title'   => __('Cancellation Reason', 'fluent-booking-pro'),
                    'content' => $booking->getCancelReason(true)
                ]
            ], $sections);
        }

        if ($booking->message) {
            $sections['note'] = [
                'title'   => __('Additional Note', 'fluent-booking-pro'),
                'content' => wpautop($booking->message)
            ];
        }

        $subHeading = '';
        if ($booking->status == 'scheduled') {
            $subHeading = sprintf(__('You are scheduled with %s', 'fluent-booking-pro'), $author['name']);
        }

        $confirmationData = [
            'author'      => $author,
            'title'       => __(sprintf('Your meeting has been %s', $booking->status), 'fluent-booking-pro'),
            'sub_heading' => $subHeading,
            'sections'    => $sections,
            'slot'        => $calendarSlot,
            'booking'     => $booking,
            'message'     => __('A confirmation has been sent to your email address along with meeting location details.', 'fluent-booking-pro'),
            'action_type' => $actionType,
            'can_cancel'  => $booking->canCancel(),
            'bookmarks'   => [],
            'extra_html'  => ''
        ];

        if ($booking->payment_status) {
            $confirmationData['extra_html'] = EditorShortCodeParser::parse('{{payment.receipt_html}}', $booking);
        }


        if ($booking->canCancel()) {
            $confirmationData['action_url'] = add_query_arg([
                'action'       => 'fcal_cancel_meeting',
                'meeting_hash' => $booking->hash,
                'scope'        => Arr::get($_REQUEST, 'scope')
            ], admin_url('admin-ajax.php'));
        }


        if ($booking->status == 'scheduled') {
            $assetsUrl = App::getInstance('url.assets');
            $confirmationData['bookmarks'] = apply_filters('fluent_booking/meeting_bookmarks', [
                'google'   => [
                    'title' => __('Google Calendar', 'fluent-booking-pro'),
                    'url'   => add_query_arg([
                        'dates'    => date('Ymd\THis\Z', strtotime($booking->start_time)) . '/' . date('Ymd\THis\Z', strtotime($booking->end_time)),
                        'text'     => $meetingTitle,
                        'details'  => $booking->title,
                        'location' => urlencode(LocationService::getBookingLocationUrl($booking)),
                    ], 'https://calendar.google.com/calendar/r/eventedit'),
                    'icon'  => $assetsUrl . 'images/google-icon.svg'
                ],
                'outlook'  => [
                    'title' => __('Outlook', 'fluent-booking-pro'),
                    'url'   => add_query_arg([
                        'startdt'  => date('Ymd\THis\Z', strtotime($booking->start_time)),
                        'enddt'    => date('Ymd\THis\Z', strtotime($booking->end_time)),
                        'subject'  => $meetingTitle,
                        'path'     => '/calendar/action/compose',
                        'body'     => $booking->title,
                        'rru'      => 'addevent',
                        'location' => urlencode(LocationService::getBookingLocationUrl($booking)),
                    ], 'https://outlook.live.com/calendar/0/deeplink/compose'),
                    'icon'  => $assetsUrl . 'images/outlook.svg'
                ],
                'msoffice' => [
                    'title' => __('Microsoft Office', 'fluent-booking-pro'),
                    'url'   => add_query_arg([
                        'startdt'  => date('Ymd\THis\Z', strtotime($booking->start_time)),
                        'enddt'    => date('Ymd\THis\Z', strtotime($booking->end_time)),
                        'subject'  => $meetingTitle,
                        'path'     => '/calendar/action/compose',
                        'body'     => $booking->title,
                        'rru'      => 'addevent',
                        'location' => urlencode(LocationService::getBookingLocationUrl($booking)),
                    ], 'https://outlook.office.com/calendar/0/deeplink/compose'),
                    'icon'  => $assetsUrl . 'images/msoffice.svg'
                ],
                'other'    => [
                    'title' => __('Other Calendar', 'fluent-booking-pro'),
                    'url'   => $booking->getIcsDownloadUrl(),
                    'icon'  => $assetsUrl . 'images/ics.svg'
                ]
            ], $booking);
        }

        $confirmationData = apply_filters('fluent_booking/schedule_receipt_data', $confirmationData, $booking);

        return (string)App::make('view')->make('public.booking_confirmation', $confirmationData);
    }

    public static function generateBookingICS(Booking $booking)
    {
        $host = $booking->getHostDetails(false);
        $meetingTitle = sprintf(__('%1s Meeting between %2s and %3s', 'fluent-booking-pro'), esc_html($booking->calendar_event->title), esc_html(trim($booking->first_name . ' ' . $booking->last_name)), esc_attr($host['name']));

        // Initialize the ICS content
        $icsContent = "BEGIN:VCALENDAR\r\n";
        $icsContent .= "VERSION:2.0\r\n";
        $icsContent .= "PRODID:-//Your Organization//Your Application//EN\r\n";

        $icsContent .= "BEGIN:VEVENT\r\n";
        $icsContent .= "UID:" . md5($booking->hash) . "\r\n"; // Unique ID for the event

        // Event details
        $icsContent .= "SUMMARY:" . esc_html($booking->calendar_event->title) . "\r\n";
        $icsContent .= "DESCRIPTION:" . $meetingTitle . "\r\n";

        // Date and time formatting (assuming eventStart and eventEnd are DateTime objects)
        $icsContent .= "DTSTART:" . date('Ymd\THis\Z', strtotime($booking->start_time)) . "\r\n";
        $icsContent .= "DTEND:" . date('Ymd\THis\Z', strtotime($booking->end_time)) . "\r\n";

        $icsContent .= "LOCATION:" . wp_kses_post(LocationService::getBookingLocationUrl($booking)) . "\r\n";

        $icsContent .= "END:VEVENT\r\n";

        // Close the VCALENDAR component
        $icsContent .= "END:VCALENDAR\r\n";

        return $icsContent;
    }

}
