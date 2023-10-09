<?php

namespace FluentBooking\App\Services\Integrations\Calendars\Google;

use FluentBooking\Framework\Support\Arr;

class GoogleHelper
{
    public static function getApiConfig()
    {
        $defaults = [
            'client_id'     => '',
            'client_secret' => ''
        ];

        $settings = get_option('_fcal_google_calendar_client_details', []);

        return wp_parse_args($settings, $defaults);
    }

    public static function getApiClient()
    {
        $config = self::getApiConfig();
        return new Client($config['client_id'], $config['client_secret']);
    }

    public static function isConfigured()
    {
        $config = self::getApiConfig();
        return !empty($config['client_id']) && !empty($config['client_secret']);
    }


    public static function getEmailByIdToken($token)
    {
        $tokenParts = explode(".", $token);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtPayload = json_decode($tokenPayload, true);

        if (empty($jwtPayload['email'])) {
            return new \WP_Error('payload_error', __('Sorry! There has an error when fetching data for google authentication. Please try again', 'fluent-booking'));
        }

        return Arr::get($jwtPayload, 'email');
    }
}
