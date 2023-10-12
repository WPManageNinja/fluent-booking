<?php

namespace FluentBooking\App\Services\Integrations\PaymentMethods\Stripe;

class StripeSettings
{

    public $settings;

    protected $methodHandler = 'fluent_booking_payment_settings_stripe';

    public function __construct()
    {
        $settings = get_option($this->methodHandler, []);

        if (!$settings) {
            $defaults['provider'] = 'connect';
        }

        $settings = wp_parse_args($settings, static::getDefaults());

        if ($settings['provider'] == 'connect' && apply_filters('fluent_booking_form_disable_stripe_connect', false)) {
            $settings['provider'] = 'api_keys';
        }

        $this->settings = $settings;
    }

    /**
     * @return array with default fields value
     */
    public static function getDefaults()
    {
        return [
            'is_active'             => 'no',
            'test_publishable_key'  => '',
            'test_secret_key'       => '',
            'live_publishable_key'  => '',
            'live_secret_key'       => '',
            'payment_mode'          => 'test',
            'provider'              => 'api_keys',
            'test_account_id'       => '',
            'live_account_id'       => '',
            'checkout_mode'         => 'onsite'
        ];
    }

    public function isActive()
    {
        return $this->settings['is_active'] == 'yes';
    }

    public function get()
    {
        return $this->settings;
    }

    public function getMode()
    {
        return $this->settings['payment_mode'];
    }

    public function getPublicKey()
    {
        if ($this->getMode() === 'live') {
            return $this->get()['live_publishable_key'];
        } else {
            return $this->get()['test_publishable_key'];
        }
    }

    public function getApiKey()
    {
        if ($this->getMode() === 'live') {
            return $this->get()['live_secret_key'];
        } else {
            return $this->get()['test_secret_key'];
        }
    }
}
