<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\Framework\Support\Arr;

class BookingService
{
    public static function createBooking($data = [], $calendarSlot = null)
    {
        if (empty($data['email']) || empty($data['start_time']) || empty($data['person_time_zone'])) {
            throw new \Exception('Email, Start Time and timezone are required to create a booking', 423);
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
            'event_id'    => $calendarSlot->id,
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

        $locationData = [
            'type'              => Arr::get($calendarSlot->location_settings, '0.type'),
            'title'             => Arr::get($calendarSlot->location_settings, '0.title'),
            'host_phone_number' => Arr::get($calendarSlot->location_settings, '0.host_phone_number'),
            'description'       => Arr::get($calendarSlot->location_settings, '0.description')
        ];

        $bookingData['location_details'] = $locationData;

        $event = Booking::select('group_id')
            ->where('event_id', $calendarSlot->id)
            ->where('calendar_id', $calendarSlot->calendar_id)
            ->where('start_time', $bookingData['start_time'])
            ->first();

        $bookingData['group_id'] = $event ? $event->group_id : null;

        $bookingData = apply_filters('fluent_booking/booking_data', $bookingData, $calendarSlot);

        do_action('fluent_booking/before_booking', $bookingData, $calendarSlot);

        $booking = Booking::create($bookingData);

        $booking->hosts()->attach($calendarSlot->user_id, [
            'status' => 'confirmed'
        ]);

        $booking->load('calendar');

        do_action('fluent_booking/after_booking_' . $booking->status, $booking, $calendarSlot, $bookingData);

        $paymentMethod = Arr::get($data, 'payment_method', 'stripe');
        if ($calendarSlot->type === 'paid' && $paymentMethod) {
            //make draft orders
            (new OrderHelper())->processDraftOrder($booking, $calendarSlot);
            do_action('fluent_booking/payment/pay_order_with_' . sanitize_text_field($paymentMethod), $booking, $calendarSlot);
        }

        return $booking;
    }

    public static function getBookingConfirmationHtml($booking, $calendarSlot = null, $withActions = false)
    {
        if (!$calendarSlot) {
            $calendarSlot = $booking->slot;
        }

        $author = $calendarSlot->getAuthorProfile(true);

        $confirmationData = [
            'author'       => $author,
            'sub_heading'  => sprintf(__('You are scheduled with %s', 'fluent-booking'), $author['name']),
            'slot'         => $calendarSlot,
            'booking'      => $booking,
            'message'      => 'A confirmation has been sent to your email address along with meeting location details.',
            'with_actions' => $withActions
        ];

        return (string)App::make('view')->make('public.booking_confirmation', $confirmationData);
    }

}
