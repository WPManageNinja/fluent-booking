<?php

namespace FluentCalendar\App\Services;

use FluentCalendar\App\App;
use FluentCalendar\App\Models\Booking;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\Framework\Support\Arr;

class BookingService
{
    public static function createBooking($data = [], $calendarSlot = null)
    {
        if (empty($data['email']) || empty($data['start_time']) || empty($data['person_time_zone'])) {
            throw new \Exception('Email, Start Time and timezone are required to create a booking', 423);
        }

        if (!$calendarSlot) {
            $calendarSlot = CalendarSlot::findOrFail($data['slot_id']);
        }

        if (empty($data['first_name']) && !empty($data['name'])) {
            $nameArray = explode(' ', trim($data['name']));
            $data['first_name'] = array_shift($nameArray);
            $data['last_name'] = implode(' ', $nameArray);
        }

        $defaults = [
            'slot_id'     => $calendarSlot->id,
            'calendar_id' => $calendarSlot->calendar_id
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

        // check if the time is available or not for this slot

        $timeSlotService = new TimeSlotService($calendarSlot->calendar, $calendarSlot);

        if(!$timeSlotService->isSpotAvailable($bookingData['start_time'], $bookingData['end_time'])) {
            throw new \Exception('This selected time slot is not available. Maybe someone booked the spot just few seconds ago.', 423);
        }

        $locationData = [
            'location_type' => $calendarSlot->location_type,
            'location_heading' => $calendarSlot->location_heading,
            'location_settings' => $calendarSlot->location_settings
        ];

        $bookingData['location_details'] = $locationData;

        $bookingData = apply_filters('fluent_calendar/booking_data', $bookingData, $calendarSlot);

        do_action('fluent_calendar/before_booking', $bookingData, $calendarSlot);

        $booking = Booking::create($bookingData);

        $booking->hosts()->attach($calendarSlot->user_id, [
            'status' => 'confirmed'
        ]);

        do_action('fluent_calendar/after_booking_scheduled', $booking, $calendarSlot, $bookingData);

        return $booking;
    }

    public static function getBookingFields($slot)
    {
        $fields = [
            [
                'type'        => 'text',
                'name'        => 'name',
                'label'       => __('Your Name', 'fluent-calendar'),
                'required'    => true,
                'placeholder' => __('Your Full Name', 'fluent-calendar'),
                'input_class' => 'fcal_input'
            ],
            [
                'type'        => 'email',
                'name'        => 'email',
                'label'       => __('Your Email Address', 'fluent-calendar'),
                'required'    => true,
                'placeholder' => __('Your Email Address', 'fluent-calendar'),
                'input_class' => 'fcal_input',
                'disabled'    => is_user_logged_in()
            ]
        ];

        if (self::isPhoneRequired($slot)) {
            $fields[] = [
                'type'        => 'tel',
                'name'        => 'phone',
                'label'       => __('Your Phone Number', 'fluent-calendar'),
                'required'    => true,
                'placeholder' => esc_attr__('Phone Number with country code', 'fluent-calendar'),
                'input_class' => 'fcal_input'
            ];
        }

        $fields[] = [
            'type'        => 'textarea',
            'data_type'   => 'textarea',
            'name'        => 'message',
            'label'       => __('Please share anything that will help prepare for our meeting.', 'fluent-calendar'),
            'placeholder' => __('Note about this meeting', 'fluent-calendar'),
            'input_class' => 'fcal_input fcal_textarea'
        ];

        return $fields;
    }

    public static function isPhoneRequired($slot)
    {
        return $slot->location_type == 'phone' && $slot->location_settings['call_type'] == 'outbound';
    }

    public static function getBookingConfirmationHtml($booking, $calendarSlot = null, $withActions = false)
    {
        if (!$calendarSlot) {
            $calendarSlot = $booking->slot;
        }

        $author = $calendarSlot->getAuthorProfile(true);

        $confirmationData = [
            'author'       => $author,
            'sub_heading'  => sprintf(__('You are scheduled with %s', 'fluent-calendar'), $author['name']),
            'slot'         => $calendarSlot,
            'booking'      => $booking,
            'message'      => 'A confirmation has been sent to your email address along with meeting location details.',
            'with_actions' => $withActions
        ];

        return (string)App::make('view')->make('public.booking_confirmation', $confirmationData);
    }

}
