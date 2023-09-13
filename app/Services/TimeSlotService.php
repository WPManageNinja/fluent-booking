<?php

namespace FluentCalendar\App\Services;

use FluentCalendar\App\Models\Booking;
use FluentCalendar\App\Models\Calendar;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\Framework\Support\Arr;

class TimeSlotService
{
    protected $calendarSlot;

    protected $calendar;

    public function __construct(Calendar $calendar, CalendarSlot $calendarSlot)
    {
        $this->calendar = $calendar;
        $this->calendarSlot = $calendarSlot;
    }

    public function getDates($fromDate = false, $toDate = false)
    {
        $period = $this->calendarSlot->duration;

        $fromDate = $fromDate ? $fromDate : date('Y-m-d');
        $toDate   = $toDate ? $toDate : date('Y-m-t 23:59:59', strtotime($fromDate));

        $ranges      = $this->getCurrentDateRange($fromDate, $toDate);
        $daySlots    = $this->getWeekDaySlots();
        $bookedSlots = $this->getBookedSlots([$fromDate, $toDate], $this->calendar->author_timezone);

        $timeStamp = DateTimeHelper::getTimestamp($this->calendar->author_timezone);
        $cutOutTimeStamp = $timeStamp + $this->calendarSlot->getCutoutSeconds();
        
        $todayDate = DateTimeHelper::convertToTimeZone(date('Y-m-d'), 'UTC', $this->calendar->author_timezone, 'Y-m-d');
        
        $overrides = Arr::get($this->calendarSlot->settings, 'date_overrides', []);
        
        $rangedValidSlots = [];

        foreach ($ranges as $date) {

            if ($overrides && isset($overrides[$date])) {
                $availableSlots = $this->convertSlotSetsToFlat($overrides[$date], $this->calendar->author_timezone);
            } else {
                $day = strtolower(date('D', strtotime($date)));
                if (empty($daySlots[$day])) {
                    continue;
                }
                $availableSlots = $daySlots[$day];
            }

            if (!$availableSlots) {
                continue;
            }

            $currentBookedSlots = $bookedSlots[$date] ?? [];

            $isToday = $date === $todayDate;

            $validSlots = [];

            foreach ($availableSlots as $start) {
                $end = date('H:i', strtotime($start) + 60 * $period);
                $slot = [
                    'start' => $date . ' ' . $start . ':00',
                    'end'   => $date . ' ' . $end . ':00'
                ];

                if ($isToday && strtotime($slot['start']) < $cutOutTimeStamp) {
                    continue;
                }

                if (!$currentBookedSlots) {
                    $validSlots[] = $slot;
                    continue;
                }

                $startTimeStamp = strtotime($date . ' ' . $start);
                $endTimeStamp   = strtotime($date . ' ' . $end);

                $isSpotAvailable = true;

                foreach ($currentBookedSlots as $bookedSlot) {
                    $bookedStart = strtotime($bookedSlot['start']);
                    $bookedEnd   = strtotime($bookedSlot['end']);

                    if (
                        ($startTimeStamp >= $bookedStart && $startTimeStamp < $bookedEnd) ||
                        ($endTimeStamp > $bookedStart && $endTimeStamp <= $bookedEnd) ||
                        ($startTimeStamp <= $bookedStart && $endTimeStamp > $bookedStart) ||
                        ($startTimeStamp < $bookedEnd && $endTimeStamp >= $bookedEnd)
                    ) {
                        if (!$bookedSlot['remaining']) {
                            $isSpotAvailable = false;
                            break;
                        }
                        $slot['remaining'] = $bookedSlot['remaining'];
                    }
                }

                if ($isSpotAvailable) {
                    $validSlots[] = $slot;
                }
            }

            if ($validSlots) {
                $rangedValidSlots[$date] = $validSlots;
            }
        }

        return $rangedValidSlots;
    }

    public function isSpotAvailable($fromTime, $toTime)
    {
        $fromTime = DateTimeHelper::convertToTimeZone($fromTime, 'UTC', $this->calendar->author_timezone);
        $toTime   = DateTimeHelper::convertToTimeZone($toTime, 'UTC', $this->calendar->author_timezone);

        $fromTimeStamp = strtotime($fromTime);
        $toTimeStamp   = strtotime($toTime);

        $fromTime = date('Y-m-d 00:00:00', $fromTimeStamp);
        $toTime   = date('Y-m-d 23:59:59', $toTimeStamp);

        $slots = $this->getDates($fromTime, $toTime);

        $date = date('Y-m-d', $fromTimeStamp);

        $availableSlots = $slots[$date] ?? [];

        $left  = 0;
        $right = count($availableSlots) - 1;

        while ($left <= $right) {
            $mid = $left + (($right - $left) >> 1);

            $midStartTime = strtotime($availableSlots[$mid]['start']);
            $midEndTime   = strtotime($availableSlots[$mid]['end']);

            if ($fromTimeStamp == $midStartTime && $toTimeStamp == $midEndTime) {
                return true;
            } elseif ($fromTimeStamp > $midStartTime) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        return false;
    }

    protected function getCurrentDateRange($startDate = false, $endDate = false)
    {
        if (!$startDate) {
            $startDate = date('Y-m-d');
        }

        if (!$endDate) {
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        }

        $currentDate = strtotime($startDate);
        $endDate     = strtotime($endDate);
        $oneDay      = 24 * 60 * 60;
        
        $date_array = [];

        while ($currentDate <= $endDate) {
            $date_array[] = date('Y-m-d', $currentDate);
            $currentDate += $oneDay;
        }

        return $date_array;
    }

    protected function getBookedSlots($dateRange, $toTimeZone = false)
    {
        if ($toTimeZone) {
            $dateRange[0] = DateTimeHelper::convertToTimeZone($dateRange[0], $toTimeZone, 'UTC');
            $dateRange[1] = DateTimeHelper::convertToTimeZone($dateRange[1], $toTimeZone, 'UTC');
        }

        $hostIds = $this->calendarSlot->getHostIds();
        $status  = ['pending', 'approved', 'scheduled'];

        $bookings = Booking::whereHas('hosts', function ($query) use ($hostIds) {
            $query->whereIn('user_id', $hostIds);
        })
            ->whereBetween('start_time', $dateRange)
            ->orderBy('start_time', 'ASC')
            ->whereIn('status', $status)
            ->get()
            ->groupBy('event_id');

        $maxBooking = $this->calendarSlot->getMaxBookingPerSlot();
        
        $books = [];
        
        foreach ($bookings as $booking) {

            $booked  = $booking->count();
            $booking = $booking[0];

            $booking->start_time = DateTimeHelper::convertToTimeZone($booking->start_time, 'UTC', $toTimeZone);
            $booking->end_time   = DateTimeHelper::convertToTimeZone($booking->end_time, 'UTC', $toTimeZone);

            $date = date('Y-m-d', strtotime($booking->start_time));

            $books[$date] = $books[$date] ?? [];

            $remaining = 0;

            if ($this->calendarSlot->id == $booking->slot_id) {
                if ($maxBooking > $booked) {
                    $remaining = $maxBooking - $booked;
                }
            }

            $books[$date][] = [
                'slot_id'   => $booking->slot_id,
                'start'     => $booking->start_time,
                'end'       => $booking->end_time,
                'remaining' => $remaining,
            ];
        }

        return apply_filters('fluent_calendar/booked_events', $books, $this->calendarSlot, $dateRange, $toTimeZone);
    }

    protected function getWeekDaySlots()
    {
        $period = $this->calendarSlot->duration;

        $weeklySlots = SanitizeService::weeklySchedules($this->calendarSlot->settings['weekly_schedules'], 'UTC', $this->calendar->author_timezone, false);

        $items = [];

        foreach ($weeklySlots as $weekDay => $weeklySlot) {
            if (!$weeklySlot['enabled'] || empty($weeklySlot['slots'])) {
                continue;
            }

            $slots = $weeklySlot['slots'];

            $items[$weekDay] = $slots;
        }

        $formattedSlots = [];
        // create range of each day slots from $items array above with $period minutes interval
        foreach ($items as $day => $slots) {

            $daySlots = [];

            foreach ($slots as $slot) {
                $start = strtotime($slot['start']);
                $end   = strtotime($slot['end']);

                while ($start < $end) {
                    $daySlots[] = date('H:i', $start);
                    $start += $period * 60;
                }
            }

            if ($daySlots) {
                $formattedSlots[$day] = $daySlots;
            }

        }

        return $formattedSlots;
    }

    protected function convertSlotSetsToFlat($slotSets, $toTimeZone = false)
    {
        $period = $this->calendarSlot->duration;

        $formattedSlots = [];

        foreach ($slotSets as $slot) {

            if ($toTimeZone) {
                $slot['start'] = DateTimeHelper::convertToTimeZone($slot['start'], 'UTC', $toTimeZone, 'H:i');
                $slot['end']   = DateTimeHelper::convertToTimeZone($slot['end'], 'UTC', $toTimeZone, 'H:i');
            }

            $start = strtotime($slot['start']);
            $end   = strtotime($slot['end']);

            while ($start < $end) {
                $formattedSlots[] = date('H:i', $start);
                $start += $period * 60;
            }
        }

        return $formattedSlots;

    }

    public function getAvailableSpots($startDate, $timeZone = 'utc')
    {
        $slot     = $this->calendarSlot;
        $calendar = $this->calendar;

        if (strtotime($startDate) < time()) {
            $startDate = date('Y-m-d H:i:s');
        }

        $eventType      = $slot->event_type;
        $isDisplaySpots = $slot->is_display_spots;
        $maxBooking     = $slot->getMaxBookingPerSlot();
        $endDate        = $slot->getMaxBookableDateTime($startDate);
        $startDate      = $slot->getMinBookableDateTime($startDate);

        if (strtotime($startDate) > strtotime($endDate)) {
            return new \WP_Error('invalid_date_range', __('Invalid date range', 'fluent-calendar'));
        }

        $startDate = DateTimeHelper::convertToTimeZone($startDate, $timeZone, $calendar->author_timezone);
        $endDate   = DateTimeHelper::convertToTimeZone($endDate, $timeZone, $calendar->author_timezone);

        $slots = $this->getDates($startDate, $endDate);

        $convertedSpots = [];

        foreach ($slots as $spots) {

            foreach ($spots as $spot) {

                $startDate = DateTimeHelper::convertToTimeZone($spot['start'], $calendar->author_timezone, $timeZone, 'Y-m-d');

                $convertedSpots[$startDate] = $convertedSpots[$startDate] ?? [];

                $remainingSlots = false;

                if ($isDisplaySpots && $eventType == 'group') {
                    $remainingSlots = Arr::get($spot, 'remaining', $maxBooking);
                }

                $convertedSpots[$startDate][] = [
                    'start'     => DateTimeHelper::convertToTimeZone($spot['start'], $calendar->author_timezone, $timeZone),
                    'end'       => DateTimeHelper::convertToTimeZone($spot['end'], $calendar->author_timezone, $timeZone),
                    'remaining' => $remainingSlots,
                ];
            }
        }

        return $convertedSpots;
    }

}
