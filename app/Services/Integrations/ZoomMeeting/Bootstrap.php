<?php

namespace FluentBooking\App\Services\Integrations\ZoomMeeting;


use FluentBooking\App\App;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarHelper;
use FluentBooking\App\Services\Integrations\ZoomMeeting;
use FluentBooking\Framework\Support\Arr;

class Bootstrap
{
    public function register()
    {
        /*
         * Global Settings
         */
        add_filter('fluent_booking/settings_menu_items', function ($menuItems) {
            $app = App::getInstance();
            $menuItems['zoom_meeting'] = [
                'title'          => __('Zoom', 'fluent-booking'),
                'icon_url'       => $app['url.assets'] . 'images/zoom.svg',
                'component_type' => 'GlobalSettingsComponent',
                'route'          => [
                    'name'   => 'configure-integrations',
                    'params' => [
                        'settings_key' => 'zoom_meeting'
                    ]
                ]
            ];
            return $menuItems;
        }, 11, 1);
        add_filter('fluent_booking/get_client_settings_zoom_meeting', function ($settings) {
            $config = ZoomHelper::getApiConfig();
            $config['redirect_url'] = ZoomHelper::getAppRedirectUrl();

            if (!empty($config['constant_defined'])) {
                $config['client_secret'] = '**********';
                $config['client_id'] = '**********';
            } else if (!empty($config['client_secret'])) {
                $config['client_secret'] = '********************';
            }

            return $config;
        });
        add_filter('fluent_booking/get_client_field_settings_zoom_meeting', function ($items) {

            $app = App::getInstance();

            $fields = [
                'client_id'     => [
                    'type'        => 'text',
                    'label'       => __('App Client ID', 'fluent_booking'),
                    'placeholder' => __('Enter Your App Client ID', 'fluent_booking'),
                ],
                'client_secret' => [
                    'type'        => 'text',
                    'label'       => __('App Secret Key', 'fluent_booking'),
                    'placeholder' => __('Enter Your App Secret Key', 'fluent_booking'),
                ],
                'redirect_url'  => [
                    'type'        => 'text',
                    'label'       => __('App Redirect URI', 'fluent_booking'),
                    'placeholder' => __('Enter Your Redirect URI', 'fluent_booking'),
                    'readonly'    => true,
                    'copy_btn'    => true,
                ],
            ];

            $config = ZoomHelper::getApiConfig();

            $description = '<p>Please read the step by step documentation to setup client ID and Client Secret for your app. <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/zoom-integration-with-fluent-booking/">Go to the documentation article</a></p>';

            if (!empty($config['constant_defined'])) {
                $fields = null;
                $description = '<p>Zoom Meeting integration is configured by wp-config.php constants. No action required here</p>';
            }

            return [
                'logo'          => $app['url.assets'] . 'images/zoom.svg',
                'title'         => __('Zoom Meeting', 'fluent_booking'),
                'subtitle'      => __('Configure Zoom to create dynamic meeting for your bookings', 'fluent_booking'),
                'description'   => $description,
                'save_btn_text' => __('Save Settings', 'fluent_booking'),
                'fields'        => $fields,
                'will_encrypt'  => true
            ];
        });
        add_action('fluent_booking/save_client_settings_zoom_meeting', function ($settings) {
            ZoomHelper::updateApiConfig($settings);
        });

        /*
         * Calendar Level Settings
         */
        add_filter('fluent_booking/calendar_setting_menu_items', function ($menuItems, $calendar) {
            if (!ZoomHelper::isConfigured()) {
                return $menuItems;
            }

            $menuItems['zoom_meeting'] = [
                'type'    => 'route',
                'route'   => [
                    'name'   => 'calendar_general_integration_settings',
                    'params' => [
                        'id'           => $calendar->id,
                        'settings_key' => 'zoom_meeting'
                    ]
                ],
                'label'   => __('Zoom Integration', 'fluent-booking'),
                'svgIcon' => '<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 48 48" width="48px" height="48px"><circle cx="24" cy="24" r="20" fill="#2196f3"/><path fill="#fff" d="M29,31H14c-1.657,0-3-1.343-3-3V17h15c1.657,0,3,1.343,3,3V31z"/><polygon fill="#fff" points="37,31 31,27 31,21 37,17"/></svg>'
            ];

            return $menuItems;

        }, 10, 2);
        add_filter('fluent_booking/get_general_integration_feed_zoom_meeting', function ($data, $calendar) {
            if (!ZoomHelper::isConfigured()) {
                return $data;
            }

            $driverInfo = [
                'key'            => 'zoom_meeting',
                'title'          => 'Zoom Video Meeting Integration',
                'description'    => 'Create zoom meeting from your booked events. Connect your zoom account to create dynamic meeting for your bookings.',
                'configure_type' => 'require_oauth',
                'icon'           => App::getInstance()['url.assets'] . 'images/zoom.svg',
                'oauth_content'  => [
                    'instruction' => 'To use Zoom Video Meeting feature as a meeting location please connect with your Zoom account.',
                    'btn_text'    => 'Connect with Zoom',
                    'btn_url'     => ZoomHelper::getOauthRedirectUrl($calendar->id),
                    'title'       => 'Zoom Video Meeting',
                    'subtitle'    => 'Configure Zoom to use the video meeting feature for your bookings',
                ],
                'configure_url'  => ZoomHelper::getAppRedirectUrl(),
            ];

            return [
                'driver' => $driverInfo
            ];

        }, 10, 2);

        add_action('wp_ajax_fluent_booking_zoom_auth', [$this, 'handleAuthCallback']);

        add_action('init', function () {
            if (!isset($_REQUEST['zoom'])) {
                return;
            }

            $calendar = Calendar::find(3);
            $access = ZoomHelper::getCalendarApiClient($calendar);
            $me = $access->me();
            dd($me);
        });

    }

    public function handleAuthCallback()
    {
        if (empty($_REQUEST['code'])) {
            return;
        }

        $code = Arr::get($_REQUEST, 'code');
        $calendarId = (int)Arr::get($_REQUEST, 'state');

        $calendar = Calendar::find($calendarId);

        if (!$calendar) {
            return;
        }

        $client = ZoomHelper::getApiClient();
        $response = $client->generateAuthCode($code);

        if (is_wp_error($response)) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Failed to connect Zoom API', 'fluent-booking'),
                'body'     => 'Zoom API Response Error: ' . $response->get_error_message(),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/calendar-general-integration-settings/zoom_meeting'),
                'btn_text' => 'Back to Zoom Configuration'
            ]);
            return;
        }

        $requiredScopes = [];

        // Verify the scopes
        $returnedScopes = explode(' ', $response['scope']);
        if (!in_array('meeting:write', $returnedScopes)) {
            $requiredScopes[] = 'meeting:write';
        }

        if (!in_array('meeting:read', $returnedScopes)) {
            $requiredScopes[] = 'meeting:read';
        }

        if ($requiredScopes) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Required scopes missing', 'fluent-booking'),
                'body'     => 'Looks like you did not allow the required scopes. Please try again with the following scopes: ' . implode(', ', $requiredScopes) . print_r($response, true),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/calendar-general-integration-settings/zoom_meeting'),
                'btn_text' => 'Back to Zoom Configuration'
            ]);
        }

        $response['expires_in'] += time();
        $response = Arr::only($response, ['access_token', 'refresh_token', 'expires_in', 'token_type']);

        ZoomHelper::updateAccessConfig($response, $calendar);

        wp_redirect(Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/calendar-general-integration-settings/zoom_meeting'));
        exit;
    }
}
