<?php
namespace FluentBooking\App\Hooks\Handlers;

use FluentBooking\App\Models\Webhook;
use FluentBooking\App\Services\EditorShortCodeParser;

class WebhookHandler {
    public function processWebhookResponseForBooking($booking, $type)
    {
        $slot_id     = $booking['event_id'];

        $webhook_metas = Webhook::where('object_id', $slot_id)->where('object_type', 'webhook')->get();

        foreach ($webhook_metas as $meta) {

            if ($type == 'scheduled' && in_array('after_booking_scheduled', $meta->value['event_triggers'])) {
                $this->processWebhook($meta, $booking);
            } else if ($type == 'cancelled' && in_array('booking_schedule_cancelled', $meta->value['event_triggers'])) {
                $this->processWebhook($meta, $booking);
            } else if ($type == 'completed' && in_array('booking_schedule_completed', $meta->value['event_triggers'])) {
                $this->processWebhook($meta, $booking);
            }

        }

    }


    /**
     * Process a webhook request for a booking.
     *
     * @param mixed $meta     Metadata containing webhook configuration.
     * @param mixed $booking  The booking object to be used in the webhook.
     */
    public function processWebhook($meta, $booking)
    {
        $remoteUrl = $meta->value['request_url'];
        if (\FluentBooking\Framework\Support\Arr::isTrue($meta, 'value.enabled') && !empty($remoteUrl)) {

            $body = [];

            if($meta->value['request_body'] == 'selected_fields') {
                foreach ($meta->value['fields'] as $item) {
                    if (empty($item['key']) || empty($item['value'])) {
                        continue;
                    }
                    $body[$item['key']] = EditorShortCodeParser::parse($item['value'], $booking);;
                }
            }

            $headers = [];
            if ($meta->value['with_header'] == 'yup') {
                foreach ($meta->value['request_headers'] as $item) {
                    if (empty($item['key']) || empty($item['value'])) {
                        continue;
                    }
                    $headers[$item['key']] = $item['value'];
                }
            }

            $sendingMethod = $meta->value['request_method'];
            $isJson = 'no';
            if ($meta->value['request_format'] == 'JSON' && $sendingMethod == 'POST') {
                $isJson = 'yes';
                $headers['Content-Type'] = 'application/json; charset=utf-8';
            }

            if ($sendingMethod == 'GET') {
                $remoteUrl = add_query_arg($body, $remoteUrl);
            }

            $data = [
                'payload'       => [
                    'body'      => $body,
                    'method'    => $sendingMethod,
                    'headers'   => $headers
                ],
                'remote_url'  => $remoteUrl,
                'booking_id'  => $booking->id,
                'event_id'     => $booking->event_id,
                'calendar_id' => $booking->calendar_id,
                'is_json'     => $isJson
            ];

            \FluentBooking\App\Services\Helper::fluentbooking_queue_on_background('fluentbooking_run_http_send_data_request_process', $data);

            $this->requestProcess($data);
        }
    }


    /**
     * Send a remote HTTP request and process the response.
     *
     * @param array $data An array containing request parameters.
     *
     * @return bool True if the request was sent successfully, false otherwise.
     */
    public function requestProcess($data)
    {
        if ($data['is_json'] == 'yes') {
            $data['payload']['body'] = json_encode($data['payload']['body']);
        }
        $response = wp_remote_request($data['remote_url'], $data['payload']);


        $responseBody = wp_remote_retrieve_body($response);
        if (is_array($responseBody)) {
            json_encode($responseBody);
        }

        return true;
    }


    /**
     * This method handles a background process callback.
     *
     * @return void
     */
    public function handleBackgroundProcessCallback()
    {
        $callbackName = sanitize_text_field($_REQUEST['callback_name']);
        if (!wp_verify_nonce($_REQUEST['nonce'], 'fluent_booking_callback_for_background')) {
            die('Security Check Failed');
        }
        $data = $_REQUEST['payload'];
        do_action($callbackName, $data);
        echo 'success';
        die();
    }
}
