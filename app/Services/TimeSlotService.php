<?php

namespace FluentCalendar\App\Services;

use FluentCalendar\App\Models\Booking;
use FluentCalendar\App\Models\Calendar;
use FluentCalendar\App\Models\CalendarSlot;

class TimeSlotService
{
    protected $calenderSlot;

    protected $calendar;

    public function __construct(Calendar $calendar, CalendarSlot $calenderSlot)
    {
        $this->calendar = $calendar;
        $this->calenderSlot = $calenderSlot;
    }

    function getDates($fromDate = false, $toDate = false)
    {
        $period = $this->calenderSlot->duration;

        $fromDate = $fromDate ? $fromDate : date('Y-m-d');
        $toDate = $toDate ? $toDate : date('Y-m-t', strtotime($fromDate));

        $ranges = $this->getCurrentDateRange($fromDate);

        $weekends = $this->getWeekends();
        $holidays = $this->getPublicHolidays();

        if ($weekends && $holidays) {
            $ranges = array_filter($ranges, function ($date) use ($weekends, $holidays) {
                $day = strtolower(date('D', strtotime($date)));
                return !in_array($day, $weekends) && !in_array($date, $holidays);
            });

            $ranges = array_values($ranges);
        }

        $daySlots = $this->getWeekDaySlots();
        $bookedSlots = $this->getBookedSlots([$fromDate, $toDate]);

        $rangedValidSlots = [];

        $fromValidTimeStamp = time() + 60 * 60 * 3; // 3 hours after now

        foreach ($ranges as $date) {

            $day = strtolower(date('D', strtotime($date)));
            if (empty($daySlots[$day])) {
                continue;
            }

            $availableSlots = $daySlots[$day];

            $currentBookedSlots = [];

            if (!empty($bookedSlots[$date])) {
                $currentBookedSlots = $bookedSlots[$date];
            }

            $isToday = $date === date('Y-m-d');

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
            $rangedValidSlots[$date] = $validSlots;
        }

        return $rangedValidSlots;
    }

    private function getWeekends()
    {
        return ['sat', 'sun'];
    }

    private function getPublicHolidays()
    {
        return [
            '2023-01-01',
            '2023-01-18',
            '2023-02-15',
            '2023-04-02',
            '2023-04-05',
            '2023-05-13',
            '2023-05-24',
            '2023-09-06',
            '2023-10-04',
            '2023-11-01',
            '2023-12-25',
            '2023-12-26'
        ];
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

    protected function getBookedSlots($dateRange)
    {
        $bookings = Booking::where('slot_id', $this->calenderSlot->id)
            ->whereBetween('start_time', $dateRange)
            ->orderBy('start_time', 'ASC')
            ->whereIn('status', ['pending', 'approved', 'scheduled', 'completed'])
            ->get();

        $books = [];

        foreach ($bookings as $booking) {
            $date = $booking->start_date;

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

        $weeklySlots = $this->calenderSlot->settings['weekly_schedules'];

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
}
