<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Http\Controllers\Controller;
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
}
