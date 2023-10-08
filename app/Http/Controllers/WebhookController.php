<?php
namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Request\Request;
use FluentBooking\App\Models\Webhook;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Validator\ValidationException as Exception;

class WebhookController extends Controller
{
    private $isJsonValue = true;

    public function index(Request $request)
    {
        try {

            return $this->sendSuccess([
                'event_triggers'  => $this->eventTriggers(),
                'webhooks' => $this->getAll($request),
                'request_headers' => $this->getHeaders()
            ]);

        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function create(Request $request)
    {
        try {
            $slot_id = $this->app->request->get('slot_id');
            $webhook_id = $this->app->request->get('webhook_id');
            $webhook = $this->app->request->get('webhook');
//            $webhook = json_decode($webhook, true);

            $webhook = Helper::fluentBookingSanitizer(
                $this->validate($webhook,[])
            );
            if ($webhook_id) {

                $webhookUpdate = Webhook::where('id', $webhook_id)->first();
                $webhookUpdate->value = $webhook;
                $webhookUpdate->save();

                $message = __('WebHook Successfully Updated', 'fluent-booking');
            } else {
                Webhook::store($slot_id, $webhook);
                $message = __('Successfully created the WebHook', 'fluent-booking');

            }

            return $this->sendSuccess([
                'webhook_id' => $webhook_id,
                'message'  => $message
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateData(Request $request)
    {
        try {
            $webhook = $request->get('webhook');
            $id = $request->get('id');

            $webhook = Helper::fluentBookingSanitizer(
                $this->validate($webhook,[])
            );
            $webhookUpdate = Webhook::where('id', $id)->first();
            $webhookUpdate->value = $webhook;
            $webhookUpdate->save();

            return $this->sendSuccess([
                'message'  => __('WebHook Successfully Updated', 'fluent-booking')
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function delete(Webhook $webhook, $id)
    {
        try {
            $webhook->where('id', $id)->delete();

            return $this->sendSuccess([
                'message' => __('Successfully Deleted the Webhook', 'fluent-booking')
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function eventTriggers()
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
                'label' => 'Accept',
                'value' => 'Accept',
                'possible_values' => [
                    'title' => 'Accep Header Samples',
                    'shortcodes' => [
                        'Accept: text/plain' => 'text/plain',
                        'Accept: text/html' => 'text/html',
                        'Accept: text/*' => 'text/*'
                    ]
                ]
            ),
            array(
                'label' => 'Accept-Charset',
                'value' => 'Accept-Charset',
                'possible_values' => [
                    'title' => 'Accep-Charset Header Samples',
                    'shortcodes' => [
                        'Accept-Charset: utf-8' => 'utf-8',
                        'Accept-Charset: iso-8859-1' => 'iso-8859-1'
                    ]
                ]
            ),
            array(
                'label' => 'Accept-Encoding',
                'value' => 'Accept-Encoding',
                'possible_values' => [
                    'title' => 'Accept-Encoding Header Samples',
                    'shortcodes' => [
                        'Accept-Encoding: gzip' => 'gzip',
                        'Accept-Encoding: compress' => 'compress',
                        'Accept-Encoding: deflate' => 'deflate',
                        'Accept-Encoding: br' => 'br',
                        'Accept-Encoding: identity' => 'identity',
                        'Accept-Encoding: *' => '*'
                    ]
                ]
            ),
            array(
                'label' => 'Accept-Language',
                'value' => 'Accept-Language',
                'possible_values' => [
                    'title' => 'Accept-Language Header Samples',
                    'shortcodes' => [
                        'Accept-Language: en' => 'en',
                        'Accept-Language: en-US' => 'en-US',
                        'Accept-Language: en-GR' => 'en-GR',
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

    public function getAll($request)
    {
        $slot_id       = $request->get('slot_id');
        $settingsQuery = Webhook::where('object_id', $slot_id)->get();


        foreach ($settingsQuery as $setting) {

            $setting->enabled = Arr::isTrue($setting, 'value.enabled');

//            $setting->value = $setting->value;
        }
        return $settingsQuery;
    }
}