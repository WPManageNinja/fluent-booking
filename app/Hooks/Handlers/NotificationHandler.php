<?php

namespace FluentCalendar\App\Hooks\Handlers;

use FluentCalendar\App\Models\Booking;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Services\EmailNotificationService;
use FluentCalendar\Framework\Support\Arr;

class NotificationHandler
{
    public function register()
    {
        add_action('fluent_calendar/after_booking_scheduled', array($this, 'pushBookingScheduledToQueue'), 10, 2);
        add_action('fluent_calendar/after_booking_scheduled_async', [$this, 'bookingScheduledEmails'], 10, 2);

        add_action('fluent_calendar/booking_reminder_one_hour', [$this, 'maybeOneHourReminder'], 10, 2);
        add_action('fluent_calendar/booking_reminder_15_minutes', [$this, 'maybe15MinReminder'], 10, 2);
        add_action('fluent_calendar/booking_schedule_cancelled', [$this, 'emailOnBookingCancelled']);

    }

    public function pushBookingScheduledToQueue($booking, $slot)
    {
        $reminders = $slot->getNotifications();

        if (Arr::isTrue($reminders, 'booking_conf_attendee.enabled') || (Arr::isTrue($reminders, 'booking_conf_host.enabled'))) {
            as_enqueue_async_action('fluent_calendar/after_booking_scheduled_async', [
                $booking->id,
                $slot->id
            ], 'fluent-calendar');
        }

        // schedule 1 hour & 15 minutes reminder
        $happeningTimestamp = strtotime($booking->start_time);
        $startingTo = $happeningTimestamp - time();

        if ($startingTo > 1020) { // 17 minutes from the scheduled time
            as_schedule_single_action(($happeningTimestamp - 900), 'fluent_calendar/booking_reminder_15_minutes', [
                $booking->id,
                $slot->id
            ], 'fluent-calendar');
        }

        if ($startingTo > 3720) { // 1 hour and 2 minutes from the scheduled time
            as_schedule_single_action(($happeningTimestamp - 3600), 'fluent_calendar/booking_reminder_one_hour', [
                $booking->id,
                $slot->id
            ], 'fluent-calendar');
        }
    }

    public function bookingScheduledEmails($bookingId, $slotId)
    {
        $booking = Booking::find($bookingId);
        $slot = CalendarSlot::find($slotId);

        if ($booking && $slot) {
            $reminders = $slot->getNotifications();

            if (Arr::isTrue($reminders, 'booking_conf_attendee.enabled')) {
                EmailNotificationService::emailToGuestOnBooked($booking, $slot);
            }

            if (Arr::isTrue($reminders, 'booking_conf_host.enabled')) {
                EmailNotificationService::emailToHostOnBooked($booking, $slot);
            }
        }

        return true;
    }

    /**
     * @param $booking Booking
     * @return void
     */
    public function maybeOneHourReminder($bookingId, $slotId)
    {

        $booking = Booking::with(['slot'])->find($bookingId);

        if (!$booking || $booking->slot) {
            return false;
        }

        if ($booking->status != 'scheduled') {
            return false;
        }

        $slot = $booking->slot;

        $notifications = $slot->getNotifications();

        if (!$notifications) {
            return;
        }

        if (Arr::isTrue($notifications, 'reminder_1_hour_attendee.enabled')) {
            EmailNotificationService::emailToGuestOnOneHourReminder($booking, $slot);
        }

        if (Arr::isTrue($notifications, 'reminder_1_hour_host.enabled')) {
            EmailNotificationService::emailToHostOnOneHourReminder($booking, $slot);
        }

    }

    public function maybe15MinReminder($bookingId, $slotId)
    {
        $booking = Booking::with(['slot'])->find($bookingId);

        if (!$booking || $booking->slot) {
            return false;
        }

        $slot = $booking->slot;

        if ($booking->status != 'scheduled') {
            return false;
        }

        $notifications = $slot->getNotifications();

        if (!$notifications) {
            return false;
        }

        if (Arr::isTrue($notifications, 'reminder_15_min_attendee.enabled')) {
            EmailNotificationService::emailToGuest15MinutesReminder($booking, $slot);
        }

        if (Arr::isTrue($notifications, 'reminder_15_min_host.enabled')) {
            EmailNotificationService::emailToHostOn15MinutesReminder($booking, $slot);
        }
    }

    public function emailOnBookingCancelled($booking)
    {
        $slot = $booking->slot;
        if (!$slot) {
            return;
        }

        $notifications = $slot->getNotifications();

        if (!$notifications) {
            return;
        }

        if ($booking->cancelled_by) {
            // This from the host
            EmailNotificationService::bookingCancelledEmailToGuest($booking, $slot);
        } else {
            EmailNotificationService::bookingCancelledEmailToHost($booking, $slot);
        }
    }
}
