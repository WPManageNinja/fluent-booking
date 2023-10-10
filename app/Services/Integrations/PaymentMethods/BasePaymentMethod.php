<?php

namespace FluentBooking\App\Services\Integrations\PaymentMethods;

use FluentCart\Api\Orders;
use FluentCart\App\Models\Order;
use FluentCart\App\Models\OrderTransaction;
use FluentCart\Api\Helper;
use FluentCart\App\Services\OrderHelper;
use FluentCart\App\Services\Payments\PaymentHelper;
use FluentCart\App\Services\StatusHelper;

use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Validator\Validator;

abstract class BasePaymentMethod implements BasePaymentInterface
{
    public $slug;

    public $title;

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

    abstract public function makePayment($orderItem);

    abstract public function maybeUpdatePayments($orderHash);

    public function resolveOrderHash($orderItem)
    {
        if ($orderItem != null && isset($orderItem->order) && $orderItem->order instanceof Order) {
            return $orderItem->order->uuid;
        }
        return "";
    }

    public function __construct($title, $slug, $brandColor)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->brandColor = $brandColor;
        $this->methodHandler = 'fluent_booking_payment_settings_' . $slug;
    }

    public function init()
    {
        add_filter('fluent_booking/payment/get_global_payment_settings_' . $this->slug, [$this, 'globalFields']);
        add_filter('fluent_booking/payment/get_global_payment_methods', [$this, 'register']);
        add_action('fluent_booking/payment/payment_settings_update_' . $this->slug, [$this, 'update'], 10, 1);
        add_filter('fluent_booking/payment/payment_settings_before_update_' . $this->slug, [$this, 'beforeUpdate']);
        add_filter('fluent_booking/payment/payment_method_settings_routes', [$this, 'setRoutes']);
        add_action('fluent_booking/payment/pay_order_with_' . $this->slug, [$this, 'makePayment']);
        add_action('fluent_booking/payment/ipn_endpoint_' . $this->webHookPaymentMethodName(), [$this, 'onPaymentEventTriggered']);
        add_action('fluent_booking/payment/prepare_payment_method_' . $this->slug, [$this, 'prepare'], 10, 1);
        add_action('fluent_booking/payment/pre_render_page_process_' . $this->slug, [$this, 'maybeUpdatePayments'], 10, 1);
    }
    public function handleRedirectData() 
    {
        return '';
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
            "title" => $this->title,
            "route" => $this->slug,
            "description" => $this->getDescription(),
            "logo" => $this->getLogo(),
            "status" => $this->isEnabled(),
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

        update_option($this->methodHandler, $settings);

        return $this->getSettings();
    }

    public function globalFields()
    {
        return [
            'fields' => $this->fields(),
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

    public function beforeUpdate($data)
    {
        return Helper::sanitize($data, $this->fields());
    }

    protected function getSuccessUrl($orderItem, $args = null)
    {
        $paymentHelper = new PaymentHelper($this->slug);
        return $paymentHelper->successUrl($orderItem->order->uuid, $args);
    }

    protected function getListenerUrl($args = null)
    {
        return (new PaymentHelper($this->slug))->listenerUrl($args);
    }

    public function getOrderByHash($orderHash)
    {
        return (new Orders())->getByHash($orderHash);
    }

    public function updateOrderDataByHash($orderHash, $transactionData = [])
    {
        $order = $this->getOrderByHash($orderHash);
        if ($order == null) {
            return;
        }

        $status = Arr::get($transactionData, 'status', 'paid');

        (new StatusHelper())->setOrder($order)
            ->changeOrderStatus($status)
            ->updateTransactionData($transactionData);
    }

    public function getPayableAmount($orderData)
    {
        if ($orderData instanceof OrderHelper) {
            return $orderData->order->total_amount;
        } else if ($orderData instanceof Order) {
            return $orderData->total_amount;
        }
        return 0;
    }

    public function maybeUpdatePayment()
    {
        return false;
    }

    public function render($method)
    {
        echo '
            <img src="' . esc_url($this->getLogo()) . '"alt="' . esc_attr($this->title) . '"/>
            <span>Cash on delivery</span>
        ';
    }

    public function prepare($method)
    {
        do_action('fluent-booking/before_render_payment_method_' . $this->slug, $method);

        $this->render($method);

        do_action('fluent-booking/after_render_payment_method_' . $this->slug, $method);
    }

    protected function validate($data, array $rules = [])
    {
        $validator = (new Validator())->make($data, $rules);
        if ($validator->validate()->fails()) {
            wp_send_json_error($validator->errors(), 423);
        }
    }
}

