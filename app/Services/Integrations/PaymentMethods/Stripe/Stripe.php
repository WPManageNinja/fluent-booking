<?php

namespace FluentBooking\App\Services\Integrations\PaymentMethods\Stripe;

use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\PaymentMethods\BasePaymentMethod;
use FluentBooking\App\Services\Integrations\PaymentMethods\CurrenciesHelper;
use FluentBooking\App\Services\Integrations\PaymentMethods\Stripe\API\API;
use FluentBooking\App\Services\Integrations\PaymentMethods\Stripe\API\ApiRequest;
use FluentBooking\App\Services\OrderHelper;
use FluentBooking\Framework\Support\Arr;

class Stripe extends BasePaymentMethod
{
    /**
     * title, slug, brandColor
     */
    public function __construct()
    {
        parent::__construct(
            __('Stripe', 'fluent-booking'),
            'stripe',
            '#136196',
            $this->getLogo()
        );
        
        add_filter('fluent_booking/get_payment_connect_info_' . $this->slug, [$this, 'getConnectInfo']);
        add_filter('fluent_booking/get_payment_settings_disconnect_' . $this->slug, [$this, 'disconnect']);
        add_action('fluent-booking/before_render_payment_method_' . $this->slug, [$this, 'loadCheckoutJs'], 10, 1);

        add_action('wp_ajax_nopriv_fluent_cal_confirm_stripe_payment', [$this, 'confirmStripePayment']);
        add_action('wp_ajax_fluent_cal_confirm_stripe_payment', [$this, 'confirmStripePayment']);
        add_filter('fluent_booking/payment/payment_settings_before_update_stripe', [$this, 'beforeUpdateSettings'], 10, 1);

    }
    
    public function disconnect($data)
    {
        return ConnectConfig::disconnect($data);
    }

    public function isEnabled(): bool
    {
        return $this->getActiveStatus();
    }
    /**
     * Connect configuration should return
     */
    public function getConnectInfo()
    {
        wp_send_json_success(ConnectConfig::getConnectConfig(), 200);
    }

    /**
     * @return string path of the svg image which will be used as checkout logo
     */
    public function getLogo()
    {
        return FLUENT_BOOKING_URL . "assets/images/payment-methods/stripe.svg";
    }

    /**
     * @return string checkout methods short description
     * which will be shown to the checkout page and method settings page
     */
    public function getDescription()
    {
        return "Stripe's payments platform lets you accept 
            credit cards, debit cards, and popular payment 
            methods around the world—all with a single integration";
    }

    public function beforeUpdateSettings($data)
    {
        //encrypt secret keys by Helper::encrypt
//        if (isset($data['test_secret_key'])) {
//            $data['test_secret_key'] = Helper::encryptKey($data['test_secret_key']);
//        }
//        if (isset($data['live_secret_key'])) {
//            $data['live_secret_key'] = Helper::encryptKey($data['live_secret_key']);
//        }
        return $data;
    }

    public function getSettings()
    {
        return (new StripeSettings())->get();
    }

    public function makePayment($orderItem, $calendarSlot)
    {
        $hash = $orderItem->hash;
        $stripeSettings = new StripeSettings($this->slug);
        $apiKey = $stripeSettings->getApiKey();
        $publicKey = $stripeSettings->getPublicKey();
        $stripeSetting = $this->getSettings();
        $paymentInfo = $calendarSlot->getMeta('payment_settings');
        $currency = CurrenciesHelper::getGlobalCurrency();

        if (empty($paymentInfo)) {
            return;
        }

        $items = Arr::get($paymentInfo, 'items');
        $paymentTotal = $this->getPayableAmount($items, $currency);

        $paymentArgs = array(
            'client_reference_id' => $hash,
            'items' => $items,
            'amount' => (int) round($paymentTotal),
            'currency' => strtolower($currency),
            'description' => "Payment for Order",
            'customer_email' => $orderItem->email,
            'success_url' => $this->getSuccessUrl($orderItem),
        );

        //Subscription only available for hosted, will implement onsite later
        if ($stripeSetting['checkout_mode'] === 'onsite') {
            $paymentArgs['public_key'] = $publicKey;
            $this->handleOnsitePayment($orderItem, $paymentArgs, $apiKey);
        } else {
            $this->handleHostedPayment($orderItem, $paymentArgs, $apiKey);
        }
    }

    public function confirmStripePayment()
    {
        if (!isset($_REQUEST['intentId'])) {
            return;
        }

        $intentId = $_REQUEST['intentId'];
        $path = 'payment_intents/' . $intentId;

        $api = new API();
        $response = $api->makeRequest($path, [], (new StripeSettings())->getApiKey());

        if (!$response || is_wp_error($response)) {
            return;
        }

        $orderHash = Arr::get($response, 'metadata.ref_id');
        $amount = intval(Arr::get($response, 'amount_received'));

        //verify order
        $order =  (new OrderHelper())->getOrderByHash($orderHash);
        if (intval($order->total_amount) !== $amount) {
            return;
        }

        $status = Arr::get($response, 'status') === 'succeeded' ? 'paid' : 'pending';

        $updateData = [
            'status' => sanitize_text_field($status),
            'vendor_charge_id' => sanitize_text_field($intentId),
            'payment_mode' => Arr::get($response, 'livemode') ? 'live' : 'test'
        ];

        $order =  (new OrderHelper())->getOrderByHash($orderHash);
        $this->updateOrderData($order, $updateData);

    }

    public function verifyInvoiceAndUpdate($eventId)
    {
        $invoice = (new API())->getInvoice($eventId);
        $orderHash = self::getOrderHash($invoice);

        if (!$invoice || is_wp_error($invoice)) {
            error_log('invoice not found');
            return;
        }

        $updateData = [
            'status' => sanitize_text_field($invoice->data->object->status),
            'vendor_charge_id' => sanitize_text_field($invoice->data->object->payment_intent)
        ];

        //card_info update
        if ($cardInfo = $invoice->data->object->payment_method_details->card) {
            $updateData['card_brand'] = sanitize_text_field($cardInfo->brand);
            $updateData['card_last_4'] = sanitize_text_field($cardInfo->last4);
        }

        if ($invoice->data->object->status === 'succeeded') {
            $updateData['status'] = 'paid';
            $updateData['payment_method_type'] = $invoice->data->object->payment_method_details->type;
            $updateData['payment_mode'] = $invoice->data->object->livemode ? 'live' : 'test';
        }

        $order =  (new OrderHelper())->getOrderByHash($orderHash);
        $this->updateOrderData($order, $updateData);
    }


    /**
     * @param $orderItem
     * @param $paymentArgs
     * @param $apiKey
     * @return void
     * Handle Onsite Payment Api for stripe
     **/
    public function handleOnsitePayment($orderItem, $paymentArgs, $apiKey)
    {
        try {
            $sessionData = $this->intentData($orderItem, $paymentArgs);
            $invoiceResponse = (new API())->makeRequest('payment_intents', $sessionData, $apiKey, 'POST');

            $orderItem->payment_args = $paymentArgs;

            wp_send_json_success(
                [
                    'nextAction' => 'stripe',
                    'actionName' => 'custom',
                    'status' => 'success',
                    'message' => __('Order has been placed successfully', 'fluent-booking'),
                    'data' => $orderItem,
                    'intent' => $invoiceResponse,
                ],
                200
            );
        } catch (\Exception $e) {
            wp_send_json_error([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 422);
        }

    }

    public function getPayableAmount($items, $currency)
    {
        $total = 0;
        foreach ($items as $item) {
            if (!isset($item['value'])) {
                continue;
            }
            $total += intval($item['value']);
        }

        if (CurrenciesHelper::isZeroDecimal($currency)) {
            return $total;
        }
        return $total * 100;
    }

    public function intentData($orderItem, $args)
    {
        $currency = CurrenciesHelper::getGlobalCurrency();
        $sessionPayload = array(
            'amount' => intval($args['amount']),
            'currency' => $currency,
            'metadata' => [
                'ref_id'  => $args['client_reference_id'],
            ],
        );

        return $sessionPayload;
    }

    public function sessionData($args)
    {
        $items = $args['items'];
        $currency = CurrenciesHelper::getGlobalCurrency();

        $conversionFactor = 100;
        if (CurrenciesHelper::isZeroDecimal($currency)) {
            $conversionFactor = 1;
        }

        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = [
                'amount' => intval($item['value'] * $conversionFactor),
                'currency' => $currency,
                'name' => $item['title'],
                'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : 1,
            ];
        }

        $invoiceData = [
            'account_tax_ids' => [],
            'custom_fields' => [],
            'description' => 'Invoice for Order #' . $args['client_reference_id'],
            'footer' => '',
            'metadata' => [
                'ref_id'  => $args['client_reference_id'],
            ],
            'rendering_options' => [],
        ];

        $sessionPayload = array(
            'client_reference_id' => $args['client_reference_id'],
            'success_url' => $args['success_url'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'invoice_creation' => array(
                'enabled' => 'true',
                'invoice_data' => $invoiceData,
            ),
            'metadata' => [
                'ref_id'  => $args['client_reference_id'],
            ]
        );

        if (isset($args['payment_method_type'])) {
            $sessionPayload['payment_method_types'] = $args['payment_method_type'];
        }

        return $sessionPayload;
    }


    /*
    * @param $orderItem
     * @param $paymentArgs
     * @param $apiKey
     * @return void
     * Handle Onsite hosted checkout for stripe
    */
    public function handleHostedPayment($orderItem, $paymentArgs, $apiKey)
    {
        try {
            $sessionData = $this->sessionData($paymentArgs);

            $sessionData = apply_filters('fluent-booking/payment/stripe_checkout_session_args', $sessionData);

            $argsDefault = [
                'locale' => 'auto'
            ];
            $sessionData = array_merge($sessionData, $argsDefault);

            $invoiceResponse = (new API())->makeRequest('checkout/sessions', $sessionData, $apiKey, 'POST');

            is_wp_error($invoiceResponse) ? wp_send_json_error($invoiceResponse->get_error_message(), 422) : '';

            wp_send_json_success(
                [
                    'status' => 'success',
                    'message' => __('Order has been placed successfully', 'fluent-booking'),
                    'data' => $orderItem,
                    'redirect_to' => $invoiceResponse['url']
                ],
                200
            );
        } catch (\Exception $e) {
            wp_send_json_error([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 422);
        }

    }

    public function renderDescription()
    {
        echo '<p>' . esc_html__('Pay with Stripe', 'fluent-booking') . '</p>';
    }

    public function fields()
    {
        $currencies = CurrenciesHelper::getFormattedCurrencies();
        return array(
            'is_active' => array(
                'value' => 'no',
                'label' => __('Enable Stripe payment payment for booking payment', 'fluent-booking'),
                'type' => 'inline_checkbox'
            ),
            'payment_mode' => array(
                'value' => 'test',
                'label' => __('Payment Mode', 'fluent-booking'),
                'options' => array(
                    'test' => __('Test Mode', 'fluent-booking'),
                    'live' => __('Live Mode', 'fluent-booking')
                ),
                'type' => 'radio'
            ),
//            'checkout_mode' => array(
//                'value' => 'onsite',
//                'label' => __('Checkout Mode', 'fluent-booking'),
//                'options' => array(
//                    'onsite' => __('Onsite', 'fluent-booking'),
//                    'hosted' => __('Hosted', 'fluent-booking')
//                ),
//                'type' => 'radio'
//            ),
            'provider' => array(
                'value' => 'connect',
                'label' => __('Provider', 'fluent-booking'),
                'type' => 'provider'
             ),
            'currency' => array(
                'value' => 'USD',
                'label' => __('Currency', 'fluent-booking'),
                'options' => $currencies,
                'type' => 'select'
            ),
        );

    }

    public function webHookPaymentMethodName()
    {
        return $this->slug;
    }
    

    public function onPaymentEventTriggered()
    {
        $data =  (new API())->verifyIPN();

        if (!$data) {
            error_log('invalid data');
            return;
        }

        $this->verifyInvoiceAndUpdate($data->id);
    }


    public static function getOrderHash($event)
    {
        $eventType = $event->type;

        $metaDataEvents = [
            'checkout.session.completed',
            'charge.refunded',
            'charge.succeeded',
            'invoice.paid'
        ];

        if (in_array($eventType, $metaDataEvents)) {
            $data = $event->data->object;
            $metaData = (array)$data->metadata;
            return Arr::get($metaData, 'ref_id');
        }

        return false;
    }

    public function ShowModal($invoiceResponse)
    {
        $responseData = [
            'nextAction'       => 'stripe',
            'actionName'       => 'custom',
            'buttonState'      => 'hide',
            'invoice_response' => $invoiceResponse,
            'message_to_show'  => __('Payment Modal is opening, Please complete the payment', 'fluent-booking'),
        ];
        wp_send_json_success($responseData, 200);
    }

    public function loadCheckoutJs($my_data)
    {
        wp_enqueue_script('fluent-booking-checkout-sdk-' . $this->slug, 'https://js.stripe.com/v3/',null, false);
        wp_enqueue_script('fluent-booking-checkout-handler-' . $this->slug, FLUENT_BOOKING_URL . 'assets/public/js/stripe-checkout.js', ['fluent-booking-checkout-sdk-' . $this->slug], false);
    }

    public function render($method)
    {
        do_action('fluent-booking/before_render_payment_method_' . $this->slug, $method);
        return '
            <input checked value="' .esc_attr($this->slug) .'" name="'. esc_attr($this->slug) .'_payment_method' .'" type="radio"  id="'. esc_attr($this->slug) .'_payment_method">
            <label for="' . esc_attr($this->slug) . '_payment_method">
              Stripe
            </label>
        ';
    }
}
