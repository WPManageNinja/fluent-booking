<?php

namespace FluentBooking\App\Services\Integrations\PaymentMethods\Stripe;

use FluentCart\Api\CurrencySettings;
use FluentCart\App\Modules\PaymentMethods\Stripe\API\ApiRequest;
use FluentCart\App\Services\CurrenciesHelper;
use FluentCart\Framework\Support\Arr;

class Plan
{
    public static function getOrCreatePlan($order, $subscription)
    {
        if (CurrenciesHelper::isZeroDecimal($order->currency)) {
            $subscription->recurring_amount = intval(Arr::get($subscription, 'recurring_amount' / 100));
        }

        // Generate The subscription ID Here
        $vendorSubsId = self::getGeneratedSubscriptionId($subscription, $order->currency);
        $vendorSubsId = apply_filters('fluent_booking/payment/subscription_plan_id_' . Arr::get($subscription, 'id'), $vendorSubsId);

        $stripePlan = self::retirive($vendorSubsId);

        if ($stripePlan && !is_wp_error($stripePlan)) {
            return $stripePlan;
        }

        // We don't have this plan yet. Now we have to create the plan from subscription
        $billingInterval = Arr::get($subscription, 'billing_interval');
        if ($billingInterval === 'daily') {
            $billingInterval = 'day';
        } elseif ($billingInterval === 'monthly') {
            $billingInterval = 'month';
        } elseif ($billingInterval === 'yearly') {
            $billingInterval = 'year';
        } elseif( $billingInterval === 'weekly') {
            $billingInterval = 'week';
        }

        $plan = array(
            'id' => $vendorSubsId,
            'currency' => $order->currency,
            'interval' => $billingInterval,
            'amount' => Arr::get($subscription, 'recurring_amount'),
            'trial_period_days' => Arr::get($subscription, 'trial_days'),
            'product' => array(
                'id' => Arr::get($subscription, 'id'),
                'name' => Arr::get($subscription, 'item_name'),
                'type' => 'service'
            ),
            'metadata' => array(
                'product_id' => Arr::get($subscription, 'product_id'),
                'element_id' => $subscription->element_id,
                'wp_plugin' => 'fluent-booking'
            )
        );

        return self::create($plan);
    }

    public static function getGeneratedSubscriptionId($subscription, $currency = 'USD')
    {
        $productId = Arr::get($subscription, 'product_id');
        $subscriptionId = Arr::get($subscription, 'id');
        $recurringAmount = Arr::get($subscription, 'recurring_amount');
        $billingInterval = Arr::get($subscription, 'billing_interval');
        $trialDays = Arr::get($subscription, 'trial_days', 0);

        $vendorSubsId = 'fluent_booking_' . $productId . '_' . $subscriptionId . '_recurring_' . $recurringAmount . '_' . $billingInterval . '_' . $trialDays . '_' . $currency;;
        return apply_filters('fluent_booking/payment/stripe_plan_name_generated', $vendorSubsId, $subscription, $currency);
    }

    public static function retirive($planId)
    {
        try {
            $stripe = new StripeSettings();
            ApiRequest::set_secret_key($stripe->getApiKey());
            $response = ApiRequest::request([], 'plans/' . $planId, 'GET');
            if (!empty($response->error)) {
                $errotType = 'general';
                if (!empty($response->error->type)) {
                    $errotType = $response->error->type;
                }
                $errorCode = '';
                if (!empty($response->error->code)) {
                    $errorCode = $response->error->code . ' : ';
                }
                return self::errorHandler($errotType, $errorCode . $response->error->message);
            }
            if (false !== $response) {
                return $response;
            }
        } catch (\Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return self::errorHandler('non_stripe', esc_html__('General Error', 'fluent-booking') . ': ' . $e->getMessage());
        }
        return false;
    }

    public static function create($plan)
    {
        $stripe = new StripeSettings();
        ApiRequest::set_secret_key($stripe->getApiKey());
        $response = ApiRequest::request($plan, 'plans', 'POST');
        if (!empty($response->error)) {
            $errotType = 'general';
            if (!empty($response->error->type)) {
                $errotType = $response->error->type;
            }
            $errorCode = '';
            if (!empty($response->error->code)) {
                $errorCode = $response->error->code . ' : ';
            }
            return self::errorHandler($errotType, $errorCode . $response->error->message);
        }
        if (false !== $response) {
            return $response;
        }
        return false;
    }

    private static function errorHandler($code, $message, $data = array()): \WP_Error
    {
        return new \WP_Error($code, $message, $data);
    }

}