<?php

namespace FluentCalendar\App\Services;

use FluentCalendar\App\Models\Booking;
use FluentCalendar\App\Models\Calendar;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\Framework\Support\Arr;

class TimeSlotService
{
    protected $calenderSlot;

    protected $calendar;

    public function __construct(Calendar $calendar, CalendarSlot $calenderSlot)
    {
        $this->calendar = $calendar;
        $this->calenderSlot = $calenderSlot;
    }

    public function getDates($fromDate = false, $toDate = false)
    {
        $period = $this->calenderSlot->duration;

        $fromDate = $fromDate ? $fromDate : date('Y-m-d');
        $toDate = $toDate ? $toDate : date('Y-m-t 23:59:59', strtotime($fromDate));

        $ranges = $this->getCurrentDateRange($fromDate, $toDate);

        $daySlots = $this->getWeekDaySlots();
        $bookedSlots = $this->getBookedSlots([$fromDate, $toDate], $this->calendar->author_timezone);

        $rangedValidSlots = [];

        $fromValidTimeStamp = time() + 60 * 60 * 3; // 3 hours after now

        $todayDate = DateTimeHelper::convertToTimeZone(date('Y-m-d'), 'UTC', $this->calendar->author_timezone, 'Y-m-d');

        $overrides = Arr::get($this->calenderSlot->settings, 'date_overrides', []);

        foreach ($ranges as $date) {

            if($overrides && isset($overrides[$date])) {
                $availableSlots = $this->convertSlotSetsToFlat($overrides[$date], $this->calendar->author_timezone);
            } else {
                $day = strtolower(date('D', strtotime($date)));
                if (empty($daySlots[$day])) {
                    continue;
                }

                $availableSlots = $daySlots[$day];
            }

            if(!$availableSlots) {
                continue;
            }

            $currentBookedSlots = [];

            if (!empty($bookedSlots[$date])) {
                $currentBookedSlots = $bookedSlots[$date];
            }

            $isToday = $date === $todayDate;

            $validSlots = [];
            foreach ($availableSlots as $start) {
                $end = date('H:i', strtotime($start) + 60 * $period);
                $slot = [
                    'start' => $date . ' ' . $start . ':00',
                    'end'   => $date . ' ' . $end . ':00'
                ];

                if ($isToday && strtotime($slot['start']) < $fromValidTimeStamp) {
                    continue;
                }

                if (!$currentBookedSlots) {
                    $validSlots[] = $slot;
                    continue;
                }

                $startTimeStamp = strtotime($date . ' ' . $start);
                $endTimeStamp = strtotime($date . ' ' . $end);

                $isBooked = false;
                foreach ($currentBookedSlots as $bookedSlot) {
                    if (
                        (
                            $startTimeStamp >= strtotime($bookedSlot['start']) && $startTimeStamp < strtotime($bookedSlot['end'])
                        )
                        ||
                        (
                            $endTimeStamp > strtotime($bookedSlot['start']) && $endTimeStamp <= strtotime($bookedSlot['end'])
                        )
                    ) {
                        $isBooked = true;
                        break;
                    }
                }

                if (!$isBooked) {
                    $validSlots[] = $slot;
                }
            }

            if($validSlots) {
                $rangedValidSlots[$date] = $validSlots;
            }
        }

        return $rangedValidSlots;
    }

    public function isSpotAvailable($fromDate, $toDate)
    {
        $fromDate = DateTimeHelper::convertToTimeZone($fromDate, 'UTC', $this->calendar->author_timezone);
        $toDate = DateTimeHelper::convertToTimeZone($toDate, 'UTC', $this->calendar->author_timezone);

        $slots = $this->getDates($fromDate, $toDate);

        $start = null;
        $end = null;

        foreach ($slots as $spots) {

            if(!$spots) {
                continue;
            }

            $first  = array_shift($spots);
            $start = $first['start'];
            $end = $first['end'];

            if(!$spots) {
                if(strtotime($fromDate) >= strtotime($start) && strtotime($toDate) <= strtotime($end)) {
                    return true;
                }
                continue;
            }

            foreach ($spots as $spot) {
                if($spot['start'] == $end) {
                    $end = $spot['end'];
                } else {
                    if(strtotime($fromDate) >= strtotime($start) && strtotime($toDate) <= strtotime($end)) {
                        return true;
                    }
                    $start = $spot['start'];
                    $end = $spot['end'];
                }
            }
        }

        if ($start && $end && strtotime($fromDate) >= strtotime($start) && strtotime($toDate) <= strtotime($end)) {
            return true;
        }

        return false;
    }

    protected function getCurrentDateRange($startDate = false, $endDate = false)
    {
        $today = new \DateTime($startDate);

        if (!$endDate) {
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        }

        $end_of_month = new \DateTime($endDate);

        $interval = new \DateInterval('P1D'); // P1D means a period of 1 day
        $date_range = new \DatePeriod($today, $interval, $end_of_month);
        $date_array = array();
        foreach ($date_range as $date) {
            $date_array[] = $date->format('Y-m-d');
        }

        return $date_array;
    }

    protected function getBookedSlots($dateRange, $toTimeZone = false)
    {
        $bookings = Booking::where('slot_id', $this->calenderSlot->id)
            ->whereBetween('start_time', $dateRange)
            ->orderBy('start_time', 'ASC')
            ->whereIn('status', ['pending', 'approved', 'scheduled', 'completed'])
            ->get();

        $books = [];

        foreach ($bookings as $booking) {
            $booking->start_time = DateTimeHelper::convertToTimeZone($booking->start_time, 'UTC', $toTimeZone);
            $booking->end_time = DateTimeHelper::convertToTimeZone($booking->end_time, 'UTC', $toTimeZone);

            $date = date('Y-m-d', strtotime($booking->start_time));

            if (!isset($books[$date])) {
                $books[$date] = [];
            }

            $books[$date][] = [
                'start' => $booking->start_time,
                'end'   => $booking->end_time,
            ];
        }

        return $books;
    }

    protected function getWeekDaySlots()
    {
        $period = $this->calenderSlot->duration;

        $weeklySlots = SanitizeService::weeklySchedules($this->calenderSlot->settings['weekly_schedules'], 'UTC', $this->calendar->author_timezone, false);

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
                $end = strtotime($slot['end']);

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
        $period = $this->calenderSlot->duration;

        $formattedSlots = [];


        foreach ($slotSets as $slot) {

            if($toTimeZone) {
                $slot['start'] = DateTimeHelper::convertToTimeZone($slot['start'], 'UTC', $toTimeZone, 'H:i');
                $slot['end'] = DateTimeHelper::convertToTimeZone($slot['end'], 'UTC', $toTimeZone, 'H:i');
            }

            $start = strtotime($slot['start'] );
            $end = strtotime($slot['end']);

            while ($start < $end) {
                $formattedSlots[] = date('H:i', $start);
                $start += $period * 60;
            }
        }

        return $formattedSlots;

    }

}
