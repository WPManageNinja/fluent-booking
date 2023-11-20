<?php

namespace FluentBooking\App\Services\Integrations\Calendars\AppleCalendar;

use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class AppleHelper
{
    public static function getApiConfig()
    {
        $settings = get_option('_fcal_apple_calendar_client_details');

        if (!$settings || Arr::get($settings, 'is_enabled') != 'yes') {
            return [
                'is_enabled' => 'no'
            ];
        }

        return [
            'is_enabled' => 'yes'
        ];
    }

    public static function updateConfig($settings)
    {
        if (Arr::get($settings, 'is_enabled') == 'no') {
            $settings = [
                'is_enabled' => 'no'
            ];
        } else {
            $settings = [
                'is_enabled' => 'yes'
            ];
        }

        update_option('_fcal_apple_calendar_client_details', $settings, 'no');
        return $settings;
    }
}
