<?php

namespace FluentBooking\App\Services\Integrations\Calendars\Google;

use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;

class Bootstrap
{
    public function register()
    {
        add_filter('fluent_booking/remote_calendar_providers', function ($calendars, $userId = null) {
            $calendars['google'] = [
                'key'                  => 'google',
                'title'                => __('Google Calendar', 'fluent-booking'),
                'subtitle'             => __('Configure Google Calendar/Meet to sync your events', 'fluent_booking'),
                'btn_text'             => __('Connect with Google Calendar', 'fluent-booking'),
                'auth_url'             => $this->getAuthUrl($userId),
                'is_global_configured' => GoogleHelper::isConfigured(),
                'global_config_url'    => admin_url('admin.php?page=fluent-booking#/settings/configure-integrations/google_calendar'),
            ];

            return $calendars;
        }, 10, 2);

        add_filter('fluent_booking/remote_calendar_connection_feeds', [$this, 'pushGoogleFeeds'], 10, 2);

        add_action('wp_ajax_fluent_booking_g_auth', [$this, 'handleAuthCallback']);

    }

    public function pushGoogleFeeds($feeds, $userId)
    {
        $items = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $userId)
            ->get();

        $formattedFeeds = [];
        foreach ($items as $item) {
            $formattedFeeds[] = [
                'driver'           => 'google',
                'db_id'            => $item->id,
                'identifier'       => $item->key,
                'remote_calendars' => $this->getRemoteCalendarsList($item)
            ];
        }

        return $formattedFeeds;
    }

    private function getRemoteCalendarsList($item)
    {
        $settings = $item->value;

        if (!empty($settings['calendar_lists'])) {
            return $settings['calendar_lists'];
        }

        if ($settings['expires_in'] - 3 <= time()) {
            $newTokens = (GoogleHelper::getApiClient())->reGenerateToken($settings['refresh_token']);
            if (is_wp_error($newTokens)) {
                return [];
            }

            $settings['access_token'] = $newTokens['access_token'];
            $settings['expires_in'] = $newTokens['expires_in'];
        }

        $lists = (GoogleHelper::getApiClient())->getCalendarLists($settings['access_token']);

        if (is_wp_error($lists)) {
            return [];
        }

        $settings['calendar_lists'] = $lists;

        $item->value = $settings;
        $item->save();

        return $settings['calendar_lists'];
    }

    protected function getAuthUrl($userId)
    {
        if (!$userId) {
            return '';
        }

        return (GoogleHelper::getApiClient())->getAuthUrl($userId);
    }

    public function handleAuthCallback()
    {
        if (!isset($_GET['code'], $_GET['scope'])) {
            return;
        }

        $code = sanitize_text_field($_GET['code']);
        $scope = sanitize_text_field($_GET['scope']);

        $userId = sanitize_text_field($_GET['state']);

        $client = GoogleHelper::getApiClient();


        $response = $client->generateAuthCode($code);

        if (is_wp_error($response)) {
            dd($response);
        }

        $calendarEmail = GoogleHelper::getEmailByIdToken($response['id_token']);

        if (is_wp_error($calendarEmail)) {
            dd($calendarEmail);
        }

        unset($response['id_token']);
        $response['remote_email'] = $calendarEmail;

        $response['expires_in'] += time();

        $this->addFeedIntegration($userId, $response);

        $calendar = Calendar::where('user_id', $userId)->first();

        wp_redirect(admin_url('admin.php?page=fluent-booking#/calendars/' . $calendar->id . '/settings/google'));

        exit;
    }

    private function addFeedIntegration($userId, $tokenData)
    {
        $exist = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $userId)
            ->where('key', $tokenData['remote_email'])
            ->first();

        if ($exist) {
            $exist->value = $tokenData;
            $exist->save();
            return $exist;
        }

        return Meta::create([
            'object_type' => '_google_user_token',
            'object_id'   => $userId,
            'key'         => $tokenData['remote_email'],
            'value'       => $tokenData
        ]);
    }

}
