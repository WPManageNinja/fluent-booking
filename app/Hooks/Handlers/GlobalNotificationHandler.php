<?php

namespace FluentBooking\App\Hooks\Handlers;

use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Services\EditorShortCodeParser;
use FluentBooking\App\Services\Integrations\GlobalNotificationService;

class GlobalNotificationHandler
{
    /**
     * @var GlobalNotificationService
     */
    private $globalNotificationService;

    public function __construct()
    {
        $this->globalNotificationService = new GlobalNotificationService();
    }

    public function globalNotify($insertId, $booking, $slot)
    {
        // Let's find the feeds that are available for this form
        $feedKeys = apply_filters('fluent_booking/global_notification_active_types', [], $slot->id);

        if (!$feedKeys) {
            do_action('fluent_booking/global_notify_completed', $insertId, $slot);

            return;
        }

        $feedMetaKeys = array_keys($feedKeys);
        $feeds = $this->globalNotificationService->getNotificationFeeds($slot->id, $feedMetaKeys);

        if (!$feeds) {
            do_action('fluent_booking/global_notify_completed', $insertId, $slot);

            return;
        }

        // Now we have to filter the feeds which are enabled
        $enabledFeeds = $this->globalNotificationService->getEnabledFeeds($feeds, $booking, $insertId);

        if (!$enabledFeeds) {
            do_action('fluent_booking/global_notify_completed', $insertId, $slot);

            return;
        }

        $entry = false;
        $asyncFeeds = [];

        // $scheduler = $this->app['fluentFormAsyncRequest'];

        foreach ($enabledFeeds as $feed) {
            // We will decide if this feed will run on async or sync
            $integrationKey = Arr::get($feedKeys, $feed['key']);

            $newAction = 'fluent_booking/integration_notify_' . $feed['key'];

            // if (! $entry) {
            //     $entry = $this->globalNotificationService->getEntry($insertId, $slot);
            // }
            // skip emails which will be sent on payment form submit otherwise email is sent after payment success
            // if (!! $slot->has_payment && ('notifications' == $feed['key'])) {
            //     if (('payment_form_submit' == Arr::get($feed, 'settings.feed_trigger_event'))) {
            //         continue;
            //     }
            // }

            // It's sync
            $processedValues = $feed['settings'];
            unset($processedValues['conditionals']);
            // $processedValues = EditorShortCodeParser::parse($processedValues, $insertId, $booking, $slot, false, $feed['key']);
            $feed['processedValues'] = $processedValues;

            if (apply_filters('fluent_booking/notifying_async_' . $integrationKey, false, $slot->id)) {
                // It's async
                $asyncFeed = [
                    'action'     => $newAction,
                    'form_id'    => $slot->id,
                    'origin_id'  => $insertId,
                    'feed_id'    => $feed['id'],
                    'type'       => 'submission_action',
                    'status'     => 'pending',
                    'data'       => maybe_serialize($feed),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ];

                $asyncFeeds[] = $asyncFeed;

                // $queueId = $scheduler->queue($asyncFeed);

                // as_enqueue_async_action('fluent_booking/schedule_feed', ['queueId' => $queueId], 'fluentform');
            } else {
                do_action($newAction, $feed, $booking, $entry, $slot);
            }
        }

        if (!$asyncFeeds) {
            do_action('fluent_booking/global_notify_completed', $insertId, $slot);

            return;
        }
    }
}
