<?php
namespace FluentBooking\App\Services\Integrations\PaymentMethods;

class PaymentHelper
{
    public $slug = '';

    public function __construct($slug)
    {
        $this->slug = $slug;
    }

    public function listenerUrl($args = null)
    {
        $listener = '?fluent_booking_payment_listener=1&method=' . $this->slug;
        $listener = apply_filters('fluent_booking_ipn_url_' . $this->slug, site_url($listener));
        return add_query_arg($listener, is_array($args)?$args:[]);
    }

    public function successUrl($booking, $args = null)
    {
        $queryArgs =  array_merge(
            array(
                'method' => $this->slug,
                'order_hash' => $booking->hash,
                'fluent_booking_redirect' => 'yes'
            ),
            is_array($args)? $args:[]
        );
        return add_query_arg($queryArgs, $booking->source_url);

    }
}
