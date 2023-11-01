<?php

namespace FluentBooking\App\Services\Integrations\Woo;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class Bootstrap
{
    public function register()
    {
        add_filter('fluent_booking/booking_data', function ($bookingData, $calendarSlot) {
            if ($calendarSlot->type != 'woo' || Arr::get($bookingData, 'source') != 'web' || !$this->isEnabled()) {
                return $bookingData;
            }

            $wooProductId = $this->getEventProductId($calendarSlot);

            if (!$wooProductId) {
                return $bookingData;
            }

            $wooProduct = wc_get_product($wooProductId);

            if (!$wooProduct || !$wooProduct->get_id()) {
                return $bookingData;
            }

            $bookingData['source'] = 'woo';
            $bookingData['payment_method'] = 'woocommerce';
            $bookingData['status'] = 'pending'; // we are making it pending

            add_filter('fluent_booking/booking_confirmation_response', function ($response, $booking) use ($wooProductId) {
                if ($booking->status != 'pending' || $booking->source != 'woo') {
                    return $response;
                }

                if (apply_filters('fluent_booking/will_refresh_woo_cart', true, $booking, $wooProductId)) {
                    WC()->cart->empty_cart();
                }

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
                $response['data']['redirect_message'] = __('You are redirecting to checkout page to complete the appointment.', 'fluent-booking-pro');

                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'info',
                    'title'       => __('Redirect to WooCommerce checkout page', 'fluent-booking-pro'),
                    'description' => __('User redirected to Woo checkout page to comeplete the order.', 'fluent-booking-pro')
                ]);

                return $response;
            }, 10, 2);

            return $bookingData;
        }, 10, 2);

        add_action('fluent_booking/landing_page_route_woo-checkout', [$this, 'modifyCheckout']);
        add_filter('woocommerce_get_item_data', function ($item, $itemData) {
            if (!$this->isEnabled() || empty($itemData['fcal_id'])) {
                return $item;
            }
            $item['fluent_booking'] = [
                'key'     => __('Appointment', 'fluent-booking-pro'),
                'display' => Arr::get($itemData, 'booking_time') . ' (' . Arr::get($itemData, 'guest_timezone') . ')'
            ];
            return $item;
        }, 10, 2);

        add_action('woocommerce_order_status_changed', [$this, 'maybeBookingOrderStatusChanged'], 10, 4);

        add_filter('woocommerce_checkout_create_order_line_item_object', function ($item, $cart_item_key, $values, $order) {
            if (!$this->isEnabled() || empty($values['fcal_id'])) {
                return $item;
            }

            $fcalId = (int)Arr::get($values, 'fcal_id');
            if (!$fcalId) {
                return $item;
            }

            $order->update_meta_data('__fcal_booking_id', $fcalId);
            $item->add_meta_data('__fcal_booking_id', $fcalId);
            return $item;
        }, 10, 4);

        add_filter('woocommerce_hidden_order_itemmeta', function ($items) {
            $items[] = '__fcal_booking_id';
            return $items;
        });
    }

    public function modifyCheckout($data)
    {
        if (!$this->isEnabled()) {
            return;
        }

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
        }, 100);
    }

    public function maybeBookingOrderStatusChanged($orderId, $from, $to, $order)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $paidStatuses = wc_get_is_paid_statuses();
        if (in_array($to, $paidStatuses)) {
            $fcalBookingId = $order->get_meta('__fcal_booking_id');
            if (!$fcalBookingId) {
                return;
            }
            $booking = Booking::find($fcalBookingId);
            if (!$booking || $booking->source_id) {
                return;
            }

            if ($booking->status != 'pending') {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'success',
                    'title'       => __('Woo: Booking status could not be changed', 'fluent-booking-pro'),
                    'description' => __(sprintf(
                        'Booking status could not changed as it\'s in %1s status. %2sView Order%3s',
                        $booking->status,
                        '<a target="_blank" href="' . $order->get_edit_order_url() . '">',
                        '</a>'
                    ), 'fluent-booking-pro')
                ]);
                return;
            }

            $booking->status = 'scheduled';
            $booking->source_id = $order->get_id();
            $booking->save();

            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'success',
                'title'       => __('Woo: Booking status changed to scheduled', 'fluent-booking-pro'),
                'description' => __(sprintf(
                    'Woocommerce order status changed to %1s and booking status changed to scheduled. %2sView Order%3s',
                    $to,
                    '<a target="_blank" href="' . $order->get_edit_order_url() . '">',
                    '</a>'
                ), 'fluent-booking-pro')
            ]);

            $bookingData = [
                'name'  => $booking->first_name . ' ' . $booking->last_name,
                'email' => $booking->email,
                'phone' => $booking->phone
            ];

            // this pre hook is for early actions that require for remote calendars and locations
            do_action('fluent_booking/pre_after_booking_' . $booking->status, $booking, $booking->calendar_event, $bookingData);

            do_action('fluent_booking/after_booking_' . $booking->status, $booking, $booking->calendar_event, $bookingData);

            // Order Comment
            $order->add_order_note(
                sprintf(
                    __('Booking #%1s status changed to scheduled at %2s. %3sView Booking%4s', 'fluent-booking-pro'),
                    $booking->id,
                    $booking->getFullBookingDateTimeText($booking->calendar->author_timezone, true) . ' (' . $booking->calendar->author_timezone . ')',
                    '<a target="_blank" href="' . Helper::getAppBaseUrl('scheduled-events?period=upcoming&booking_id=' . $booking->id) . '">',
                    '</a>'
                )
            );
            return;
        }

        if ($to != 'refunded') {
            return;
        }

        // Let's cancel the booking if any
        $fcalBookingId = $order->get_meta('__fcal_booking_id');

        if (!$fcalBookingId) {
            return;
        }

        $booking = Booking::find($fcalBookingId);

        if (!$booking || $booking->status != 'scheduled') {
            return;
        }

        $booking->cancelMeeting(__('Cancelled by WooCommerce Order', 'fluent-booking-pro'), 'guest', get_current_user_id());

        $order->add_order_note(
            sprintf(
                __('Booking #%1s status changed to cancelled. %2sView Booking%3s', 'fluent-booking-pro'),
                $booking->id,
                '<a target="_blank" href="' . Helper::getAppBaseUrl('scheduled-events?period=upcoming&booking_id=' . $booking->id) . '">',
                '</a>'
            ));

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'error',
            'title'       => __('Woo: Booking status changed to cancelled', 'fluent-booking-pro'),
            'description' => __(sprintf(
                'Woocommerce order status changed to %1s and booking status changed to cancelled. %2sView Order%3s',
                $to,
                '<a target="_blank" href="' . $order->get_edit_order_url() . '">',
                '</a>'
            ), 'fluent-booking-pro')
        ]);
    }

    private function isEnabled()
    {
        return Helper::isModuleEnabled('woo');
    }

    private function getEventProductId($calendarSlot)
    {
        $paymentSettings = $calendarSlot->getMeta('payment_settings');

        if (!$paymentSettings || empty($paymentSettings['woo_product_id']) || Arr::get($paymentSettings, 'enabled') != 'yes' || Arr::get($paymentSettings, 'driver') != 'woo') {
            return null;
        }

        return (int)$paymentSettings['woo_product_id'];
    }
}
