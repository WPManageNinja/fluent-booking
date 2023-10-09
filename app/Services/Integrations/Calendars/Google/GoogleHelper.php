<?php

namespace FluentBooking\App\Services\Integrations\Calendars\Google;

use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
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

        $settings = wp_parse_args($settings, $defaults);

        if(!empty($settings['client_secret'])) {
            $settings['client_secret'] = Helper::decryptKey($settings['client_secret']);
        }

        return $settings;

    }

    public static function updateApiConfig($settings)
    {
        $settings = Arr::only($settings, ['client_id', 'client_secret']);

        if(!empty($settings['client_secret'])) {
            $settings['client_secret'] = Helper::encryptKey($settings['client_secret']);
        }

        update_option('_fcal_google_calendar_client_details', $settings, 'no');

        return $settings;
    }

    public static function getApiClient($accessToken = null)
    {
        $config = self::getApiConfig();
        $client = new Client($config['client_id'], $config['client_secret']);

        if ($accessToken) {
            $client = $client->setAccessToken($accessToken);
        }

        return $client;
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

    public static function getConflictCheckCalendars($userId)
    {
        $metaItems = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $userId)
            ->get();

        if ($metaItems->isEmpty()) {
            return [];
        }

        $calendars = [];

        foreach ($metaItems as $item) {

            $settings = $item->value;

            $checkIds = Arr::get($settings, 'conflict_check_ids', []);

            if (empty($checkIds)) {
                continue;
            }

            $itemValidCalendars = [];

            $allCalendars = Arr::get($settings, 'calendar_lists', []);

            foreach ($allCalendars as $calendar) {
                if (in_array($calendar['id'], $checkIds)) {
                    $itemValidCalendars[] = $calendar['id'];
                }
            }

            if ($itemValidCalendars) {
                $calendars[] = [
                    'item'      => $item,
                    'check_ids' => $itemValidCalendars
                ];
            }
        }

        return $calendars;
    }
}
