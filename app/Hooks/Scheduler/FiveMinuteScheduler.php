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
            ->whereDate('created_at', '<=', strtotime('-5 minutes'))
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled'
            ]);
        $fiveMinutesHook = 'fluent_booking_five_minutes_tasks';
        wp_schedule_single_event(time() + (60 * 5), $fiveMinutesHook);
    }
}