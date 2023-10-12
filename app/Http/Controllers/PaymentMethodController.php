<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Http\Controllers\Controller;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
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
            ], 423);
        }

        return [
            'gateways' => $gateways
        ];
    }

    public function store(Request $request)
    {

        $data = $request->settings;
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
            ], 423);
        }
    }

    public function currencies(Request $request, GlobalPaymentHandler $globalHandler)
    {
        try {
            return $globalHandler->currencies();
        } catch (\Exception $error) {
            return $this->sendError([
                'message' => $error->getMessage()
            ], 423);
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

    public function getCalendarSettings($id, $event_id)
    {
        $calendarSlot = CalendarSlot::findOrFail($event_id);
        return $this->sendSuccess([
            'data' => $calendarSlot->getMeta('payment_settings')
        ]);
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

        $data['currency_sign'] = CurrenciesHelper::getCurrencySign(Arr::get($data, 'currency'));

        $res = $event->updateMeta('payment_settings', $data);

        return $this->sendSuccess([
                'data' => $res->toArray(),
                'message' => __('Settings updated successfully', 'fluent-booking')
            ]
        );

    }
}
