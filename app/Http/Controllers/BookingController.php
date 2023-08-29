<?php

namespace FluentCalendar\App\Http\Controllers;

use FluentCalendar\App\App;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Services\BookingService;
use FluentCalendar\App\Services\DateTimeHelper;
use FluentCalendar\App\Services\TimeSlotService;
use FluentCalendar\Framework\Request\Request;
use FluentCalendar\Framework\Support\Arr;

class BookingController extends Controller
{
    public function getSlots(Request $request, $slotId)
    {
        $slot = CalendarSlot::findOrfail($slotId);

        if ($slot->status != 'active') {
            return $this->sendError([
                'message' => 'Sorry, this host is not accepting any new bookings at the moment.'
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
                'message' => 'Sorry, this host is not accepting any new bookings at the moment.'
            ]);
        }

        $postedData = $request->all();

        $rules = [
            'name'       => 'required',
            'email'      => 'required|email',
            'timezone'   => 'required',
            'start_date' => 'required'
        ];

        $isPhoneRequired = BookingService::isPhoneRequired($calendarSlot);
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
                'message' => 'This selected time slot is not available. Maybe someone booked the spot just a few seconds ago.'
            ], 423);
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
            'sub_heading' => sprintf(__('You are scheduled with %s', 'fluent-calendar'), $author['name']),
            'slot'        => $calendarSlot,
            'booking'     => $booking,
            'message'     => 'A confirmation has been sent to your email address along with meeting location details.'
        ];

        $confirmationData = apply_filters('fluent_calendar/booking_confirmation_data', $confirmationData, $booking, $calendarSlot);

        $responseHtml = (string)App::make('view')->make('public.booking_confirmation', $confirmationData);

        return apply_filters('fluent_calendar/booking_confirmation', [
            'message'       => 'Booking has been confirmed',
            'response_html' => $responseHtml
        ]);
    }
}
