<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Availability;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Support\DateTime;

class TimeSlotService
{
    protected $calendarSlot;

    protected $calendar;

    public function __construct(Calendar $calendar, CalendarSlot $calendarSlot)
    {
        $this->calendar = $calendar;
        $this->calendarSlot = $calendarSlot;
    }

    public function getDates($fromDate = false, $toDate = false, $bookingRequest = false)
    {
        $period = $this->calendarSlot->duration;

        $fromDate = $fromDate ? $fromDate : date('Y-m-d'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        $toDate = $toDate ? $toDate : date('Y-m-t 23:59:59', strtotime($fromDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        $ranges = $this->getCurrentDateRange($fromDate, $toDate);
        $daySlots = $this->getWeekDaySlots();
        $bookedSlots = $this->getBookedSlots([$fromDate, $toDate], $this->calendar->author_timezone, $bookingRequest);

        $timeStamp = DateTimeHelper::getTimestamp($this->calendar->author_timezone);
        $cutOutTimeStamp = $timeStamp + $this->calendarSlot->getCutoutSeconds();
        
        $maxBookPerDay = Arr::get($this->calendarSlot->settings, 'max_book_per_day', null);

        $todayDate = DateTimeHelper::convertToTimeZone(date('Y-m-d'), 'UTC', $this->calendar->author_timezone, 'Y-m-d'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        $overrides = Arr::get($this->calendarSlot->settings, 'date_overrides', []);

        if ('existing_schedule' === $this->calendarSlot->availability_type) {
            $availability = Availability::findOrFail($this->calendarSlot->availability_id);
            $overrides = Arr::get($availability, 'value.date_overrides', []);
        }

        $rangedValidSlots = [];

        foreach ($ranges as $date) {

            if ($overrides && isset($overrides[$date])) {
                $availableSlots = $this->convertSlotSetsToFlat($overrides[$date], $this->calendar->author_timezone);
            } else {
                $day = strtolower(date('D', strtotime($date))); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                if (empty($daySlots[$day])) {
                    continue;
                }
                $availableSlots = $daySlots[$day];
            }
            
            $currentBookedSlots = $bookedSlots[$date] ?? [];

            if (!$availableSlots || $this->hasReachedMaxLimit($maxBookPerDay, $currentBookedSlots)) {
                continue;
            }

            $isToday = $date === $todayDate;

            $validSlots = [];

            foreach ($availableSlots as $start) {
                $end = date('H:i', strtotime($start) + 60 * $period); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
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
                $endTimeStamp = strtotime($date . ' ' . $end);

                $isSpotAvailable = true;

                foreach ($currentBookedSlots as $bookedSlot) {
                    $bookedStart = strtotime($bookedSlot['start']);
                    $bookedEnd = strtotime($bookedSlot['end']);

                    if (
                        ($startTimeStamp >= $bookedStart && $startTimeStamp < $bookedEnd) ||
                        ($endTimeStamp > $bookedStart && $endTimeStamp <= $bookedEnd) ||
                        ($startTimeStamp <= $bookedStart && $endTimeStamp > $bookedStart) ||
                        ($startTimeStamp < $bookedEnd && $endTimeStamp >= $bookedEnd)
                    ) {
                        if (!Arr::get($bookedSlot, 'remaining')) {
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
        $toTime = DateTimeHelper::convertToTimeZone($toTime, 'UTC', $this->calendar->author_timezone);

        $fromTimeStamp = strtotime($fromTime);
        $toTimeStamp = strtotime($toTime);

        $fromTime = date('Y-m-d 00:00:00', $fromTimeStamp); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        $toTime = date('Y-m-d 23:59:59', $toTimeStamp); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        $slots = $this->getDates($fromTime, $toTime, true);

        $date = date('Y-m-d', $fromTimeStamp); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        $availableSlots = $slots[$date] ?? [];

        $left = 0;
        $right = count($availableSlots) - 1;

        while ($left <= $right) {
            $mid = $left + (($right - $left) >> 1);

            $midStartTime = strtotime($availableSlots[$mid]['start']);
            $midEndTime = strtotime($availableSlots[$mid]['end']);

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
            $startDate = date('Y-m-d'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        }

        if (!$endDate) {
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        }

        $currentDate = strtotime($startDate);
        $endDate = strtotime($endDate);
        $oneDay = 24 * 60 * 60;

        $date_array = [];

        while ($currentDate <= $endDate) {
            $date_array[] = date('Y-m-d', $currentDate); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            $currentDate += $oneDay;
        }

        return $date_array;
    }

    protected function bookSlot($eventId, $start, $end, $remaining = 0)
    {
        return [
            'event_id'    => $eventId,
            'start'       => $start,
            'end'         => $end,
            'remaining'   => $remaining,
        ];
    }

    protected function getBookedSlots($dateRange, $toTimeZone = false, $isDoingBooking = false)
    {
        if ($toTimeZone) {
            $dateRange[0] = DateTimeHelper::convertToUtc($dateRange[0], $toTimeZone);
            $dateRange[1] = DateTimeHelper::convertToUtc($dateRange[1], $toTimeZone);
        }

        $hostIds = $this->calendarSlot->getHostIds();
        $status = ['pending', 'approved', 'scheduled', 'completed'];

        $bookings = Booking::whereIn('host_user_id', $hostIds)
            ->whereBetween('start_time', $dateRange)
            ->orderBy('start_time', 'ASC')
            ->whereIn('status', $status)
            ->get()
            ->groupBy('group_id');

        $maxBooking = $this->calendarSlot->getMaxBookingPerSlot();
        $bufferTime = $this->calendarSlot->getTotalBufferTime();

        $books = [];

        foreach ($bookings as $booking) {

            $booked = $booking->count();
            $booking = $booking[0];

            $booking->start_time = DateTimeHelper::convertToTimeZone($booking->start_time, 'UTC', $toTimeZone);
            $booking->end_time = DateTimeHelper::convertToTimeZone($booking->end_time, 'UTC', $toTimeZone);

            $date = date('Y-m-d', strtotime($booking->start_time)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

            $books[$date] = $books[$date] ?? [];

            $remaining = 0;

            if ($this->calendarSlot->id == $booking->event_id) {
                $beforeBufferTime = date('Y-m-d H:i:s', strtotime($booking->start_time . " -$bufferTime minutes")); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                $afterBufferTime  = date('Y-m-d H:i:s', strtotime($booking->end_time   . " +$bufferTime minutes")); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

                if ($maxBooking > $booked) {
                    $remaining = $maxBooking - $booked;
                    if ($beforeBufferTime < $booking->start_time) {
                        $books[$date][] = $this->bookSlot($booking->event_id, $beforeBufferTime, $booking->start_time);
                    }
                    if ($afterBufferTime > $booking->end_time) {
                        $books[$date][] = $this->bookSlot($booking->event_id, $booking->end_time, $afterBufferTime);
                    }
                } else {   
                    $booking->start_time = $beforeBufferTime;
                    $booking->end_time   = $afterBufferTime;
                }
            }

            $books[$date][] = $this->bookSlot($booking->event_id, $booking->start_time, $booking->end_time, $remaining);
        }

        return apply_filters('fluent_booking/booked_events', $books, $this->calendarSlot, $toTimeZone, $dateRange, $isDoingBooking);
    }

    protected function getWeekDaySlots()
    {
        $period = $this->calendarSlot->duration * 60;

        $schedule = $this->calendarSlot->settings['weekly_schedules'];

        if ('existing_schedule' === $this->calendarSlot->availability_type) {
            $availability = Availability::findOrFail($this->calendarSlot->availability_id);
            $schedule = Arr::get($availability, 'value.weekly_schedules');
        }

        $weeklySlots = SanitizeService::weeklySchedules($schedule, 'UTC', $this->calendar->author_timezone, false);

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
                
                while ($start + $period <= $end) {
                    $daySlots[] = date('H:i', $start); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                    $start += $period;
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
                $slot['end'] = DateTimeHelper::convertToTimeZone($slot['end'], 'UTC', $toTimeZone, 'H:i');
            }

            $start = strtotime($slot['start']);
            $end = strtotime($slot['end']);

            while ($start < $end) {
                $formattedSlots[] = date('H:i', $start); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                $start += $period * 60;
            }
        }

        return $formattedSlots;

    }

    protected function hasReachedMaxLimit($maxBookPerDay, $bookedSlots)
    {
        if (!$maxBookPerDay) {
            return false;
        }

        $booked = array_filter($bookedSlots, function ($bookedSlot) {
            return $this->calendarSlot->id == $bookedSlot['event_id'];
        });

        return count($booked) >= $maxBookPerDay;
    }

    public function getAvailableSpots($startDate, $timeZone = 'utc')
    {
        $slot = $this->calendarSlot;
        $calendar = $this->calendar;
        $requestedDate = $startDate;

        // Extract current month and year
        $requestedDateMonth = date('m', strtotime($requestedDate));
        $requestedDateYear  = date('Y', strtotime($requestedDate));

        $startDate = DateTimeHelper::convertToTimeZone($startDate, $timeZone, $calendar->author_timezone);
        $currentAuthorDateTime = DateTimeHelper::convertToTimeZone(date('Y-m-d H:i:s'), 'UTC', $calendar->author_timezone); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        if (strtotime($startDate) < strtotime($currentAuthorDateTime)) {
            $startDate = $currentAuthorDateTime;
        }

        // Extract month and year from the timezone converted start date
        $startDateMonth = date('m', strtotime($startDate));
        $startDateYear = date('Y', strtotime($startDate));

        if ($startDateYear < $requestedDateYear || $startDateMonth < $requestedDateMonth) {
            $startDate = date('Y-m-01 00:00:00', strtotime($requestedDate));
        }

        $eventType = $slot->event_type;
        $isDisplaySpots = $slot->is_display_spots;
        $maxBooking = $slot->getMaxBookingPerSlot();
        $endDate = $slot->getMaxBookableDateTime($startDate);
        $startDate = $slot->getMinBookableDateTime($startDate);
        if (strtotime($startDate) > strtotime($endDate)) {
            return new \WP_Error('invalid_date_range', __('Invalid date range', 'fluent-booking-pro'));
        }
        
        $startDate = DateTimeHelper::convertToTimeZone($startDate, $timeZone, $calendar->author_timezone);
        $endDate = DateTimeHelper::convertToTimeZone($endDate, $timeZone, $calendar->author_timezone);

        $slots = $this->getDates($startDate, $endDate);

        $convertedSpots = [];

        $minBookableTimestamp = strtotime($startDate);

        foreach ($slots as $spots) {

            foreach ($spots as $spot) {
                if(strtotime($spot['start']) < $minBookableTimestamp) {
                    continue;
                }

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
