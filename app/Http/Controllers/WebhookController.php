<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Meta;
use FluentBooking\Framework\Request\Request;
use FluentBooking\Framework\Support\Arr;

class WebhookController extends Controller
{
    public function getFeeds(Request $request, $calendarId, $eventId)
    {
        $calendarEvent = CalendarSlot::findOrFail($eventId);
        $feeds = Meta::where('object_id', $calendarEvent->id)
            ->where('object_type', 'calendar_event')
            ->where('key', 'webhook_feeds')
            ->get();

        $formattedFeeds = [];

        foreach ($feeds as $feed) {
            $formattedFeeds[] = [
                'id'       => $feed->id,
                'settings' => $feed->value,
            ];
        }


        return [
            'feeds'           => $formattedFeeds,
            'event_triggers'  => $this->eventTriggers(),
            'request_headers' => $this->getHeaders(),
        ];
    }

    public function saveFeed(Request $request, $calendarId, $eventId)
    {
        $calendarEvent = CalendarSlot::findOrFail($eventId);
        $webhookFeed = $request->get('webhook', []);
        $settings = Arr::get($webhookFeed, 'settings', []);

        $this->validate($settings, [
            'name'           => 'required',
            'request_url'    => 'required',
            'request_body'   => 'required',
            'request_method' => 'required',
            'event_triggers' => 'required',
        ]);

        $settings['enabled'] = Arr::isTrue($settings, 'enabled');

        if ($webhookFeed['id']) {
            $webhook = Meta::where('object_type', 'calendar_event')
                ->where('key', 'webhook_feeds')
                ->where('object_id', $calendarEvent->id)
                ->where('id', $webhookFeed['id'])
                ->first();

            if (!$webhook) {
                return $this->sendError([
                    'message' => __('WebHook not found', 'fluent-booking')
                ], 422);
            }

            $webhook->value = $settings;
            $webhook->save();
            return [
                'message' => __('WebHook Successfully Updated', 'fluent-booking')
            ];
        }

        // create new

        $data = [
            'value'       => $settings,
            'object_type' => 'calendar_event',
            'object_id'   => $calendarEvent->id,
            'key'         => 'webhook_feeds'
        ];

        Meta::create($data);

        return [
            'message' => __('WebHook Successfully Created', 'fluent-booking')
        ];
    }

    public function deleteFeed(Request $request, $calendarId, $eventId, $webhookId)
    {
        Meta::where('id', $webhookId)
            ->where('key', 'webhook_feeds')
            ->where('object_id', $eventId)
            ->delete();

        return [
            'message' => 'Selected Webhook has been deleted'
        ];
    }

    protected function eventTriggers()
    {
        return [
            [
                'label' => 'Booking Confirmed',
                'value' => 'after_booking_scheduled'
            ],
            [
                'label' => 'Booking Canceled',
                'value' => 'booking_schedule_cancelled'
            ],
            [
                'label' => 'Booking Completed',
                'value' => 'booking_schedule_completed'
            ]
        ];
    }

    protected function getHeaders()
    {
        return array(
            array(
                'label'           => 'Accept',
                'value'           => 'Accept',
                'possible_values' => [
                    'title'      => 'Accept Header Samples',
                    'shortcodes' => [
                        'Accept: text/plain' => 'text/plain',
                        'Accept: text/html'  => 'text/html',
                        'Accept: text/*'     => 'text/*'
                    ]
                ]
            ),
            array(
                'label'           => 'Accept-Charset',
                'value'           => 'Accept-Charset',
                'possible_values' => [
                    'title'      => 'Accep-Charset Header Samples',
                    'shortcodes' => [
                        'Accept-Charset: utf-8'      => 'utf-8',
                        'Accept-Charset: iso-8859-1' => 'iso-8859-1'
                    ]
                ]
            ),
            array(
                'label'           => 'Accept-Encoding',
                'value'           => 'Accept-Encoding',
                'possible_values' => [
                    'title'      => 'Accept-Encoding Header Samples',
                    'shortcodes' => [
                        'Accept-Encoding: gzip'     => 'gzip',
                        'Accept-Encoding: compress' => 'compress',
                        'Accept-Encoding: deflate'  => 'deflate',
                        'Accept-Encoding: br'       => 'br',
                        'Accept-Encoding: identity' => 'identity',
                        'Accept-Encoding: *'        => '*'
                    ]
                ]
            ),
            array(
                'label'           => 'Accept-Language',
                'value'           => 'Accept-Language',
                'possible_values' => [
                    'title'      => 'Accept-Language Header Samples',
                    'shortcodes' => [
                        'Accept-Language: en'             => 'en',
                        'Accept-Language: en-US'          => 'en-US',
                        'Accept-Language: en-GR'          => 'en-GR',
                        'Accept-Language: en-US,en;q=0.5' => 'en-US,en;q=0.5'
                    ]
                ]
            ),
            array(
                'label' => 'Accept-Datetime',
                'value' => 'Accept-Datetime',
            ),
            array(
                'label' => 'Authorization',
                'value' => 'Authorization',
            ),
            array(
                'label' => 'Cache-Control',
                'value' => 'Cache-Control',
            ),
            array(
                'label' => 'Connection',
                'value' => 'Connection',
            ),
            array(
                'label' => 'Cookie',
                'value' => 'Cookie',
            ),
            array(
                'label' => 'Content-Length',
                'value' => 'Content-Length',
            ),
            array(
                'label' => 'Content-Type',
                'value' => 'Content-Type',
            ),
            array(
                'label' => 'Date',
                'value' => 'Date',
            ),
            array(
                'label' => 'Expect',
                'value' => 'Expect',
            ),
            array(
                'label' => 'Forwarded',
                'value' => 'Forwarded',
            ),
            array(
                'label' => 'From',
                'value' => 'From',
            ),
            array(
                'label' => 'Host',
                'value' => 'Host',
            ),
            array(
                'label' => 'If-Match',
                'value' => 'If-Match',
            ),
            array(
                'label' => 'If-Modified-Since',
                'value' => 'If-Modified-Since',
            ),
            array(
                'label' => 'If-None-Match',
                'value' => 'If-None-Match',
            ),
            array(
                'label' => 'If-Range',
                'value' => 'If-Range',
            ),
            array(
                'label' => 'If-Unmodified-Since',
                'value' => 'If-Unmodified-Since',
            ),
            array(
                'label' => 'Max-Forwards',
                'value' => 'Max-Forwards',
            ),
            array(
                'label' => 'Origin',
                'value' => 'Origin',
            ),
            array(
                'label' => 'Pragma',
                'value' => 'Pragma',
            ),
            array(
                'label' => 'Proxy-Authorization',
                'value' => 'Proxy-Authorization',
            ),
            array(
                'label' => 'Range',
                'value' => 'Range',
            ),
            array(
                'label' => 'Referer',
                'value' => 'Referer',
            ),
            array(
                'label' => 'TE',
                'value' => 'TE',
            ),
            array(
                'label' => 'User-Agent',
                'value' => 'User-Agent',
            ),
            array(
                'label' => 'Upgrade',
                'value' => 'Upgrade',
            ),
            array(
                'label' => 'Via',
                'value' => 'Via',
            ),
            array(
                'label' => 'Warning',
                'value' => 'Warning',
            ),
        );
    }
}
