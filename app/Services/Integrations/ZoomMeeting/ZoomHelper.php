<?php

namespace FluentBooking\App\Services\Integrations\ZoomMeeting;

use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class ZoomHelper
{
    public static function getApiConfig()
    {
        if (defined('FLUENT_BOOKING_ZOOM_AUTH_CLIENT_ID') && defined('FLUENT_BOOKING_ZOOM_AUTH_CLIENT_SECRET')) {
            return [
                'client_id'        => FLUENT_BOOKING_ZOOM_AUTH_CLIENT_ID,
                'client_secret'    => FLUENT_BOOKING_ZOOM_AUTH_CLIENT_SECRET,
                'constant_defined' => true
            ];
        }

        $defaults = [
            'client_id'     => '',
            'client_secret' => ''
        ];


        $settings = get_option('_fcal_zoom_client_details', []);

        $settings = wp_parse_args($settings, $defaults);

        if (!empty($settings['client_secret'])) {
            $settings['client_secret'] = Helper::decryptKey($settings['client_secret']);
        }

        return $settings;

    }

    public static function updateApiConfig($settings)
    {
        if (defined('FLUENT_BOOKING_ZOOM_AUTH_CLIENT_ID') && defined('FLUENT_BOOKING_ZOOM_AUTH_CLIENT_SECRET')) {
            return [
                'client_id'        => FLUENT_BOOKING_ZOOM_AUTH_CLIENT_ID,
                'client_secret'    => FLUENT_BOOKING_ZOOM_AUTH_CLIENT_SECRET,
                'constant_defined' => true
            ];
        }

        $settings = Arr::only($settings, ['client_id', 'client_secret']);

        if (!empty($settings['client_secret'])) {

            if ($settings['client_secret'] == '********************') {
                $oldSettings = self::getApiConfig();
                $settings['client_secret'] = $oldSettings['client_secret'];
            }

            $settings['client_secret'] = Helper::encryptKey($settings['client_secret']);
        }

        update_option('_fcal_zoom_client_details', $settings, 'no');

        return $settings;
    }

    public static function updateAccessConfig($config, $calendar)
    {
        $config = Arr::only($config, ['access_token', 'refresh_token', 'expires_in', 'account_email']);

        $config['access_token'] = Helper::encryptKey($config['access_token']);
        $config['refresh_token'] = Helper::encryptKey($config['refresh_token']);
        $calendar->updateMeta('_zoom_integration_config', $config);
        return $calendar;
    }

    public static function getAccessConfig($calendar)
    {
        if (is_numeric($calendar)) {
            $calendar = Calendar::where('id', $calendar)->first();
            if (!$calendar) {
                return [];
            }
        }

        $config = $calendar->getMeta('_zoom_integration_config', []);

        if ($config) {
            $config['access_token'] = Helper::decryptKey($config['access_token']);
            $config['refresh_token'] = Helper::decryptKey($config['refresh_token']);
        }

        return $config;
    }

    public static function getApiClient($accessToken = null)
    {
        $config = self::getApiConfig();
        $client = new Client($config['client_id'], $config['client_secret'], self::getAppRedirectUrl());

        if ($accessToken) {
            $client = $client->setAccessToken($accessToken);
        }

        return $client;
    }

    public static function getCalendarApiClient($calendar)
    {
        $config = self::getAccessConfig($calendar);
        if (!$config || empty($config['access_token'])) {
            return new \WP_Error('wp_error', 'No access token found');
        }

        $client = self::getApiClient();

        if ($config['expires_in'] - 10 < time()) {
            // New to renew this token
            $config = $client->reGenerateToken($config['refresh_token']);
            if (is_wp_error($config)) {
                return $config;
            }

            $config['expires_in'] += time();
            self::updateAccessConfig($config, $calendar);
        }

        return $client->setAccessToken($config['access_token']);
    }

    public static function isConfigured()
    {
        $config = self::getApiConfig();
        return !empty($config['client_id']) && !empty($config['client_secret']);
    }

    public static function isCalendarConfigured($calendar)
    {
        if (!self::isConfigured()) {
            return false;
        }

        $config = self::getAccessConfig($calendar);

        if (!$config) {
            return false;
        }

        return !empty($config['access_token']) && !empty($config['refresh_token']);
    }

    public static function getAppRedirectUrl()
    {
        if (defined('FLUENT_BOOKING_ZOOM_AUTH_REDIRECT_URL')) {
            return FLUENT_BOOKING_ZOOM_AUTH_REDIRECT_URL;
        }
        return admin_url('admin-ajax.php?action=fluent_booking_zoom_auth');
    }

    public static function getOauthRedirectUrl($state)
    {
        $config = self::getApiConfig();
        return add_query_arg([
            'response_type' => 'code',
            'client_id'     => $config['client_id'],
            'redirect_uri'  => self::getAppRedirectUrl(),
            'state'         => $state
        ], 'https://zoom.us/oauth/authorize');
    }
}
