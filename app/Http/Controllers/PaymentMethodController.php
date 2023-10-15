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
                        'message' => 'Please connect your Stripe account first.'
                    ]);
                }

                if (!Arr::get($settings, 'currency')) {
                    return $this->sendError([
                        'message' => 'Please connect your Stripe account first.'
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

        $settings = $calendarSlot->getMeta('payment_settings', []);

        if (!$settings) {
            $settings = [
                'enabled' => 'no',
                'items'   => [
                    [
                        'title' => __('Booking Fee', 'fluent-booking'),
                        'value' => 100,
                    ]
                ]
            ];
        }

        $data = [
            'settings' => $settings
        ];

        if (Helper::isPaymentEnabled()) {
            $data['global_enabled'] = true;
        } else {
            $data['global_enabled'] = false;
            $data['global_config_link'] = Helper::getAppBaseUrl('settings/configure-integrations/payment/stripe');
        }

        return $data;
    }

    public function updateSettings($id, $event_id)
    {
        $data = $this->request->settings;
        $event = CalendarSlot::findOrFail($event_id);

        if (!$event) {
            return $this->sendError([
                'message' => 'Calendar not found'
            ], 404);
        }

        $type = Arr::get($data, 'enabled') === 'yes' ? 'paid' : 'free';

        $event->update([
            'type' => $type
        ]);

        $data['currency_sign'] = CurrenciesHelper::getGlobalCurrencySign();

        $res = $event->updateMeta('payment_settings', $data);

        return $this->sendSuccess([
                'data'    => $res->toArray(),
                'message' => __('Settings updated successfully', 'fluent-booking')
            ]
        );

    }
}
