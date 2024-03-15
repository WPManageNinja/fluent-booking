<?php

namespace FluentBooking\App\Services\Integrations\PaymentMethods;

use FluentBooking\App\App;
use FluentBooking\App\Hooks\Handlers\GlobalPaymentHandler;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Order;
use FluentBooking\App\Models\Transactions;
use FluentBooking\App\Services\Integrations\PaymentMethods\PaymentHelper;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Validator\Validator;
use FluentBooking\App\Services\OrderHelper;


abstract class BasePaymentMethod implements BasePaymentInterface
{
    public $slug;

    public $title;

    public $logo;

    public $brandColor = '#ccc';

    protected $methodHandler;

    public static $methods = [];

    public static $routes = [];

    abstract public function getLogo();

    abstract public function getDescription();

    abstract public function getSettings();

    abstract public function fields();

    abstract public function validSettingKeys();

    /**
     * This method should return the name of the method that will be passed from webhook to listen payment events
     * TODO need testing, if it breaks something
     * eg: www.test-site.com/?fluent_booking_payment_listener='true'&method='nameReturnedFromTheMethod'
     * @return string
     */
    abstract public function webHookPaymentMethodName();

    abstract public function onPaymentEventTriggered();

    abstract public function makePayment($orderItem, $calendarSlot);

    public function resolveOrderHash($orderItem)
    {
        return $orderItem->hash;
    }

    public function __construct($title, $slug, $brandColor, $logo)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->brandColor = $brandColor;
        $this->logo = $logo;
        $this->methodHandler = 'fluent_booking_payment_settings_' . $slug;
    }

    public function init()
    {
        add_filter('fluent_booking/payment/get_global_payment_settings_' . $this->slug, [$this, 'globalFields']);
        add_filter('fluent_booking/payment/get_global_payment_methods', [$this, 'register']);
        add_action('fluent_booking/payment/payment_settings_update_' . $this->slug, [$this, 'update'], 10, 1);
        add_filter('fluent_booking/payment/payment_method_settings_routes', [$this, 'setRoutes']);
        add_action('fluent_booking/payment/pay_order_with_' . $this->slug, [$this, 'makePayment'], 10, 2);
        add_action('fluent_booking/payment/ipn_endpoint_' . $this->webHookPaymentMethodName(), [$this, 'onPaymentEventTriggered']);
        add_filter('fluent_booking/settings_menu_items', [$this, 'addGlobalMenu'], 12, 1);

        add_filter('fluent_booking/payment/get_all_methods', [$this, 'getAllMethods'], 10, 1);

        add_filter('fluent_booking/payment_methods_renderer', [$this, 'getMethodsTemplate'], 10, 1);

        add_filter('fluent_booking/public_event_vars', [$this, 'addPaymentRendererTemplates'], 10, 2);

        add_action('fluent_booking/pre_after_booking_pending', [$this, 'afterBookingPending'], 1, 3);

        add_filter('fluent_booking/booking_data', [$this, 'addPaymentMethodToBookingData'], 10, 3);
    }

    public function addPaymentMethodToBookingData($bookingData, $calendarSlot, $customData)
    {
        if (Arr::get($bookingData, 'source') != 'web') {
            return $bookingData;
        }

        if ($calendarSlot->type === 'paid') {
            $bookingData['status'] = 'pending';
            $bookingData['payment_status'] = 'pending';
            $bookingData['payment_method'] = Arr::get($customData, 'payment_method', '');
        }
        return $bookingData;
    }

    public function afterBookingPending($booking, $calendarSlot, $bookingData)
    {
        $paymentMethod = Arr::get($bookingData, 'payment_method', 'stripe');

        if ($calendarSlot->type === 'paid' && $booking->source === 'web' && $paymentMethod) {
            (new OrderHelper())->processDraftOrder($booking, $calendarSlot); // make draft order
            do_action('fluent_booking/payment/pay_order_with_' . sanitize_text_field($paymentMethod), $booking, $calendarSlot);
        }
    }

    public function getAllMethods()
    {
        static::$methods[$this->slug] = array(
            'title'  => $this->title,
            'image'  => $this->logo,
            "status" => $this->isEnabled(),
        );
        return static::$methods;
    }

    public function addPaymentRendererTemplates($vars, $slot)
    {
        $paymentSettings = $slot->getPaymentSettings();
        if (Arr::get($paymentSettings, 'enabled') === 'yes' && Arr::get($paymentSettings, 'driver') === 'native') {
            $vars['payment_methods'] = $this->getMethodsTemplate(['templates' => '']);
            $vars['payment_items'] = Arr::get($paymentSettings, 'items');
            $vars['currency_sign'] = CurrenciesHelper::getGlobalCurrencySign();
        }
        return $vars;
    }

    public function handleRedirectData()
    {
        return '';
    }

    public function addGlobalMenu($menuItems)
    {
        $menuItems[$this->slug] = [
            'title'          => $this->title,
            'component_type' => 'GlobalSettingsComponent',
            'icon_url'       => $this->logo,
            'route'          => [
                'name'   => 'PaymentSettingsIndex',
                'params' => [
                    'settings_key' => $this->slug
                ]
            ]
        ];
        return $menuItems;
    }

    public function setRoutes()
    {
        static::$routes[] = [
            'path' => $this->slug,
            'name' => $this->slug,
            'meta' => [
                'title' => $this->title
            ]
        ];
        return static::$routes;
    }

    public function register()
    {
        static::$methods[] = [
            "title"       => $this->title,
            "route"       => $this->slug,
            "description" => $this->getDescription(),
            "logo"        => $this->getLogo(),
            "status"      => $this->isEnabled(),
            "brand_color" => $this->brandColor
        ];

        return static::$methods;
    }

    public function getMode()
    {
        $settings = $this->getSettings();
        return Arr::get($settings, 'payment_mode', 'test');
    }

    public function getActiveStatus()
    {
        $settings = $this->getSettings();
        return Arr::get($settings, 'is_active') === 'yes' ? true : false;
    }

    public function hasLiveRefund()
    {
        return false;
    }

    public function getTitle($scope = 'admin')
    {
        return $this->title;
    }

    public function renderDescription()
    {
        echo '';
    }

    public function supportedCurrencies()
    {
        return ['*'];
    }

    public function processCartOrder($order)
    {
        return $order;
    }

    public function getVenodPaymentLink($payment)
    {
        return false;
    }

    public function capturePayment($payment)
    {
        $payment->updateStatus('paid');
        return $payment;
    }

    public function update($data)
    {
        wp_send_json_success(
            $this->updateSettings($data)
        );
    }

    public function updateSettings($data)
    {
        $settings = $this->getSettings();

        $settings = wp_parse_args($data, $settings);

        $settings = Arr::only($settings, $this->validSettingKeys());

        $settings = apply_filters('fluent_booking/payment/payment_settings_before_update_' . $this->slug, $settings);

        update_option($this->methodHandler, $settings, 'no');

        return $this->getSettings();
    }

    public function globalFields()
    {
        return [
            'fields'   => $this->fields(),
            'settings' => $this->getSettings()
        ];
    }

    public function sanitize($data, $fields)
    {
        foreach ($fields as $key => $value) {
            if (isset($data[$key])) {
                if ('email' === $value['type']) {
                    $data[$key] = sanitize_email($data[$key]);
                } else {
                    $data[$key] = sanitize_text_field($data[$key]);
                }
            }
        }

        return $data;
    }

    protected function getSuccessUrl($orderItem, $args = null)
    {
        return (new PaymentHelper($this->slug))->successUrl($orderItem, $args);
    }

    protected function getListenerUrl($args = null)
    {
        return (new PaymentHelper($this->slug))->listenerUrl($args);
    }

    public function updateOrderData($orderHash, $orderData = [])
    {
        $this->updateOrder($orderHash, $orderData);
        $this->updateTransaction($orderHash, $orderData);
        $this->updateBooking($orderHash, $orderData);
    }

    public function updateOrder($orderHash, $data)
    {
        $order = Order::where('uuid', $orderHash)->first();

        if (!$order) {
            return;
        }

        $order->update($data);
    }

    public function updateTransaction($orderHash, $data)
    {
        $transaction = Transactions::where('uuid', $orderHash)->first();

        if (!$transaction) {
            return;
        }
        
        $transaction->update($data);
    }

    public function updateBooking($orderHash, $data)
    {
        $booking = Booking::with(['calendar_event', 'calendar'])
            ->where('hash', $orderHash)
            ->first();

        if (!$booking) {
            return;
        }

        if ($data['status'] == 'paid') {
            $booking->status = 'scheduled';
        }

        $booking->payment_status = $data['status'];
        $booking->save();

        do_action('fluent_booking/payment/update_payment_status_' . $data['status'], $booking);

        if ($booking->status == 'scheduled') {
            do_action('fluent_booking/log_booking_activity', $this->getSuccessActivity($booking->id, $data));

            do_action('fluent_booking/pre_after_booking_scheduled', $booking, $booking->calendar_event);
            // We are just renewing this as this may have been changed by the pre hook
            $booking = Booking::with(['calendar_event', 'calendar'])->find($booking->id);
            do_action('fluent_booking/after_booking_scheduled', $booking, $booking->calendar_event);
        }

        if ($booking->status == 'pending') {
            do_action('fluent_booking/log_booking_activity', $this->getPendingActivity($booking->id, $data));
        }

        if ($booking->status == 'failed') {
            do_action('fluent_booking/log_booking_activity', $this->getFailedActivity($booking->id, $data));
        }
    }

    public function getSuccessActivity($bookingId, $data)
    {
        return [
            'booking_id'  => $bookingId,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Payment Successfully Completed', 'fluent-booking-pro'),
            'description' => sprintf(__('Transaction marked as paid and %s Transaction ID: %s ', 'fluent-booking-pro'), $this->title, $data['vendor_charge_id'])
        ];
    }

    public function getPendingActivity($bookingId, $data)
    {
        return [
            'booking_id'  => $bookingId,
            'status'      => 'info',
            'type'        => 'error',
            'title'       => sprintf(__('%s Payment Pending', 'fluent-booking-pro'), $this->title),
            'description' => $data['pending_reason']
        ];
    }

    public function getFailedActivity($bookingId, $data)
    {
        return [
            'booking_id'  => $bookingId,
            'status'      => 'closed',
            'type'        => 'error',
            'title'       => sprintf(__('%s Payment Failed', 'fluent-booking-pro'), $this->title),
            'description' => $data['failed_reason']
        ];
    }

    public function maybeUpdatePayment()
    {
        return false;
    }

    public function render($method)
    {
        return '';
    }

    public function getMethodsTemplate($data)
    {
        $methods = GlobalPaymentHandler::getAllMethods();

        $settings = $this->getSettings();
        if (isset($settings['is_active']) && $settings['is_active'] !== 'yes') {
            return $data['template'] = '<div class="fluent_booking_payment_methods">' . __('Please activate payment first!', 'fluent-booking-pro') . '</div>';
        }

        $templates = [
            'template' => '',
        ];

        $hasActiveMethod = false;
        $radio = "<div class='payment-methods-radio fluent_booking_payment_methods'><div style='display: flex; gap: 20px;'>" . __('Pay with:', 'fluent-booking-pro');
        foreach ($methods as $slug => $methodData) {
            if (isset($methodData['status']) && $methodData['status']) {
                $hasActiveMethod = true;
                $radio .= $this->render($slug);
            }
        }
        $radio .= "</div></div>";

        $templates['template'] = $radio;
        if (!$hasActiveMethod) {
            return $data['template'] = '<p style="color:#fb7373; font-size:16px; margin: 0 auto;">'. __('Please active at least one payment method!', 'fluent-booking-pro') .'</p>';
        }

        return $data['template'] = $templates;
    }

    protected function validate($data, array $rules = [])
    {
        $validator = (new Validator())->make($data, $rules);
        if ($validator->validate()->fails()) {
            wp_send_json_error($validator->errors(), 422);
        }
    }
}

