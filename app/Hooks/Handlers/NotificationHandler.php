<?php

namespace FluentBooking\App\Hooks\Handlers;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\EmailNotificationService;
use FluentBooking\Framework\Support\Arr;

class NotificationHandler
{
    public function register()
    {
        add_action('fluent_booking/after_booking_scheduled', array($this, 'pushBookingScheduledToQueue'), 10, 2);
        add_action('fluent_booking/after_booking_scheduled_async', [$this, 'bookingScheduledEmails'], 10, 2);
        add_action('fluent_booking/booking_schedule_reminder', [$this, 'bookingReminderEmails'], 10, 4);
        add_action('fluent_booking/booking_schedule_cancelled', [$this, 'emailOnBookingCancelled']);
    }

    private function getReminderTime($time)
    {
        $timestamp = $time['value'] * 60;

        if ($time['unit'] == 'hours') {
            $timestamp = $timestamp * 60;
        } elseif ($time['unit'] == 'days') {
            $timestamp = $timestamp * 60 * 24;
        }

        return $timestamp;
    }

    private function pushRemindersToQueue($booking, $slot, $reminderTimes, $emailTo)
    {
        foreach ($reminderTimes as $time) {
            $reminderTimestamp = $this->getReminderTime($time);

            $happeningTimestamp = strtotime($booking->start_time);
            $startingTo = $happeningTimestamp - time();
            
            $bufferTime = 2 * 60; // 2 Minute Buffer Time
            if ($startingTo > ($reminderTimestamp + $bufferTime)) {
                as_schedule_single_action(($happeningTimestamp - $reminderTimestamp), 'fluent_booking/booking_schedule_reminder', [
                    $booking->id,
                    $slot->id,
                    $time,
                    $emailTo
                ], 'fluent-booking');
            }
        }
    }

    public function pushBookingScheduledToQueue($booking, $slot)
    {
        $notifications = $slot->getNotifications();

        if (Arr::isTrue($notifications, 'booking_conf_attendee.enabled') || (Arr::isTrue($notifications, 'booking_conf_host.enabled'))) {
            as_enqueue_async_action('fluent_booking/after_booking_scheduled_async', [
                $booking->id,
                $slot->id
            ], 'fluent-booking');
        }

        if (Arr::isTrue($notifications, 'reminder_to_attendee.enabled')) {
            $reminderTimes = Arr::get($notifications, 'reminder_to_attendee.email.times', []);
            $this->pushRemindersToQueue($booking, $slot, $reminderTimes, 'guest');
        }

        if (Arr::isTrue($notifications, 'reminder_to_host.enabled')) {
            $reminderTimes = Arr::get($notifications, 'reminder_to_host.email.times', []);
            $this->pushRemindersToQueue($booking, $slot, $reminderTimes, 'host');
        }
        
    }

    public function bookingScheduledEmails($bookingId, $slotId)
    {
        $booking = Booking::find($bookingId);
        $slot = CalendarSlot::find($slotId);

        if ($booking && $slot) {
            $notifications = $slot->getNotifications();

            if (Arr::isTrue($notifications, 'booking_conf_attendee.enabled')) {
                $email = Arr::get($notifications, 'booking_conf_attendee.email', []);
                EmailNotificationService::emailOnBooked($booking, $slot, $email, 'guest');
            }

            if (Arr::isTrue($notifications, 'booking_conf_host.enabled')) {
                $email = Arr::get($notifications, 'booking_conf_host.email', []);
                EmailNotificationService::emailOnBooked($booking, $slot, $email, 'host');
            }
        }

        return true;
    }

    /**
     * @param $booking Booking
     * @return void
     */
    public function bookingReminderEmails($bookingId, $slotId, $time, $emailTo)
    {
        $booking = Booking::find($bookingId);
        $slot = CalendarSlot::find($slotId);

        if (!$slot || !$booking || $booking->status != 'scheduled') {
            return false;
        }

        $notifications = $slot->getNotifications();

        if (!$notifications) {
            return;
        }

        if ('guest' == $emailTo && Arr::isTrue($notifications, 'reminder_to_attendee.enabled')) {
            $email = Arr::get($notifications, 'reminder_to_attendee.email', []);
            EmailNotificationService::reminderEmail($booking, $slot, $email, $time, $emailTo);
        } elseif ('host' == $emailTo && Arr::isTrue($notifications, 'reminder_to_host.enabled')) {
            $email = Arr::get($notifications, 'reminder_to_host.email', []);
            EmailNotificationService::reminderEmail($booking, $slot, $email, $time, $emailTo);
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

        if ($booking->cancelled_by && Arr::isTrue($notifications, 'cancelled_by_host.enabled')) {
            // This from the host
            $email = Arr::get($notifications, 'cancelled_by_host.email', []);
            EmailNotificationService::bookingCancelledEmail($booking, $slot, $email, 'guest');
        } elseif (Arr::isTrue($notifications, 'cancelled_by_attendee.enabled')) {
            $email = Arr::get($notifications, 'cancelled_by_attendee.email', []);
            foreach ($booking->getHostProfiles() as $host) {
                EmailNotificationService::bookingCancelledEmail($booking, $slot, $email, 'host', $host);
            }
        }
    }
}
