<?php

namespace FluentBooking\App\Services\Integrations\PaymentMethods\Stripe;

use FluentBooking\App\Services\Integrations\PaymentMethods\BasePaymentMethod;
use FluentBooking\App\Services\Integrations\PaymentMethods\Stripe\API\API;
use FluentBooking\App\Services\Integrations\PaymentMethods\Stripe\API\ApiRequest;
use FluentBooking\Framework\Support\Arr;

use FluentCart\App\Services\OrderHelper;

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

    public function getSettings()
    {
        return (new StripeSettings())->get();
    }

    public function makePayment($orderItem)
    {
        $hash = $orderItem->hash;
        $stripeSettings = new StripeSettings($this->slug);
        $apiKey = $stripeSettings->getApiKey();
        $publicKey = $stripeSettings->getPublicKey();
        $stripeSetting = $this->getSettings();

        //to-do will add from settings
        $currency =  'USD';
        $paymentTotal = 2000;
//            $this->getPayableAmount($orderItem)

        $paymentArgs = array(
            'payment_method_type' => ['card'],
            'client_reference_id' => $hash,
            'items' => $orderItem->items,
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
            ], 423);
        }

    }

    public function intentData($orderItem, $args)
    {
        $items = $args['items'];

        $sessionPayload = array(
            'amount' => intval($args['amount']),
            'currency' => $args['currency'],
            'metadata' => [
                'ref_id'  => $args['client_reference_id'],
            ],
        );

        return $sessionPayload;
    }

    public function sessionData($args)
    {

        //to-do
        //now dummy data
        $items = [
            [
                'price' => 2000,
                'title' => 'test',
                'quantity' => 1
            ]
        ];

        $lineItems = [];

        foreach ($items as $item) {
            $lineItems[] = [
                'amount' => (int) ($item['price']),
                'currency' => $args['currency'],
                'name' => $item['title'],
                'quantity' => (int) $item['quantity'],
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
            //'cancel_url' => 'http://stripe.com',
            'payment_method_types' => $args['payment_method_type'],
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

            is_wp_error($invoiceResponse) ? wp_send_json_error($invoiceResponse->get_error_message(), 423) : '';

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
            ], 423);
        }

    }

    public function renderDescription()
    {
        echo '<p>' . esc_html__('Pay with Stripe', 'fluent-booking') . '</p>';
    }

    public function fields()
    {
        return array(
            'is_active' => array(
                'value' => 'no',
                'label' => __('Enable Stripe payment', 'fluent-booking'),
                'type' => 'enable'
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
            'checkout_mode_notice' => array(
                'value' => "Using onsite checkout mode you can accept payment without leaving your site.<br/> NB: Subscriptions payment may force to hosted checkout automatically! <br/>",
                'label' => '',
                'type' => 'html_attr'
            ),
            'checkout_mode' => array(
                'value' => 'onsite',
                'label' => __('Checkout Mode', 'fluent-booking'),
                'options' => array(
                    'onsite' => __('Onsite', 'fluent-booking'),
                    'hosted' => __('Hosted', 'fluent-booking')
                ),
                'type' => 'radio'
            ),
            'provider' => array(
                'value' => 'connect',
                'label' => __('Provider', 'fluent-booking'),
                'type' => 'provider'
            ),
            'setup_guide' => array(
                'value' => '<h3>Or Setup keys manually.</h3><hr/>',
                'label' => __('Or Setup keys manually', 'fluent-booking'),
                'type' => 'html_attr'
            ),
            'test_publishable_key' => array(
                'value' => '',
                'label' => __('Test Publishable Key', 'fluent-booking'),
                'type' => 'text'
            ),
            'test_secret_key' => array(
                'value' => '',
                'label' => __('Test Publishable Key', 'fluent-booking'),
                'type' => 'password'
            ),
            'live_publishable_key' => array(
                'value' => '',
                'label' => __('Live Publishable Key', 'fluent-booking'),
                'type' => 'text'
            ),
            'live_secret_key' => array(
                'value' => '',
                'label' => __('Live Secret Key', 'fluent-booking'),
                'type' => 'password'
            ),
            'webhook_desc' => array(
                'value' => "
                <hr/>
                <div class='mt-6'>
                <h3 style='color:green;'>Stripe Webhook (Setup Required *) </h3> 
                <p>If you use Stripe for recurring payments please set the notification URL in Stripe as bellow:<br/> 
                <p><b>Webhook URL: </b><br/><code> " . site_url() . '?fluent_booking_payment_listener=1&method=stripe' . "</code></p> <br/> 
                you must configure your Stripe webhooks. Visit your <a href='https://stripe.com/docs/webhooks' target='_blank' rel='noopener'>account dashboard</a> 
                to configure them.<br/> Please consider enabling webhook endpoints must: <code>charge.succeeded</code>, <code>charge.captured</code>, <code>invoice.paid</code></div></div>",
                'label' => __('Webhook URL', 'fluent-booking'),
                'type' => 'html_attr'
            ),
        );

    }

    public function webHookPaymentMethodName()
    {
        return $this->slug;
    }
    

    public function onPaymentEventTriggered()
    {
        $data = (new API())->verifyIPN();

        if (!$data) {
            error_log('invalid data');
            return;
        }

        $eventId = $data->id;
        $invoice = (new API())->getInvoice($eventId);

        $orderHash = $this->getOrderHash($invoice);

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
            $updateData['status'] = 'pending';
        }

        $this->updateOrderDataByHash($orderHash, $updateData);
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
        wp_enqueue_script('fluent-booking-checkout-handler-' . $this->slug, FLUENT_BOOKING_URL . 'assets/admin/js/payment-methods/stripe-checkout.js', ['fluent-booking-checkout-sdk-stripe'], false);
    }

    public function render($method)
    {
        echo '
            <img src="' . esc_url($this->getLogo()) . '"alt="' . esc_attr($this->title) . '"/>
            <span>Stripe</span>
        ';
    }

    public function maybeUpdatePayments($orderHash)
    {
        $updateData = [
            'status' => 'pending'
        ];
        $this->updateOrderDataByHash($orderHash, $updateData);
        return;
    }
}
