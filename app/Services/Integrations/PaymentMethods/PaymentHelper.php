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
                'fluent-booking'=> 'calendar',
                'type' => 'confirmation',
                'booking_token' => $booking->hash,
                'method' => $this->slug,
            ),
            is_array($args)? $args:[]
        );
        return add_query_arg($queryArgs, $booking->source_url);

    }

    public static function getReceiptTemplate($items): array
    {
        $sign = CurrenciesHelper::getGlobalCurrencySign();

        $total = 0;
        $template = '';
        if (count($items) <= 1) {
            $template .= $items[0]['title'] . ': ' . $sign .' '. $items[0]['value'];
            $total = $items[0]['value'];
        } else {
            $template = '<table>';
            $template .= '<tr><th>Item</th><th>Price</th></tr>';
            foreach ($items as $item) {
                $total += floatval($item['value']);
                $template .= '<tr><td>' . $item['title'] . '</td><td>' .$sign.' '. $item['value'] . '</td></tr>';
            }
            $template .= '<tr><td>Total</td><td>' . $sign .' '. $total . '</td></tr>';
            $template .= '</table>';
        }

        return [
            'total' => $total,
            'template' => $template,
        ];
    }
}
