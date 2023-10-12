<?php

namespace FluentBooking\App\Services\Integrations\PaymentMethods\Stripe\API;

use FluentBooking\App\Services\Integrations\PaymentMethods\Stripe\StripeSettings;
use FluentBooking\Framework\Support\Arr;

class API
{
    private $createSessionUrl;
    private $apiUrl = 'https://api.stripe.com/v1/';

    public function makeRequest($path, $data, $apiKey, $method = 'GET')
    {
        $stripeApiKey = $apiKey;
        $sessionHeaders = array(
            'Authorization' => 'Bearer ' . $stripeApiKey,
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $requestData = array(
            'headers' => $sessionHeaders,
            'body' => http_build_query($data),
            'method' => $method,
        );

        $url = $this->apiUrl . $path;

        $sessionResponse = wp_remote_post($url, $requestData);

        if (is_wp_error($sessionResponse)) {
            echo "API Error: " . esc_html($sessionResponse->get_error_message());
            exit;
        }

        $sessionResponseData = wp_remote_retrieve_body($sessionResponse);

        $sessionData = json_decode($sessionResponseData, true);

        if (empty($sessionData['id'])) {
            $message = Arr::get($sessionData, 'detail');
            if (!$message) {
                $message = Arr::get($sessionData, 'error.message');
            }
            if (!$message) {
                $message = 'Unknown Stripe API request error';
            }

            return new \WP_Error(423, $message, $sessionData);
        }

        return $sessionData;
    }


    public function verifyIPN()
    {
        if (!isset($_REQUEST['fluent_booking_payment_listener'])) {
            return;
        }

        $post_data = '';
        if (ini_get('allow_url_fopen')) {
            $post_data = file_get_contents('php://input');
        } else {
            // If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
            ini_set('post_max_size', '12M');
        }

        $post_data = "{\n  \"id\": \"evt_3NzZWEIyGOUknw8l0641zDR7\",\n  \"object\": \"event\",\n  \"api_version\": \"2018-11-08\",\n  \"created\": 1696919668,\n  \"data\": {\n    \"object\": {\n      \"id\": \"ch_3NzZWEIyGOUknw8l0jix73Lw\",\n      \"object\": \"charge\",\n      \"amount\": 50000,\n      \"amount_captured\": 50000,\n      \"amount_refunded\": 0,\n      \"application\": \"ca_K5OP4naU0e54TsLIyYJwX0eIuQqDaqpB\",\n      \"application_fee\": null,\n      \"application_fee_amount\": null,\n      \"balance_transaction\": \"txn_3NzZWEIyGOUknw8l0iRHSF6C\",\n      \"billing_details\": {\n        \"address\": {\n          \"city\": null,\n          \"country\": \"BD\",\n          \"line1\": null,\n          \"line2\": null,\n          \"postal_code\": null,\n          \"state\": null\n        },\n        \"email\": null,\n        \"name\": null,\n        \"phone\": null\n      },\n      \"calculated_statement_descriptor\": \"WPMINERS.COM\",\n      \"captured\": true,\n      \"created\": 1696919668,\n      \"currency\": \"usd\",\n      \"customer\": null,\n      \"description\": null,\n      \"destination\": null,\n      \"dispute\": null,\n      \"disputed\": false,\n      \"failure_balance_transaction\": null,\n      \"failure_code\": null,\n      \"failure_message\": null,\n      \"fraud_details\": {\n      },\n      \"invoice\": null,\n      \"livemode\": false,\n      \"metadata\": {\n        \"ref_id\": \"6cff19ab8d3739148edacce8306bcef0\"\n      },\n      \"on_behalf_of\": null,\n      \"order\": null,\n      \"outcome\": {\n        \"network_status\": \"approved_by_network\",\n        \"reason\": null,\n        \"risk_level\": \"normal\",\n        \"risk_score\": 45,\n        \"seller_message\": \"Payment complete.\",\n        \"type\": \"authorized\"\n      },\n      \"paid\": true,\n      \"payment_intent\": \"pi_3NzZWEIyGOUknw8l0OK9VCj6\",\n      \"payment_method\": \"pm_1NzZWRIyGOUknw8lDyCEWTZy\",\n      \"payment_method_details\": {\n        \"card\": {\n          \"amount_authorized\": 50000,\n          \"brand\": \"visa\",\n          \"checks\": {\n            \"address_line1_check\": null,\n            \"address_postal_code_check\": null,\n            \"cvc_check\": \"pass\"\n          },\n          \"country\": \"US\",\n          \"exp_month\": 2,\n          \"exp_year\": 2032,\n          \"extended_authorization\": {\n            \"status\": \"disabled\"\n          },\n          \"fingerprint\": \"3sxbEEqW9pdXsP31\",\n          \"funding\": \"credit\",\n          \"incremental_authorization\": {\n            \"status\": \"unavailable\"\n          },\n          \"installments\": null,\n          \"last4\": \"4242\",\n          \"mandate\": null,\n          \"multicapture\": {\n            \"status\": \"unavailable\"\n          },\n          \"network\": \"visa\",\n          \"network_token\": {\n            \"used\": false\n          },\n          \"overcapture\": {\n            \"maximum_amount_capturable\": 50000,\n            \"status\": \"unavailable\"\n          },\n          \"three_d_secure\": null,\n          \"wallet\": null\n        },\n        \"type\": \"card\"\n      },\n      \"receipt_email\": null,\n      \"receipt_number\": null,\n      \"receipt_url\": \"https:\/\/pay.stripe.com\/receipts\/payment\/CAcaFwoVYWNjdF8xRTFxcmVJeUdPVWtudzhsKPXgk6kGMgYM153mv1Y6LBZRLRWafp_q2poje_3R2Sw5byMDehPjZLoE0TRwjvfK_C-qxGrP2mwshVoK\",\n      \"refunded\": false,\n      \"refunds\": {\n        \"object\": \"list\",\n        \"data\": [\n\n        ],\n        \"has_more\": false,\n        \"total_count\": 0,\n        \"url\": \"\/v1\/charges\/ch_3NzZWEIyGOUknw8l0jix73Lw\/refunds\"\n      },\n      \"review\": null,\n      \"shipping\": null,\n      \"source\": null,\n      \"source_transfer\": null,\n      \"statement_descriptor\": null,\n      \"statement_descriptor_suffix\": null,\n      \"status\": \"succeeded\",\n      \"transfer_data\": null,\n      \"transfer_group\": null\n    }\n  },\n  \"livemode\": false,\n  \"pending_webhooks\": 3,\n  \"request\": {\n    \"id\": \"req_8cGSp8kAF0HCE3\",\n    \"idempotency_key\": \"57369ae2-2ca9-4226-83e0-784352db1faf\"\n  },\n  \"type\": \"charge.succeeded\"\n}";

        error_log('ipn data received');

        $data =  json_decode($post_data);

        if ($data->id) {
            status_header(200);
            return $data;
        } else {
            error_log("specific event");
            error_log(print_r($data));
            return false;
        }

        exit(200);
    }

    public function getInvoice($eventId)
    {
        $api = new ApiRequest();
        $api::set_secret_key((new StripeSettings())->getApiKey());
        return $api::request([], 'events/' . $eventId, 'GET');
    }
}
