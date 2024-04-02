<?php

namespace FluentBooking\App\Services\LandingPage;

use FluentBooking\App\Models\Calendar;
use FluentBooking\Framework\Support\Arr;

class LandingPageHelper
{
    public static function getSettings(Calendar $calendar, $scope = 'admin')
    {
        $sharingSettings = $calendar->getMeta('sharing_settings', []);

        $defaults = [
            'enabled'       => 'no',
            'enabled_slots' => [],
            'show_type'     => 'all'
        ];

        $settings = wp_parse_args($sharingSettings, $defaults);
        
        return apply_filters('fluent_booking/calendar/sharing_settings', $settings, $calendar, $scope);
    }

    public static function updateSettings(Calendar $calendar, $settings)
    {
        $defaults = [
            'enabled'       => 'no',
            'enabled_slots' => [],
            'show_type'     => 'all'
        ];

        $sharingSettings = Arr::only($settings, array_keys($defaults));
        $sharingSettings = wp_parse_args($sharingSettings, $defaults);

        if ($sharingSettings['show_type'] == 'all' || $sharingSettings['enabled'] == 'no') {
            $sharingSettings['enabled_slots'] = [];
        } else {
            $sharingSettings['enabled_slots'] = array_map('intval', $sharingSettings['enabled_slots']);
        }

        $sharingSettings['enabled'] = $sharingSettings['enabled'] == 'yes' ? 'yes' : 'no';

        $settings['show_type'] = sanitize_text_field($sharingSettings['show_type']);

        $calendar->updateMeta('sharing_settings', $sharingSettings);

        return $settings;
    }

    public static function getLandingBaseUrl()
    {
        if (defined('FLUENT_BOOKING_LANDING_SLUG')) {
            return site_url(FLUENT_BOOKING_LANDING_SLUG . '/');
        }

        return site_url('/?fluent-booking=calendar');
    }
}
