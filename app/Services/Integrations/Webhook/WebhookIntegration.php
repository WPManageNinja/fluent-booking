<?php

namespace FluentBooking\App\Services\Integrations\Webhook;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Meta;
use FluentBooking\Framework\Support\Arr;

class WebhookIntegration
{
    public function register()
    {
        add_action('fluent_booking/after_booking_scheduled', [$this, 'maybeHandleWebHookAsync'], 10, 2);
        add_action('fluent_booking/booking_schedule_cancelled', [$this, 'maybeHandleWebHookAsync'], 10, 2);
        add_action('fluent_booking/booking_schedule_completed', [$this, 'maybeHandleWebHookAsync'], 10);

        add_action('fluent_booking/run_webhook', [$this, 'runWebhook'], 10, 1);

        add_action('init', function () {
            if(!isset($_REQUEST['webhook'])) {
                return;
            }

           // $this->runWebhook($_REQUEST['webhook']);

        });
    }

    public function maybeHandleWebHookAsync($booking, $calendarSlot)
    {
        $status = $booking->status;

        $maps = [
            'scheduled' => 'after_booking_scheduled',
            'cancelled' => 'booking_schedule_cancelled',
            'completed' => 'booking_schedule_completed'
        ];

        if (!isset($maps[$status])) {
            return;
        }
        $currentHook = $maps[$status];

        $webHooks = Meta::where('object_id', $calendarSlot->id)
            ->where('object_type', 'calendar_event')
            ->where('key', 'webhook_feeds')
            ->get();

        foreach ($webHooks as $webHook) {
            $item = $webHook->value;
            $triggers = Arr::get($item, 'event_triggers', []);
            if (!in_array($currentHook, $triggers) || !Arr::isTrue($item->value, 'enabled')) {
                continue;
            }

            as_enqueue_async_action('fluent_booking/run_webhook', $webHook->id, 'fluent-booking');
        }
    }

    public function runWebhook($webhookId)
    {
        $webHook = Meta::where('key', 'webhook_feeds')->find($webhookId);

        if (!$webHook) {
            return;
        }

        $booking = Booking::with('calendar_event')->find($webHook->object_id);

        if (!$booking || !$booking->calendar_event) {
            return;
        }
        $feed = $webHook->value;

        if (!Arr::isTrue($feed, 'enabled')) {
            return;
        }

        dd($feed);
    }
}
