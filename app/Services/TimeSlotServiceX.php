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

    protected $groupedSlots = [];

    public function __construct(Calendar $calendar, CalendarSlot $calendarSlot)
    {
        $this->groupedSlots = [];
        $this->calendar = $calendar;
        $this->calendarSlot = $calendarSlot;
    }

    public function getDates($fromDate = false, $toDate = false, $duration = null, $isDoingBooking = false)
    {
        $duration = $this->calendarSlot->getDuration($duration);
        $period   = $duration * 60;

        $fromDate = $fromDate ? $fromDate : gmdate('Y-m-d'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        $toDate = $toDate ? $toDate : gmdate('Y-m-t 23:59:59', strtotime($fromDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        $ranges = $this->getCurrentDateRange($fromDate, $toDate);

        $daySlots = $this->getWeekDaySlots($duration);
        $bookedSlots = $this->getBookedSlots([$fromDate, $toDate], $this->calendar->author_timezone, $isDoingBooking);

        $ranges = $this->maybeBookingFrequencyLimitRanges($ranges, $bookedSlots);
        $ranges = $this->maybeBookingDurationLimitRanges($ranges, $bookedSlots, $duration);

        $timeStamp = DateTimeHelper::getTimestamp($this->calendar->author_timezone);
        $cutOutTimeStamp = $timeStamp + $this->calendarSlot->getCutoutSeconds();

        $todayDate = DateTimeHelper::convertToTimeZone(gmdate('Y-m-d'), 'UTC', $this->calendar->author_timezone, 'Y-m-d'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        $overrides = Arr::get($this->calendarSlot->settings, 'date_overrides', []);

        if ('existing_schedule' === $this->calendarSlot->availability_type) {
            $availability = Availability::findOrFail($this->calendarSlot->availability_id);
            $overrides = Arr::get($availability, 'value.date_overrides', []);
        }

        $rangedValidSlots = [];

        foreach ($ranges as $date) {
            if ($overrides && isset($overrides[$date])) {
                $availableSlots = $this->convertSlotSetsToFlat($overrides[$date], $this->calendar->author_timezone, $duration);
            } else {
                $day = strtolower(gmdate('D', strtotime($date))); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
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
                $end = gmdate('H:i', strtotime($start) + $period); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
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

        if (!$this->groupedSlots) {
            return $rangedValidSlots;
        }

        $addedDates = [];

        foreach ($this->groupedSlots as $slot) {
            $date = gmdate('Y-m-d', strtotime($slot['start']));
            if ($todayDate == $date && strtotime($slot['start']) < $cutOutTimeStamp) {
                continue;
            }

            if (!isset($rangedValidSlots[$date])) {
                $rangedValidSlots[$date] = [];
                $rangedValidSlots[$date][] = $slot;
                continue;
            }

            $addedDates[$date] = $date;

            $rangedValidSlots[$date][] = $slot;
        }

        foreach ($addedDates as $date) {
            $dateSlots = $rangedValidSlots[$date];
            // short the $dateSlots array with start key asc way
            usort($dateSlots, function ($a, $b) {
                return strtotime($a['start']) - strtotime($b['start']);
            });

            $rangedValidSlots[$date] = $dateSlots;
        }

        //  $formattedSlots = $this->convertSlotSetsToFlat($this->groupedSlots, $this->calendar->author_timezone);

        return $rangedValidSlots;
    }

    public function isSpotAvailable($fromTime, $toTime, $duration = null)
    {
        $fromTime = DateTimeHelper::convertToTimeZone($fromTime, 'UTC', $this->calendar->author_timezone);
        $toTime = DateTimeHelper::convertToTimeZone($toTime, 'UTC', $this->calendar->author_timezone);

        $fromTimeStamp = strtotime($fromTime);
        $toTimeStamp = strtotime($toTime);

        $fromTime = gmdate('Y-m-d 00:00:00', $fromTimeStamp); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        $toTime = gmdate('Y-m-d 23:59:59', $toTimeStamp); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        $duration = $this->calendarSlot->getDuration($duration);

        $slots = $this->getDates($fromTime, $toTime, $duration, true);

        $date = gmdate('Y-m-d', $fromTimeStamp); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

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
            $startDate = gmdate('Y-m-d'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        }

        if (!$endDate) {
            $endDate = gmdate('Y-m-t 23:59:59', strtotime($startDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        }

        $currentDate = strtotime($startDate);
        $endDate = strtotime($endDate);
        $oneDay = 24 * 60 * 60;

        $date_array = [];

        while ($currentDate <= $endDate) {
            $date_array[] = gmdate('Y-m-d', $currentDate); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            $currentDate += $oneDay;
        }

        return $date_array;
    }

    protected function bookSlot($eventId, $start, $end, $remaining = 0)
    {
        return [
            'event_id'  => $eventId,
            'start'     => $start,
            'end'       => $end,
            'remaining' => $remaining,
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

        $isGroupBooking = $maxBooking > 1;

        $bufferTime = $this->calendarSlot->getTotalBufferTime();

        $books = [];

        foreach ($bookings as $booking) {

            $booked = $booking->count();
            $booking = $booking[0];

            $booking->start_time = DateTimeHelper::convertToTimeZone($booking->start_time, 'UTC', $toTimeZone);
            $booking->end_time = DateTimeHelper::convertToTimeZone($booking->end_time, 'UTC', $toTimeZone);

            $date = gmdate('Y-m-d', strtotime($booking->start_time)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

            if (!isset($books[$date])) {
                $books[$date] = [];
            }

            $remaining = 0;

            if ($this->calendarSlot->id == $booking->event_id) {
                $remaining = max(0, $maxBooking - $booked);
                if ($bufferTime) {
                    $beforeBufferTime = gmdate('Y-m-d H:i:s', strtotime($booking->start_time . " -$bufferTime minutes")); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                    $afterBufferTime = gmdate('Y-m-d H:i:s', strtotime($booking->end_time . " +$bufferTime minutes")); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                    if ($remaining) {
                        if ($beforeBufferTime < $booking->start_time) {
                            $books[$date][] = $this->bookSlot(null, $beforeBufferTime, $booking->start_time);
                        }
                        if ($afterBufferTime > $booking->end_time) {
                            $books[$date][] = $this->bookSlot(null, $booking->end_time, $afterBufferTime);
                        }
                    } else {
                        $booking->start_time = $beforeBufferTime;
                        $booking->end_time = $afterBufferTime;
                    }
                }
            }

            $rangedItems = $this->createDateRangeArrayFromSlotConfig([
                'event_id'  => $booking->event_id,
                'start'     => $booking->start_time,
                'end'       => $booking->end_time,
                'remaining' => $remaining
            ]);

            $eventIdAdded = false;

            foreach ($rangedItems as $date => $slot) {
                if ($isGroupBooking && $remaining && $this->calendarSlot->id == $booking->event_id) {
                    $this->groupedSlots[] = $slot;
                    if ($eventIdAdded) {
                        $slot['event_id'] = null;
                    }
                    $eventIdAdded = true;
                }

                if (!isset($books[$date])) {
                    $books[$date] = [];
                }

                $books[$date][] = $slot;
            }
        }

        $books = apply_filters('fluent_booking/local_booked_events', $books, $this->calendarSlot, $toTimeZone, $dateRange, $isDoingBooking);

        $remoteBookings = apply_filters('fluent_booking/remote_booked_events', [], $this->calendarSlot, $toTimeZone, $dateRange, $isDoingBooking);

        if (!$remoteBookings) {
            return apply_filters('fluent_booking/booked_events', $books, $this->calendarSlot, $toTimeZone, $dateRange, $isDoingBooking);
        }

        if (!$isGroupBooking) {

            foreach ($remoteBookings as $slot) {
                $rangedItems = $this->createDateRangeArrayFromSlotConfig([
                    'start' => $slot['start'],
                    'end'   => $slot['end']
                ]);

                foreach ($rangedItems as $rangedDate => $rangedSlot) {
                    if (!isset($books[$rangedDate])) {
                        $books[$rangedDate] = [];
                    }
                    $books[$rangedDate][] = $rangedSlot;
                }
            }

            return apply_filters('fluent_booking/booked_events', $books, $this->calendarSlot, $toTimeZone, $dateRange, $isDoingBooking);
        }

        foreach ($remoteBookings as $slot) {
            $rangedItems = $this->createDateRangeArrayFromSlotConfig([
                'start' => $slot['start'],
                'end'   => $slot['end']
            ]);
            foreach ($rangedItems as $rangedDate => $rangedSlot) {
                if (!isset($books[$rangedDate])) {
                    $books[$rangedDate] = [];
                }
                $books[$rangedDate][] = $rangedSlot;
            }
        }

        return apply_filters('fluent_booking/booked_events', $books, $this->calendarSlot, $toTimeZone, $dateRange, $isDoingBooking);
    }

    protected function getWeekDaySlots($duration)
    {
        $period = $duration * 60;

        $schedule = $this->calendarSlot->settings['weekly_schedules'];

        $interval = $this->calendarSlot->getSlotInterval($duration) * 60;

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
                    $daySlots[] = gmdate('H:i', $start); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                    $start += $interval;
                }
            }

            if ($daySlots) {
                $formattedSlots[$day] = $daySlots;
            }

        }

        return $formattedSlots;
    }

    protected function convertSlotSetsToFlat($slotSets, $toTimeZone = false, $duration = null)
    {
        $period = ($this->calendarSlot->getDuration($duration)) * 60;

        $interval = $this->calendarSlot->getSlotInterval($duration) * 60;

        $formattedSlots = [];

        foreach ($slotSets as $slot) {

            if ($toTimeZone) {
                $slot['start'] = DateTimeHelper::convertToTimeZone($slot['start'], 'UTC', $toTimeZone, 'H:i');
                $slot['end'] = DateTimeHelper::convertToTimeZone($slot['end'], 'UTC', $toTimeZone, 'H:i');
            }

            $start = strtotime($slot['start']);
            $end = strtotime($slot['end']);

            while ($start + $period <= $end) {
                $formattedSlots[] = gmdate('H:i', $start); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                $start += $interval;
            }
        }

        return $formattedSlots;
    }

    public function getAvailableSpots($startDate, $timeZone = 'utc', $duration = null)
    {
        $slot     = $this->calendarSlot;
        $calendar = $this->calendar;
        $duration = $this->calendarSlot->getDuration($duration);
        
        // Extract current month and year
        $requestedDate = $startDate;
        $requestedDateMonth = gmdate('m', strtotime($requestedDate));
        $requestedDateYear = gmdate('Y', strtotime($requestedDate));

        $startDate = DateTimeHelper::convertToTimeZone($startDate, $timeZone, $calendar->author_timezone);
        $currentAuthorDateTime = DateTimeHelper::convertToTimeZone(gmdate('Y-m-d H:i:s'), 'UTC', $calendar->author_timezone); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        if (strtotime($startDate) < strtotime($currentAuthorDateTime)) {
            $startDate = $currentAuthorDateTime;
        }

        // Extract month and year from the timezone converted start date
        $startDateMonth = gmdate('m', strtotime($startDate));
        $startDateYear = gmdate('Y', strtotime($startDate));

        if ($startDateYear < $requestedDateYear || $startDateMonth < $requestedDateMonth) {
            $startDate = gmdate('Y-m-01 00:00:00', strtotime($requestedDate));
        }

        $eventType      = $slot->event_type;
        $isDisplaySpots = $slot->is_display_spots;
        $maxBooking     = $slot->getMaxBookingPerSlot();
        $endDate        = $slot->getMaxBookableDateTime($startDate);
        $startDate      = $slot->getMinBookableDateTime($startDate);

        if (strtotime($startDate) > strtotime($endDate)) {
            return new \WP_Error('invalid_date_range', __('Invalid date range', 'fluent-booking-pro'));
        }

        $startDate = DateTimeHelper::convertToTimeZone($startDate, $timeZone, $calendar->author_timezone);
        $endDate = DateTimeHelper::convertToTimeZone($endDate, $timeZone, $calendar->author_timezone);

        $slots = $this->getDates($startDate, $endDate, $duration);

        $convertedSpots = [];

        $minBookableTimestamp = strtotime($startDate);

        foreach ($slots as $spots) {
            foreach ($spots as $spot) {
                if (strtotime($spot['start']) < $minBookableTimestamp) {
                    continue;
                }

                $startDate = DateTimeHelper::convertToTimeZone($spot['start'], $calendar->author_timezone, $timeZone, 'Y-m-d');

                $convertedSpots[$startDate] = $convertedSpots[$startDate] ?? [];

                $remainingSlots = false;

                if ($isDisplaySpots && $eventType == 'group') {
                    $remainingSlots = Arr::get($spot, 'remaining', $maxBooking);
                }

                $start = DateTimeHelper::convertToTimeZone($spot['start'], $calendar->author_timezone, $timeZone);
                $convertedSpots[$startDate][$start] = [
                    'start'     => $start,
                    'end'       => DateTimeHelper::convertToTimeZone($spot['end'], $calendar->author_timezone, $timeZone),
                    'remaining' => $remainingSlots,
                ];
            }
        }

        $convertedSpots = array_map(function ($spots) {
            return array_values($spots);
        }, $convertedSpots);

        return $convertedSpots;
    }

    private function createDateRangeArrayFromSlotConfig($slotConfig = [])
    {
        if (empty($slotConfig['start']) || empty($slotConfig['end'])) {
            return [];
        }

        $startTime = $slotConfig['start'];
        $endTime = $slotConfig['end'];
        if (gmdate('Ymd', strtotime($startTime)) == gmdate('Ymd', strtotime($endTime))) {
            return [
                gmdate('Y-m-d', strtotime($startTime)) => $this->bookSlot(Arr::get($slotConfig, 'event_id'), $startTime, $endTime, Arr::get($slotConfig, 'remaining'))
            ];
        }

        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);

        // Set the end time to the end of the day if it's set to the beginning of a day
        if ($end->format('H:i:s') === '00:00:00') {
            $end->modify('-1 second'); // This will set the time to 23:59:59 of the previous day
        }

        $interval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($start, $interval, $end);

        $rangeArray = [];
        foreach ($dateRange as $date) {
            $dateKey = $date->format('Y-m-d');

            if ($date->format('Y-m-d') === $start->format('Y-m-d')) {
                $rangeArray[$dateKey] = $this->bookSlot(Arr::get($slotConfig, 'event_id'), $startTime, $date->format('Y-m-d 23:59:59'), Arr::get($slotConfig, 'remaining'));
            } elseif ($date->format('Y-m-d') === $end->format('Y-m-d')) {
                $rangeArray[$dateKey] = $this->bookSlot(Arr::get($slotConfig, 'event_id'), $date->format('Y-m-d 00:00:00'), $endTime, Arr::get($slotConfig, 'remaining'));
            } else {
                $rangeArray[$dateKey] = $this->bookSlot(Arr::get($slotConfig, 'event_id'), $date->format('Y-m-d 00:00:00'), $date->format('Y-m-d 23:59:59'), Arr::get($slotConfig, 'remaining'));
            }
        }

        // Add the last day if it was not included in the loop
        if ($end->format('Y-m-d') !== $start->format('Y-m-d')) {
            $lastDayKey = $end->format('Y-m-d');
            $rangeArray[$lastDayKey] = $this->bookSlot(Arr::get($slotConfig, 'event_id'), $end->format('Y-m-d 00:00:00'), $endTime, Arr::get($slotConfig, 'remaining'));
        }

        return $rangeArray;
    }


    private function maybeBookingFrequencyLimitRanges($ranges, $bookedSlots)
    {
        if (!$ranges) {
            return $ranges;
        }

        $isBookingFrequencyEnabled = !!Arr::get($this->calendarSlot->settings, 'booking_frequency.enabled');

        if (!$isBookingFrequencyEnabled) {
            return $ranges;
        }

        $frequenceyLimits = Arr::get($this->calendarSlot->settings, 'booking_frequency.limits', []);

        $keyedFrequenceyLimits = [];
        foreach ($frequenceyLimits as $limit) {
            if (!empty($limit['value'])) {
                $keyedFrequenceyLimits[$limit['unit']] = $limit['value'];
            }
        }

        // Per Month Booking Frequency Limit Hanlder
        if (!empty($keyedFrequenceyLimits['per_month'])) {
            $startDate = gmdate('Y-m-01 00:00:00', strtotime(min($ranges)));
            $endDate = gmdate('Y-m-t 23:59:59', strtotime(min($ranges)));

            $monthlyLimit = (int)$keyedFrequenceyLimits['per_month'];

            $monthlyCount = $this->getBookingsTotal(
                DateTimeHelper::convertToUtc($startDate, $this->calendar->author_timezone),
                DateTimeHelper::convertToUtc($endDate, $this->calendar->author_timezone)
            );

            if ($monthlyCount >= $monthlyLimit) {
                return [];
            }
        }

        // Per Week Booking Frequency Limit Hanlder
        if (!empty($keyedFrequenceyLimits['per_week'])) {

            if (!$ranges) {
                return [];
            }

            $weeklyLimit = (int)$keyedFrequenceyLimits['per_week'];
            $filledWeeks = $this->getFilledWeeks(min($ranges), max($ranges));
            foreach ($filledWeeks as $filledWeek) {

                $weeklyCount = $this->getBookingsTotal(
                    DateTimeHelper::convertToUtc($filledWeek[0] . ' 00:00:00', $this->calendar->author_timezone),
                    DateTimeHelper::convertToUtc($filledWeek[6] . ' 23:59:59', $this->calendar->author_timezone)
                );

                if ($weeklyCount >= $weeklyLimit) {
                    $ranges = array_filter($ranges, function ($rangeDate) use ($filledWeek) {
                        return !in_array($rangeDate, $filledWeek);
                    });

                    if (!$ranges) {
                        return [];
                    }
                }
            }
        }

        // Per Day Booking Frequency Limit Hanlder
        if (!empty($keyedFrequenceyLimits['per_day'])) {
            $perDayLimit = $keyedFrequenceyLimits['per_day'];
            foreach ($ranges as $rangeIndex => $rangeDate) {
                if (!isset($bookedSlots[$rangeDate])) {
                    continue;
                }

                $dayBooked = array_filter($bookedSlots[$rangeDate], function ($slot) {
                    return Arr::get($slot, 'event_id') == $this->calendarSlot->id;
                });

                if (!$dayBooked) {
                    continue;
                }

                if (count($dayBooked) >= $perDayLimit) {
                    unset($ranges[$rangeIndex]);
                }

                if (!$ranges) {
                    return [];
                }
            }
        }

        return $ranges;
    }

    private function maybeBookingDurationLimitRanges($ranges, $bookedSlots, $duration)
    {
        if (!$ranges) {
            return $ranges;
        }

        if (!Arr::get($this->calendarSlot->settings, 'booking_duration.enabled')) {
            return $ranges;
        }

        $limits = Arr::get($this->calendarSlot->settings, 'booking_duration.limits', []);

        $keyedLimits = [];
        foreach ($limits as $limit) {
            if (!empty($limit['value'])) {
                $keyedLimits[$limit['unit']] = (int)$limit['value'];
            }
        }

        // Per Month Booking Frequency Limit Hanlder
        if (!empty($keyedLimits['per_month'])) {
            $startDate = gmdate('Y-m-01 00:00:00', strtotime(min($ranges)));
            $endDate = gmdate('Y-m-t 23:59:59', strtotime(min($ranges)));

            $monthlyDuration = $this->getBookingDurationTotal(
                DateTimeHelper::convertToUtc($startDate, $this->calendar->author_timezone),
                DateTimeHelper::convertToUtc($endDate, $this->calendar->author_timezone)
            );
            if ($monthlyDuration + $duration > $keyedLimits['per_month']) {
                $ranges = [];
            }
        }

        // Per Week Booking Frequency Limit Hanlder
        if (!empty($keyedLimits['per_week'])) {
            $weeklyLimit = (int)$keyedLimits['per_week'];
            $filledWeeks = $this->getFilledWeeks(min($ranges), max($ranges));
            foreach ($filledWeeks as $filledWeek) {
                $weeklyDuration = $this->getBookingDurationTotal(
                    DateTimeHelper::convertToUtc($filledWeek[0] . ' 00:00:00', $this->calendar->author_timezone),
                    DateTimeHelper::convertToUtc($filledWeek[6] . ' 23:59:59', $this->calendar->author_timezone)
                );

                if ($weeklyDuration + $duration > $weeklyLimit) {
                    $ranges = array_filter($ranges, function ($rangeDate) use ($filledWeek) {
                        return !in_array($rangeDate, $filledWeek);
                    });

                    if (!$ranges) {
                        return [];
                    }
                }
            }
        }

        // Per Day Booking Frequency Limit Hanlder
        if (!empty($keyedLimits['per_day'])) {
            $perDayLimit = $keyedLimits['per_day'];
            foreach ($ranges as $rangeIndex => $rangeDate) {
                if (!isset($bookedSlots[$rangeDate])) {
                    continue;
                }

                $dayDurarion = array_reduce($bookedSlots[$rangeDate], function ($carry, $slot) {
                    if (Arr::get($slot, 'event_id') == $this->calendarSlot->id) {
                        $carry += (int)((strtotime($slot['end']) - strtotime($slot['start'])) / 60);
                    }
                    return $carry;
                }, 0);

                if (!$dayDurarion) {
                    continue;
                }

                if ($dayDurarion + $duration > $perDayLimit) {
                    unset($ranges[$rangeIndex]);
                }
            }
        }

        return $ranges;
    }

    public function getFilledWeeks($from, $to, $weekStart = '')
    {
        $weekStart = $weekStart ? $weekStart : Arr::get(Helper::getGlobalSettings(), 'administration.start_day', 'sun');

        $startDate = new DateTime($from);
        $endDate = new DateTime($to);

        if (strtolower($startDate->format('D')) != $weekStart) {
            $startDate->modify('last ' . $weekStart);
        }

        $weeks = [];

        while ($startDate <= $endDate) {
            // get all days in this week
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $startDate->format('Y-m-d');
                $startDate->modify('+1 day');
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    protected function getBookingsTotal($start, $end)
    {
        if ($this->calendarSlot->event_type == 'group') {
            return Booking::query()
                ->where('event_id', $this->calendarSlot->id)
                ->whereBetween('start_time', [$start, $end])
                ->whereIn('status', ['scheduled', 'completed'])
                ->groupBy('group_id')
                ->count();
        }

        return Booking::query()
            ->where('event_id', $this->calendarSlot->id)
            ->whereBetween('start_time', [$start, $end])
            ->whereIn('status', ['scheduled', 'completed'])
            ->count();
    }

    protected function getBookingDurationTotal($start, $end)
    {
        if ($this->calendarSlot->event_type == 'group') {
            return Booking::query()
                ->select(['group_id', 'slot_minutes'])
                ->where('event_id', $this->calendarSlot->id)
                ->whereBetween('start_time', [$start, $end])
                ->whereIn('status', ['scheduled', 'completed'])
                ->groupBy('group_id')
                ->get()
                ->sum('slot_minutes');
        }

        return Booking::query()
            ->where('event_id', $this->calendarSlot->id)
            ->whereBetween('start_time', [$start, $end])
            ->whereIn('status', ['scheduled', 'completed'])
            ->sum('slot_minutes');
    }
}
