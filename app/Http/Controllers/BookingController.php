<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\App;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\BookingService;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\TimeSlotService;
use FluentBooking\Framework\Request\Request;
use FluentBooking\Framework\Support\Arr;

class BookingController extends Controller
{
    public function getSlots(Request $request, $slotId)
    {
        $slot = CalendarSlot::findOrfail($slotId);

        if ($slot->status != 'active') {
            return $this->sendError([
                'message' => __('Sorry, this host is not accepting any new bookings at the moment.', 'fluent-booking')
            ]);
        }

        $calendar = $slot->calendar;
        $startDate = $request->get('start_date', date('Y-m-d H:i:s'));
        $timeZone = $request->get('timezone', 'UTC');

        if (!$timeZone) {
            $timeZone = 'UTC';
        }

        $timeSlotService = new TimeSlotService($calendar, $slot);

        $availableSpots = $timeSlotService->getAvailableSpots($startDate, $timeZone);

        if(is_wp_error($availableSpots)) {
            return [
                'available_slots' => [],
                'timezone'        => $timeZone,
                'invalid_dates'   => true,
                'max_lookup_date' => $slot->getMaxLookUpDate(),
            ];
        }

        return [
            'available_slots' => array_filter($availableSpots),
            'timezone'        => $timeZone,
            'max_lookup_date' => $slot->getMaxLookUpDate(),
        ];
    }

    public function bookSlot(Request $request, $slotId)
    {
        $calendarSlot = CalendarSlot::findOrfail($slotId);

        if ($calendarSlot->status != 'active') {
            return $this->sendError([
                'message' => __('Sorry, this host is not accepting any new bookings at the moment.', 'fluent-booking')
            ]);
        }

        $postedData = $request->all();

        $rules = [
            'name'       => 'required',
            'email'      => 'required|email',
            'timezone'   => 'required',
            'start_date' => 'required'
        ];

        $isPhoneRequired = $calendarSlot->isPhoneRequired();
        if ($isPhoneRequired) {
            $rules['phone'] = 'required';
        }

        $this->validate($postedData, $rules);

        $startDateTime = DateTimeHelper::convertToUtc($postedData['start_date'], $postedData['timezone']);
        $endDateTime   = date('Y-m-d H:i:s', strtotime($startDateTime) + ($calendarSlot->duration * 60));

        $bookingData = [
            'person_time_zone' => sanitize_text_field($postedData['timezone']),
            'start_time'       => $startDateTime,
            'name'             => sanitize_text_field($postedData['name']),
            'email'            => sanitize_email($postedData['email']),
            'message'          => sanitize_textarea_field(Arr::get($postedData, 'message', '')),
            'ip_address'       => $request->getIp(),
        ];

        $sourceUrl = Arr::get($postedData, 'source_url', '');

        if ($sourceUrl) {
            $bookingData['source_url'] = sanitize_url($sourceUrl);
        }

        if ($isPhoneRequired) {
            $bookingData['phone'] = sanitize_text_field($postedData['phone']);
        }

        // Check if the time is available or not for this slot
        $timeSlotService = new TimeSlotService($calendarSlot->calendar, $calendarSlot);
        $isSpotAvailable = $timeSlotService->isSpotAvailable($startDateTime, $endDateTime);

        if (!$isSpotAvailable) {
            wp_send_json([
                'message' => __('This selected time slot is not available. Maybe someone booked the spot just a few seconds ago.', 'fluent-booking')
            ], 422);
        }

        try {
            $booking = BookingService::createBooking($bookingData, $calendarSlot);
        } catch (\Exception $e) {
            wp_send_json([
                'message' => $e->getMessage()
            ], $e->getCode());
        }

        $author = $calendarSlot->getAuthorProfile(true);

        $confirmationData = [
            'sub_heading' => sprintf(__('You are scheduled with %s', 'fluent-booking'), $author['name']),
            'slot'        => $calendarSlot,
            'booking'     => $booking,
            'message'     => __('A confirmation has been sent to your email address along with meeting location details.', 'fluent-booking')
        ];

        $confirmationData = apply_filters('fluent_booking/booking_confirmation_data', $confirmationData, $booking, $calendarSlot);

        $responseHtml = (string)App::make('view')->make('public.booking_confirmation', $confirmationData);

        return apply_filters('fluent_booking/booking_confirmation', [
            'message'       => __('Booking has been confirmed', 'fluent-booking'),
            'response_html' => $responseHtml
        ]);
    }
}
