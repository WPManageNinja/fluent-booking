<?php

namespace FluentBooking\App\Services\Integrations\Calendars\NextCloud;

use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class NextCloudHelper
{
    public static function getApiConfig()
    {
        $settings = get_option('_fcal_next_cloud_calendar_client_details');

        if (!$settings || Arr::get($settings, 'is_enabled') != 'yes') {
            return [
                'is_enabled' => 'no',
                'base_url'   => ''
            ];
        }

        return [
            'is_enabled' => 'yes',
            'base_url'   => Arr::get($settings, 'base_url')
        ];
    }

    public static function updateConfig($settings)
    {
        if (Arr::get($settings, 'is_enabled') == 'no') {
            $settings = [
                'is_enabled' => 'no',
                'base_url'   => ''
            ];
        } else {
            $settings = [
                'is_enabled' => 'yes',
                'base_url'   => Arr::get($settings, 'base_url')
            ];
        }

        update_option('_fcal_next_cloud_calendar_client_details', $settings, 'no');
        return $settings;
    }

    public static function getClientByMeta(Meta $meta)
    {
        $config = self::getApiConfig();

        if (empty($config['base_url']) || $config['is_enabled'] != 'yes') {
            return false;
        }

        $settings = $meta->value;
        $userName = Arr::get($settings, 'remote_email');
        $passWord = Helper::decryptKey(Arr::get($settings, 'remote_pass'));

        if (!$userName || !$passWord) {
            return new \WP_Error('invalid_credentials', __('Invalid credentials', 'fluent-booking-pro'));
        }

        return new NextcloudClient($config['base_url'], $userName, $passWord);
    }
}
