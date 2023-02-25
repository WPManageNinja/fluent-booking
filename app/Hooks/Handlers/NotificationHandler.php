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

        add_action('fluent_calendar/booking_reminder_one_hour', [$this, 'maybeOneHourReminder']);
        add_action('fluent_calendar/booking_reminder_15_minutes', [$this, 'maybe15MinReminder']);
    }

    public function pushBookingScheduledToQueue($booking, $slot )
    {

        $reminders = $slot->getNotifications();

        if(Arr::isTrue($reminders, 'booking_conf_attendee.enabled') || (Arr::isTrue($reminders, 'booking_conf_host.enabled'))) {
            as_enqueue_async_action( 'fluent_calendar/after_booking_scheduled_async', [
                $booking->id,
                $slot->id
            ], 'fluent-calendar' );
        }
    }

    public function bookingScheduledEmails($bookingId, $slotId)
    {
        $booking = Booking::find($bookingId);
        $slot = CalendarSlot::find($slotId);

        if($booking && $slot) {
            $reminders = $slot->getNotifications();

            if(Arr::isTrue($reminders, 'booking_conf_attendee.enabled')) {
                EmailNotificationService::emailToGuestOnBooked($booking, $slot);
            }

            if(Arr::isTrue($reminders, 'booking_conf_host.enabled')) {
                EmailNotificationService::emailToHostOnBooked($booking, $slot);
            }
        }

        return true;
    }

    /**
     * @param $booking Booking
     * @return void
     */
    public function maybeOneHourReminder($booking)
    {
        $slot = $booking->slot;
        if(!$slot) {
            return;
        }
        $notifications = $slot->getNotifications();

        if(!$notifications) {
            return;
        }

        if(Arr::isTrue($notifications, 'reminder_1_hour_attendee.enabled')) {
            EmailNotificationService::emailToGuestOnOneHourReminder($booking, $slot);
        }

        if(Arr::isTrue($notifications, 'reminder_1_hour_host.enabled')) {
            EmailNotificationService::emailToHostOnOneHourReminder($booking, $slot);
        }

    }

    public function maybe15MinReminder($booking)
    {
        $slot = $booking->slot;
        if(!$slot) {
            return;
        }
        $notifications = $slot->getNotifications();

        if(!$notifications) {
            return;
        }

        if(Arr::isTrue($notifications, 'reminder_15_min_attendee.enabled')) {
            EmailNotificationService::emailToGuest15MinutesReminder($booking, $slot);
        }

        if(Arr::isTrue($notifications, 'reminder_15_min_host.enabled')) {
            EmailNotificationService::emailToHostOn15MinutesReminder($booking, $slot);
        }
    }
}
