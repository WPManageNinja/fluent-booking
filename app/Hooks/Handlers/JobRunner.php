<?php

namespace FluentCalendar\App\Hooks\Handlers;

use FluentCalendar\App\Models\Booking;

class JobRunner
{
    private $oneHourDones = 0;

    private $fifteenMinutesDones = 0;

    protected $startedAt = false;

    public function register()
    {
        add_action('fluent_calendar_minute_tasks', [$this, 'checkForNewReminders']);
        add_action('fluent_calendar_hourly_tasks', [$this, 'maybeBookingCompleted']);
    }

    public function checkForNewReminders()
    {
        $this->startedAt = time();
        $this->checkHourlyReminders();
        $this->checkFor15MinutesReminders();

        return [
            'one_hour'        => $this->oneHourDones,
            'fifteen_minutes' => $this->fifteenMinutesDones,
            'time_took'       => time() - $this->startedAt
        ];
    }

    public function checkHourlyReminders()
    {
        if (!$this->willRun()) {
            return false;
        }

        $fromDate = date('Y-m-d H:i:s', strtotime('+40 minutes'));
        $toDate = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $bookings = Booking::where('status', 'scheduled')
            ->with(['slot'])
            ->whereBetween('next_reminder', [$fromDate, $toDate])
            ->where('reminder_stage', '!=', 'one_hour')
            ->limit(30)
            ->get();

        if ($bookings->isEmpty()) {
            return true;
        }

        foreach ($bookings as $booking) {
            $nextReminder = date('Y-m-d H:i:s', strtotime($booking->start_time) - 60 * 16); //  before 16 minutes
            $booking->next_reminder = $nextReminder;
            $booking->last_reminder_sent = date('Y-m-d H:i:s');
            $booking->reminder_stage = 'one_hour';
            $booking->save();
            do_action('fluent_calendar/booking_reminder_one_hour', $booking);
            $this->oneHourDones++;
        }

        return $this->checkHourlyReminders();
    }

    public function checkFor15MinutesReminders()
    {
        if (!$this->willRun()) {
            return false;
        }

        $fromDate = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        $toDate = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $bookings = Booking::where('status', 'scheduled')
            ->with(['slot'])
            ->whereBetween('start_time', [$fromDate, $toDate])
            ->where('reminder_stage', '!=', '15_minutes')
            ->limit(30)
            ->get();

        if ($bookings->isEmpty()) {
            error_log('No bookings found for 15 minutes reminder ' . $fromDate . ' - ' . $toDate);
            return true;
        }

        foreach ($bookings as $booking) {
            $booking->next_reminder = NULL;
            $booking->last_reminder_sent = date('Y-m-d H:i:s');
            $booking->reminder_stage = '15_minutes';
            $booking->save();
            do_action('fluent_calendar/booking_reminder_15_minutes', $booking);

            error_log('fluent_calendar/booking_reminder_15_minutes ' . $booking->id);

            $this->fifteenMinutesDones++;
        }

        return $this->checkFor15MinutesReminders();
    }

    protected function willRun()
    {
        return (time() - $this->startedAt) < 50;
    }

    public function maybeBookingCompleted()
    {
        $this->startedAt = time();
        $this->checkForCompletedBookings();
    }

    private function checkForCompletedBookings()
    {
        if (!$this->willRun()) {
            return false;
        }

        $toDate = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $bookings = Booking::where('status', 'scheduled')
            ->where('end_time', '<=', $toDate)
            ->limit(30)
            ->get();

        if ($bookings->isEmpty()) {
            return true;
        }

        foreach ($bookings as $booking) {
            $booking->status = 'completed';
            $booking->save();
            do_action('fluent_calendar/booking_completed', $booking);
        }

        return $this->checkForCompletedBookings();
    }
}
