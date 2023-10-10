<?php

namespace FluentBooking\App\Hooks\Handlers;

use FluentBooking\App\Services\Integrations\PaymentMethods\Stripe\Stripe;
use FluentBooking\App\Services\Integrations\PaymentMethods\Stripe\ConnectConfig;
use FluentBooking\Framework\Support\Arr;

class GlobalPaymentHandler
{
    public function register()
    {
        add_action('fluent_booking_loaded', [$this, 'init']);
    }

    public function init()
    {
        (new Stripe())->init();

        //This hook will allow others to register their payment method with ours
        do_action('fluent_booking/register_payment_methods');

        $this->verifyStripeConnect();
        $this->initIpnListener();
    }

    public function initIpnListener()
    {
        if (isset($_REQUEST['fluent_booking_payment_listener'])) {
            add_action('wp', function () {
                $paymentMethod = sanitize_text_field($_REQUEST['method']);
                do_action('fluent_booking/payment/ipn_endpoint_' . $paymentMethod);
            });
        }
    }

    public function verifyStripeConnect()
    {
        if (isset($_GET['ff_stripe_connect'])) {
            $data = Arr::only($_GET, ['ff_stripe_connect', 'mode', 'state', 'code']);
            ConnectConfig::verifyAuthorizeSuccess($data);
        }
    }

    public function connectInfo($method)
    {
        return apply_filters('fluent_booking/get_payment_connect_info_' . sanitize_text_field($method), []);
    }

    public function disconnect($method, $mode)
    {
        return apply_filters('fluent_booking/get_payment_settings_disconnect_' . $method, ['mode' => sanitize_text_field($mode)]);
    }

    public function getSettings($method)
    {
        return apply_filters('fluent_booking/payment/get_global_payment_settings_' . sanitize_text_field($method), []);
    }

    public function getAll()
    {
        return apply_filters('fluent_booking/payment/get_global_payment_methods', []);
    }

}
