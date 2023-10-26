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

        add_filter('fluent_booking/payment/get_all_methods', array($this, 'getAllMethods'), 10, 1);

        add_filter('fluent_booking/payment_methods_renderer', array($this, 'getMethodsTemplate'), 10, 1);

        add_filter('fluent_booking/public_event_vars', array($this, 'addPaymentRendererTemplates'), 10, 2);

        add_action('fluent_booking/pre_after_booking_pending', array($this, 'afterBookingPending'), 1, 3);

        add_filter('fluent_booking/booking_data', array($this, 'addPaymentMethodToBookingData'), 10, 3);
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
            //make draft orders
            (new OrderHelper())->processDraftOrder($booking, $calendarSlot);
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
        $paymentSettings = $slot->getMeta('payment_settings');
        if (Arr::get($paymentSettings, 'enabled') === 'yes') {
            $vars['payment_methods'] = static::getMethodsTemplate(['templates' => '']);
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

    public function capturePayment(OrderTransaction $payment)
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

        $settings = apply_filters('fluent_booking/payment/payment_settings_before_update_' . $this->slug, $settings);

        update_option($this->methodHandler, $settings, 'no');

        $isActive = Arr::get($settings, 'is_active', 'no') == 'yes';

        $globalSettings = get_option('fluent_booking_global_payment_settings', []);

        if(!$globalSettings) {
            $globalSettings = [];
        }

        $globalSettings['is_active'] = $isActive ? 'yes' : 'no';

        update_option('fluent_booking_global_payment_settings', $globalSettings, 'no');

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
        $paymentHelper = new PaymentHelper($this->slug);
        return $paymentHelper->successUrl($orderItem, $args);
    }

    protected function getListenerUrl($args = null)
    {
        return (new PaymentHelper($this->slug))->listenerUrl($args);
    }

    public function updateOrderData($order, $transactionData = [])
    {
        $orderHash = $order->uuid;
        $order = (new OrderHelper())->getOrderByHash($orderHash);
        if ($order == null) {
            return;
        }
        $order->update($transactionData);

        $transaction = Transactions::where('object_id', $order->id)->where('uuid', $orderHash)->first();
        if ($transaction) {
            $transaction->update($transactionData);
        }

        $booking = Booking::where('hash', $orderHash)->first();

        $booking->status = 'scheduled';
        $booking->payment_status = 'paid';
        $booking->save();

        do_action('fluent_booking/payment/update_payment_status_paid', $booking);

        do_action('fluent_booking/pre_after_booking_' . $booking->status, $booking, $booking->calendar_event);

        // We are just renewing this as this may have been changed by the pre hook
        $booking = Booking::with(['calendar_event', 'calendar'])->find($booking->id);

        do_action('fluent_booking/after_booking_' . $booking->status, $booking, $booking->calendar_event);
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
            return $data['template'] = '<div class="fluent_booking_payment_methods">Please activate payment first!</div>';
        }

        $templates = [
            'template' => '',
        ];

        $hasActiveMethod = false;
        $radio = "<div class='payment-methods-radio fluent_booking_payment_methods'><div style='display: flex; gap: 20px;'>Pay with:";
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

