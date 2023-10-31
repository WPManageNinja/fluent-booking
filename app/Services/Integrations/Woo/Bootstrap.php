<?php

namespace FluentBooking\App\Services\Integrations\Woo;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\Framework\Support\Arr;

class Bootstrap
{

    public function register()
    {
        add_filter('fluent_booking/booking_data', function ($bookingData, $calendarSlot) {
            if (Arr::get($bookingData, 'source') != 'web') {
                return $bookingData;
            }

            $wooProductId = $calendarSlot->getMeta('woo_product_id');

            if (!$wooProductId) {
                return $bookingData;
            }

            $bookingData['source'] = 'woo';
            $bookingData['status'] = 'pending'; // we are maing it pending

            add_filter('fluent_booking/booking_confirmation_response', function ($response, $booking) use ($wooProductId) {

                WC()->cart->empty_cart(true);
                WC()->cart->add_to_cart($wooProductId, 1, 0, [], [
                    'fcal_id'        => $booking->id,
                    'booking_time'   => DateTimeHelper::convertFromUtc($booking->start_time, $booking->person_time_zone),
                    'guest_timezone' => $booking->person_time_zone
                ]);

                $redirect = add_query_arg([
                    'fluent-booking' => 'woo-checkout',
                    'fcal_hash'      => $booking->hash
                ], wc_get_checkout_url());

                $response['data']['redirect_to'] = $redirect;
                return $response;
            }, 10, 2);

            return $bookingData;
        }, 10, 2);
        add_action('fluent_booking/landing_page_route_woo-checkout', [$this, 'modifyCheckout']);
        add_filter('woocommerce_get_item_data', function ($item, $itemData) {
            if (empty($itemData['fcal_id'])) {
                return $item;
            }
            $item['fluent_booking'] = [
                'key'     => 'Meeting',
                'display' => Arr::get($itemData, 'booking_time') . ' (' . Arr::get($itemData, 'guest_timezone') . ')'
            ];
            return $item;
        }, 10, 2);
    }

    public function modifyCheckout($data)
    {
        $bookingHash = sanitize_text_field($data['fcal_hash']);
        $booking = Booking::where('hash', $bookingHash)->first();
        if (!$booking) {
            return;
        }

        // set checkout field first name & last name
        add_filter('woocommerce_checkout_fields', function ($fields) use ($booking) {

            if (!empty($fields['billing']['billing_first_name'])) {
                $fields['billing']['billing_first_name']['default'] = $booking->first_name;
            }

            if (!empty($fields['billing']['billing_last_name']) && $booking->last_name) {
                $fields['billing']['billing_last_name']['default'] = $booking->last_name;
            }

            if (!empty($fields['billing']['billing_phone']) && $booking->phone) {
                $fields['billing']['billing_phone']['default'] = $booking->phone;
            }

            if (!empty($fields['billing']['billing_email']) && $booking->email) {
                $fields['billing']['billing_email']['default'] = $booking->email;
            }
            return $fields;
        }, 3000);


    }

    private function isEnabled()
    {
        return defined('WC_PLUGIN_FILE');
    }
}
