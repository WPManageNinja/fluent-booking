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
            $data['slot_minutes'] = $calendarSlot->duration;
        }

        if (empty($data['end_time'])) {
            $data['end_time'] = gmdate('Y-m-d H:i:s', strtotime($data['start_time']) + ($data['slot_minutes'] * 60));
        }

        if (!isset($data['person_user_id'])) {
            $user = get_user_by('email', $data['email']);
            if ($user) {
                $data['person_user_id'] = $user->ID;
                if (empty($data['first_name'])) {
                    $data['first_name'] = $user->first_name;
                    $data['last_name'] = $user->last_name;
                }
            }
        }

        if (empty($data['location_details'])) {
            $data['location_details'] = LocationService::getLocationDetails($calendarSlot, [], []);
        }

        $additionalGuests = Arr::get($data, 'additional_guests', []);

        $bookingData = Arr::only(wp_parse_args($data, $defaults), (new Booking())->getFillable());

        if ($calendarSlot->event_type == 'group') {
            $event = Booking::select('group_id')
                ->where('event_id', $calendarSlot->id)
                ->where('calendar_id', $calendarSlot->calendar_id)
                ->where('start_time', $bookingData['start_time'])
                ->first();

            $bookingData['group_id'] = $event ? $event->group_id : null;
        }

        $bookingData['event_type'] = $calendarSlot->event_type;

        $bookingData = apply_filters('fluent_booking/booking_data', $bookingData, $calendarSlot, $customFieldsData);

        if (is_wp_error($bookingData)) {
            return $bookingData;
        }

        do_action('fluent_booking/before_booking', $bookingData, $calendarSlot);

        $booking = Booking::create($bookingData);

        if ($customFieldsData) {
            Helper::updateBookingMeta($booking->id, 'custom_fields_data', $customFieldsData);
        }

        if ($additionalGuests) {
            Helper::updateBookingMeta($booking->id, 'additional_guests', $additionalGuests);
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

        $meetingTitle = $booking->getMeetingTitle();

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
                'content' => '<ul class="fcal_listed"><li class="fcal_host_name">' . $author['name'] . '<span class="fcal_host_badge">' . __('Host', 'fluent-booking-pro') . '</span></li><li class="fcal_guest_name">' . $guestName . '</li></ul>'
            ],
            'where' => [
                'title'   => __('Where', 'fluent-booking-pro'),
                'content' => $booking->getLocationDetailsHtml()
            ]
        ];

        if ($guests = $booking->getAdditionalGuests(true)) {
            $sections['guests'] = [
                'title'   => __('Additional Guests', 'fluent-booking-pro'),
                'content' => $guests
            ];
        }

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

        $customFieldsData = $booking->getCustomFormData(true);

        foreach ($customFieldsData as $dataKey => $data) {
            if (!empty($data['value'])) {   
                $sections[$dataKey] = [
                    'title'   => $data['label'],
                    'content' => $data['value']
                ];
            }
        }

        $subHeading = '';
        if ($booking->status == 'scheduled') {
            $subHeading = sprintf(__('You are scheduled with %s', 'fluent-booking-pro'), $author['name']);
        }

        switch ($booking->status) {
            case 'cancelled':
                $bookingStatus = __('cancelled', 'fluent-booking-pro');
                break;
            case 'rescheduled':
                $bookingStatus = __('rescheduled', 'fluent-booking-pro');
                break;
            case 'scheduled':
                $bookingStatus = __('scheduled', 'fluent-booking-pro');
                break;
            default:
                $bookingStatus = $booking->status;
                break;
        }

        $confirmationData = [
            'author'      => $author,
            'title'       => sprintf(__('Your meeting has been %s', 'fluent-booking-pro'), $bookingStatus),
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


        if ($booking->status == 'scheduled' && $actionType == 'confirmation') {
            $assetsUrl = App::getInstance('url.assets');
            $confirmationData['bookmarks'] = apply_filters('fluent_booking/meeting_bookmarks', [
                'google'   => [
                    'title' => __('Google Calendar', 'fluent-booking-pro'),
                    'url'   => add_query_arg([
                        'dates'    => gmdate('Ymd\THis\Z', strtotime($booking->start_time)) . '/' . gmdate('Ymd\THis\Z', strtotime($booking->end_time)),
                        'text'     => $meetingTitle,
                        'details'  => $booking->title,
                        'location' => urlencode(LocationService::getBookingLocationUrl($booking)),
                    ], 'https://calendar.google.com/calendar/r/eventedit'),
                    'icon'  => $assetsUrl . 'images/g-icon.svg'
                ],
                'outlook'  => [
                    'title' => __('Outlook', 'fluent-booking-pro'),
                    'url'   => add_query_arg([
                        'startdt'  => gmdate('Ymd\THis\Z', strtotime($booking->start_time)),
                        'enddt'    => gmdate('Ymd\THis\Z', strtotime($booking->end_time)),
                        'subject'  => $meetingTitle,
                        'path'     => '/calendar/action/compose',
                        'body'     => $booking->title,
                        'rru'      => 'addevent',
                        'location' => urlencode(LocationService::getBookingLocationUrl($booking)),
                    ], 'https://outlook.live.com/calendar/0/deeplink/compose'),
                    'icon'  => $assetsUrl . 'images/ol-icon.svg'
                ],
                'msoffice' => [
                    'title' => __('Microsoft Office', 'fluent-booking-pro'),
                    'url'   => add_query_arg([
                        'startdt'  => gmdate('Ymd\THis\Z', strtotime($booking->start_time)),
                        'enddt'    => gmdate('Ymd\THis\Z', strtotime($booking->end_time)),
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
        $meetingTitle = $booking->getMeetingTitle();

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
        $icsContent .= "DTSTART:" . gmdate('Ymd\THis\Z', strtotime($booking->start_time)) . "\r\n";
        $icsContent .= "DTEND:" . gmdate('Ymd\THis\Z', strtotime($booking->end_time)) . "\r\n";

        $icsContent .= "LOCATION:" . wp_kses_post(LocationService::getBookingLocationUrl($booking)) . "\r\n";

        $icsContent .= "END:VEVENT\r\n";

        // Close the VCALENDAR component
        $icsContent .= "END:VCALENDAR\r\n";

        return $icsContent;
    }

}
