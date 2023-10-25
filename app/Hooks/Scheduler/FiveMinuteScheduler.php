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
        \FluentBooking\App\Models\Booking::query()
            ->whereDate('created_at', '<=', date('Y-m-d H:i:s', current_time('timestamp') - 300)) // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled'
            ]);
    }
}
