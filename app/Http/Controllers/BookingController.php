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

        if($slot->status != 'active') {
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

        if (strtotime($startDate) < time()) {
            $startDate = date('Y-m-d H:i:s');
        }

        $endDate = $slot->getMaxBookableDateTime($startDate);
        $startDate = $slot->getMinBookableDateTime($startDate);

        if(strtotime($startDate) > strtotime($endDate)) {
            return [
                'available_slots' => [],
                'timezone' => $timeZone,
                'invalid_dates' => true,
                'max_lookup_date' => $slot->getMaxLookUpDate(),
            ];
        }

        $startDate = DateTimeHelper::convertToTimeZone($startDate, $timeZone, $calendar->author_timezone);
        $endDate = DateTimeHelper::convertToTimeZone($endDate, $timeZone, $calendar->author_timezone);

        $slotService = new TimeSlotService($calendar, $slot);

        $slots = $slotService->getDates($startDate, $endDate);
        $convertedSpots = [];

        $cutOutTimeStamp = DateTimeHelper::getTimestamp($calendar->author_timezone) + $slot->getCutoutSeconds();

        foreach ($slots as $spots) {
            foreach ($spots as $spot) {

                if($cutOutTimeStamp > strtotime($spot['start'])) {
                    continue;
                }

                $startDate = DateTimeHelper::convertToTimeZone($spot['start'], $calendar->author_timezone, $timeZone, 'Y-m-d');

                if (!isset($convertedSpots[$startDate])) {
                    $convertedSpots[$startDate] = [];
                }

                $convertedSpots[$startDate][] = [
                    'start' => DateTimeHelper::convertToTimeZone($spot['start'], $calendar->author_timezone, $timeZone),
                    'end'   => DateTimeHelper::convertToTimeZone($spot['end'], $calendar->author_timezone, $timeZone),
                ];
            }
        }

        return [
            'available_slots' => array_filter($convertedSpots),
            'timezone'        => $timeZone,
            'max_lookup_date' => $slot->getMaxLookUpDate(),
        ];
    }

    public function bookSlot(Request $request, $slotId)
    {
        $calendarSlot = CalendarSlot::findOrfail($slotId);

        if($calendarSlot->status != 'active') {
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

        $bookingData = [
            'person_time_zone' => sanitize_text_field($postedData['timezone']),
            'start_time'       => $startDateTime,
            'name'             => sanitize_text_field($postedData['name']),
            'email'            => sanitize_email($postedData['email']),
            'message'          => sanitize_textarea_field(Arr::get($postedData, 'message', '')),
            'ip_address'       => $request->getIp()
        ];

        if ($isPhoneRequired) {
            $bookingData['phone'] = sanitize_text_field($postedData['phone']);
        }

        try {
            $booking = BookingService::createBooking($bookingData, $calendarSlot);
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage()
            ]);
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
