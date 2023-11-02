<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Request\Request;
use FluentBooking\Framework\Support\Arr;

class TwilioController extends Controller
{
    public function getSlotSmsNotifications(Request $request, $calendarId, $slotId)
    {
        $calendarEvent = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        $data = [
            'notifications' => $calendarEvent->getSmsNotifications(true)
        ];

        if (in_array('smart_codes', $request->get('with', []))) {
            $data['smart_codes'] = [
                'texts' => Helper::getEditorShortCodes($calendarEvent),
                'html'  => Helper::getEditorShortCodes($calendarEvent, true)
            ];
        }

        return $data;
    }

    public function saveSlotSmsNotifications(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        $notifications = $request->get('notifications', []);

        $formattedNotifications = [];

        foreach ($notifications as $key => $value) {
            $formattedNotifications[$key] = [
                'title'   => sanitize_text_field(Arr::get($value, 'title')),
                'enabled' => Arr::isTrue($value, 'enabled'),
                'sms'     => $this->sanitize_notification_data(Arr::get($value, 'sms')),
                'is_host' => Arr::isTrue($value, 'is_host')
            ];
        }

        $slot->setSmsNotifications($formattedNotifications);

        return [
            'message' => __('Notifications has been saved', 'fluent-booking-pro')
        ];
    }

    private function sanitize_notification_data($settings)
    {
        $sanitizerMap = [
            'value'                 => 'intval',
            'unit'                  => 'sanitize_text_field',
            'body'                  => 'fcal_sanitize_html',
            'number'                => 'sanitize_text_field',
            'reciever'              => 'sanitize_text_field',
            'send_to'               => 'sanitize_text_field',
        ];

        return Helper::fcal_backend_sanitizer($settings, $sanitizerMap);
    }
}