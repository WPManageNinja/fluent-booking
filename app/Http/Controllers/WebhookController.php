<?php
namespace FluentBooking\App\Http\Controllers;

use FluentBooking\Framework\Request\Request;
use FluentBooking\App\Models\Webhook;

class WebhookController extends Controller
{

    public function create(Request $request, Webhook $webhook)
    {
        error_log(print_r($request->all(), 1));
        die();
        $webhook = $webhook->store(
            $this->validate(
                $request->all(),
                [
                    'name'        => 'required',
                    'calendar_id' => 'required'
                ]
            )
        );
        return [
            'id'       => $webhook->id,
            'webhook'  => $webhook->value,
            'webhooks' => $webhook->latest()->get(),
            'message'  => __('Successfully created the WebHook', 'fluent-booking')
        ];
    }
}