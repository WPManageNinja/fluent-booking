<?php

namespace FluentBooking\App\Hooks\Scheduler;

class FiveMinuteScheduler
{
    public function register()
    {
        add_action('fluent_booking_five_minutes_tasks', [$this, 'handle']);
    }

    public function handle()
    {
        $autCancelTimeOut = 600; // 10 minutes

        \FluentBooking\App\Models\Booking::query()
            ->where('created_at', '<=', date('Y-m-d H:i:s', time() - $autCancelTimeOut)) // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s') // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            ]);
    }
}
