<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Http\Controllers\Controller;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\PaymentMethods\CurrenciesHelper;
use FluentBooking\Framework\Request\Request;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Validator\ValidationException;
use FluentBooking\App\Hooks\Handlers\GlobalPaymentHandler;


class PaymentMethodController extends Controller
{
    public function index(Request $request, GlobalPaymentHandler $globalHandler)
    {
        try {
            $gateways = $globalHandler->getAll();
        } catch (\Exception $error) {
            return $this->sendError([
                'message' => $error->getMessage()
            ], 422);
        }

        return [
            'gateways' => $gateways
        ];
    }

    public function store(Request $request)
    {

        if ($request->get('method') == 'stripe') {
            $settings = $request->get('settings', []);
            $isActive = Arr::get($settings, 'is_active') === 'yes';
            $paymentMode = Arr::get($settings, 'payment_mode', 'test');

            if ($isActive) {
                if (empty($settings[$paymentMode . '_publishable_key']) || empty($settings[$paymentMode . '_secret_key'])) {
                    return $this->sendError([
                        'message' => __('Please connect your Stripe account first.', 'fluent-booking-pro')
                    ]);
                }

                if (!Arr::get($settings, 'currency')) {
                    return $this->sendError([
                        'message' => __('Please connect your Stripe account first.', 'fluent-booking-pro')
                    ]);
                }
            }
        }

        $data = $request->settings;

        $currency = Arr::get($data, 'currency');
        $isActive = Arr::get($data, 'is_active');
        update_option('fluent_booking_global_payment_settings', [
            'currency'  => sanitize_textarea_field($currency),
            'is_active' => ($isActive == 'yes') ? 'yes' : 'no'
        ], 'no');

        $method = sanitize_text_field($request->method);

        do_action('fluent_booking/payment/payment_settings_update_' . $method, $data);
    }

    public function getSettings(Request $request, GlobalPaymentHandler $globalHandler)
    {
        try {
            return $globalHandler->getSettings($request->method);
        } catch (\Exception $error) {
            return $this->sendError([
                'message' => $error->getMessage()
            ], 422);
        }
    }

    public function currencies(Request $request, GlobalPaymentHandler $globalHandler)
    {
        try {
            return $globalHandler->currencies();
        } catch (\Exception $error) {
            return $this->sendError([
                'message' => $error->getMessage()
            ], 422);
        }
    }

    public function connectInfo(Request $request, GlobalPaymentHandler $globalHandler)
    {
        return $globalHandler->connectInfo(sanitize_text_field($request->method));
    }

    public function disconnect(Request $request, GlobalPaymentHandler $globalHandler)
    {
        return $globalHandler->disconnect(
            sanitize_text_field($request->method),
            sanitize_text_field($request->mode),
        );
    }

    public function getCalendarEventSettings($id, $event_id)
    {
        $calendarSlot = CalendarSlot::findOrFail($event_id);

        $data = [
            'settings' => $calendarSlot->getPaymentSettings(),
            'config'   => [
                'native_enabled'     => Helper::isPaymentEnabled(),
                'native_config_link' => Helper::getAppBaseUrl('settings/configure-integrations/payment/stripe'),
                'woo_config_link'    => Helper::getAppBaseUrl('settings/configure-integrations/global-modules'),
                'has_woo'            => defined('WC_PLUGIN_FILE'),
                'woo_enabled'        => defined('WC_PLUGIN_FILE') && Helper::isModuleEnabled('woo')
            ]
        ];

        return $data;
    }

    public function updateSettings($id, $event_id)
    {
        $data = $this->request->settings;
        $event = CalendarSlot::findOrFail($event_id);

        if (!$event) {
            return $this->sendError([
                'message' => __('Calendar not found', 'fluent-booking-pro')
            ], 422);
        }

        $isEnabled = Arr::get($data, 'enabled') === 'yes';

        $driver = Arr::get($data, 'driver');
        $eventType = $isEnabled ? 'paid' : 'free';
        $data['currency_sign'] = CurrenciesHelper::getGlobalCurrencySign();

        if ($isEnabled) {
            if (!$driver) {
                return $this->sendError([
                    'message' => __('Please select a payment method', 'fluent-booking-pro')
                ], 422);
            }

            if ($driver == 'woo') {
                $productId = Arr::get($data, 'woo_product_id');
                if (!$productId) {
                    return $this->sendError([
                        'message' => __('Please select a product', 'fluent-booking-pro')
                    ], 422);
                }

                $product = wc_get_product($productId);
                if (!$product || !$product->get_id()) {
                    return $this->sendError([
                        'message' => __('Product not found. Please select a product', 'fluent-booking-pro')
                    ], 422);
                }

                $eventType = 'woo';
            }
        }

        $event->type = $eventType;
        $event->save();

        $event->updateMeta('payment_settings', $data);

        return $this->sendSuccess([
                'message' => __('Settings updated successfully', 'fluent-booking-pro')
            ]
        );
    }

    public function getWooProducts(Request $request)
    {
        if (!defined('WC_PLUGIN_FILE')) {
            return $this->sendError([
                'message' => __('WooCommerce is not installed', 'fluent-booking-pro')
            ], 422);
        }

        $products = wc_get_products([
            's'       => $request->getSafe('search', 'sanitize_text_field'),
            'limit'   => 100,
            'orderby' => 'title',
            'status'  => ['publish'],
            'order'   => 'ASC',
            'type'    => ['simple']
        ]);

        $data = [];

        $includeId = $request->getSafe('include_id', 'intval');

        $hadIncludeId = false;

        foreach ($products as $product) {
            $productId = (string)$product->get_id();
            if ($productId == $includeId) {
                $hadIncludeId = true;
            }
            $data[] = [
                'id'    => $productId,
                'title' => $product->get_title(),
                'price' => $product->get_price()
            ];
        }

        if (!$hadIncludeId) {
            $product = wc_get_product($includeId);
            if ($product) {
                $data[] = [
                    'id'    => $includeId,
                    'title' => $product->get_title(),
                    'price' => $product->get_price()
                ];
            }
        }

        return [
            'items'    => $data,
            'currency' => get_woocommerce_currency_symbol()
        ];
    }
}
