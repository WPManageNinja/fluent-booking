<?php

namespace FluentCalendar\App\Hooks\Handlers;

use FluentCalendar\App\Services\EmailNotificationService;

class NotificationHandler
{
    public function bookingScheduled($booking, $bookingData, $slot)
    {
        EmailNotificationService::emailToGuestOnBooked($booking, $slot);
        EmailNotificationService::emailToAdminOnBooked($booking, $slot);
    }
}
