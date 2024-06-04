<?php
namespace FluentBooking\App\Services\Integrations\PaymentMethods;

use FluentBooking\App\Models\Booking;

class PaymentHelper
{
    public $slug = '';

    public function __construct($slug)
    {
        $this->slug = $slug;
    }

    public function listenerUrl($args = [])
    {
        $queryArgs = array_merge([
            'fluent_booking_payment_listener' => 1,
            'payment_method'                  => $this->slug
        ], is_array($args) ? $args : []);

        return add_query_arg($queryArgs, site_url('index.php'));
    }

    public function successUrl(Booking $booking, $calendarEvent, $args = null)
    {
        $queryArgs = array_merge([
                'payment_method' => $this->slug,
                'payment_success' => 'yes'
            ], is_array($args) ? $args: []
        );

        $redirectUrl = $booking->getRedirectUrlWithQuery();

        $confirmationUrl = $redirectUrl ?: $booking->getConfirmationUrl();

        return add_query_arg($queryArgs, $confirmationUrl);
    }

    public static function formatPaymentItem($string, $limit = 127)
    {
        $string = wp_strip_all_tags($string);

        $string = preg_replace('/[^a-zA-Z0-9\s]/', '', $string);

        $string = self::limitLength($string, $limit);

        return html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
    }

    public static function limitLength($string, $limit = 127)
    {
        if (strlen($string) > $limit) {
            $string = substr($string, 0, $limit - 3) . '...';
        }
        return $string;
    }

    public static function getReceiptTemplate($items)
    {
        $sign = CurrenciesHelper::getGlobalCurrencySign();

        $total = 0;
        $template = '';
        if (count($items) === 1) {
            $template .= '<p class="fcal_payment_item_single">'.$items[0]['title'] . ': ' . '<span class="amount">' . $sign . $items[0]['value'] . '</span>' . '</p>';
            $total = $items[0]['value'];
        } else {
            $template = '<table>';
            $template .= '<thead><tr><th>' . __('Item', 'fluent-booking-pro') . '</th><th>' . __('Price', 'fluent-booking-pro') . '</th></tr></thead><tbody>';
            foreach ($items as $item) {
                $total += floatval($item['value']);
                $template .= '<tr><td>' . $item['title'] . '</td><td>' .$sign . $item['value'] . '</td></tr>';
            }
            $template .= '</tbody><tfoot><tr><th>' . __('Total:', 'fluent-booking-pro') . '</th><th>' . $sign . $total . '</th></tr></tfoot>';
            $template .= '</table>';
        }

        return [
            'total' => $total,
            'template' => $template,
        ];
    }
}
