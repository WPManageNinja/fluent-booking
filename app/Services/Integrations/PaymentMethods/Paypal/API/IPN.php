<?php

namespace FluentBooking\App\Services\Integrations\PaymentMethods\Paypal\API;

use FluentBooking\App\Services\Integrations\PaymentMethods\Paypal\PaypalSettings;
use FluentBooking\Framework\Support\Arr;

class IPN
{
    public function verifyIPN()
    {
        status_header(200);

        if (!isset($_REQUEST['fluent_booking_payment_listener'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] != 'POST') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $post_data = '';

        if (ini_get('allow_url_fopen')) {
            $post_data = file_get_contents('php://input');
        }

        $encoded_data = 'cmd=_notify-validate';

        $arg_separator = ini_get('arg_separator.output');

        if ($post_data || strlen($post_data) > 0) {
            $encoded_data .= $arg_separator . $post_data;
        } else {
            if (!$_POST) {
                return;
            }
            foreach ($_POST as $key => $value) {
                $encoded_data .= $arg_separator . "$key=" . urlencode($value);
            }
        }

        // Convert collected post data to an array
        parse_str($encoded_data, $encoded_data_array);

        foreach ($encoded_data_array as $key => $value) {
            if (false !== strpos($key, 'amp;')) {
                $new_key = str_replace('&amp;', '&', $key);
                $new_key = str_replace('amp;', '&', $new_key);
                unset($encoded_data_array[$key]);
                $encoded_data_array[$new_key] = $value;
            }
        }

        $encoded_data_array = apply_filters('fluent_booking/process_paypal_ipn_data', $encoded_data_array);

        $bookingId = intval(Arr::get($_GET, 'booking_id'));

        $defaults = array(
            'txn_type'       => '',
            'payment_status' => '',
            'custom'         => ''
        );

        $encoded_data_array = wp_parse_args($encoded_data_array, $defaults);

        do_action('fluent_booking/ipn_paypal_action_web_accept', $encoded_data_array, $bookingId);

        exit(200);
    }
}
