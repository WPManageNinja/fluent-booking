<?php

namespace FluentCalendar\App\Http\Controllers;

use FluentCalendar\App\App;
use FluentCalendar\App\Models\Booking;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Services\BookingService;
use FluentCalendar\App\Services\DateTimeHelper;
use FluentCalendar\Framework\Request\Request;
use FluentCalendar\Framework\Support\Arr;

class BookingController extends Controller
{
    public function getSlots(Request $request, $slotId)
    {
        $slot = CalendarSlot::findOrfail($slotId);
        $startDate = $request->get('start_date', date('Y-m-d H:i:s', current_time('timestamp')));
        $timeZone  = $request->get('timezone', 'UTC');

        if(!$timeZone) {
            $timeZone = 'UTC';
        }

        if(strtotime($startDate) < time()) {
            $startDate = date('Y-m-d H:i:s');
        }

        $service = new \FluentCalendar\App\Services\TimeSlotService($slot->calendar, $slot);

        $slots = $service->getDates($startDate);
        $convertedSpots = [];

        if($timeZone == 'UTC') {
            $convertedSpots = $slots;
        } else {
            foreach ($slots as $slot) {
                foreach ($slot as $spot) {
                    $startDate = DateTimeHelper::convertFromUtc($spot['start'], $timeZone, 'Y-m-d');

                    if(!isset($convertedSpots[$startDate])) {
                        $convertedSpots[$startDate] = [];
                    }

                    $convertedSpots[$startDate][] = [
                        'start' => DateTimeHelper::convertFromUtc($spot['start'], $timeZone),
                        'end'   =>  DateTimeHelper::convertFromUtc($spot['end'], $timeZone),
                    ];
                }
            }
        }

        return [
            'available_slots' => $convertedSpots,
            'timezone' => $timeZone
        ];
    }

    public function bookSlot(Request $request, $slotId)
    {
        $calendarSlot = CalendarSlot::findOrfail($slotId);

        $postedData = Arr::only($request->all(), [
            'name', 'email', 'message', 'timezone', 'start_date'
        ]);

        $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
            'timezone' => 'required',
            'start_date' => 'required'
        ], $postedData);

        $startDateTime = DateTimeHelper::convertToUtc($postedData['start_date'], $postedData['timezone']);

        $bookingData = [
            'person_time_zone'  => sanitize_text_field($postedData['timezone']),
            'start_time' => $startDateTime,
            'name' => sanitize_text_field($postedData['name']),
            'email' => sanitize_email($postedData['email']),
            'message' => sanitize_textarea_field( Arr::get($postedData, 'message', '')),
            'ip_address' => $request->getIp()
        ];

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
            'slot' => $calendarSlot,
            'booking' => $booking,
            'message' => 'A confirmation has been sent to your email address.'
        ];

        $responseHtml = App::make('view')->make('public.booking_confirmation', $confirmationData);

        return [
            'message' => 'Booking has been confirmed',
            'response_html' => (string) $responseHtml
        ];
    }
}
