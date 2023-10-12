<?php

namespace FluentBooking\App\Services\Integrations\ZoomMeeting;


use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarHelper;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\Framework\Support\Arr;

class Bootstrap
{
    public function register()
    {
        /*
         * Global Settings
         */
        add_filter('fluent_booking/settings_menu_items', [$this, 'addGlobalMenu'], 11, 1);
        add_filter('fluent_booking/get_client_settings_zoom_meeting', [$this, 'getOauthClientSettings']);
        add_filter('fluent_booking/get_client_field_settings_zoom_meeting', [$this, 'getOauthClientSettingsFields']);
        add_action('fluent_booking/save_client_settings_zoom_meeting', function ($settings) {
            ZoomHelper::updateApiConfig($settings);
        });

        /*
         * Oauth Flow Settings
         */
        add_filter('fluent_booking/calendar_setting_menu_items', [$this, 'addConnectMenu'], 10, 2);
        add_filter('fluent_booking/get_general_integration_feed_zoom_meeting', [$this, 'getConnectFeedDriver'], 10, 2);
        add_action('fluent_booking/disconnect_general_integration_feed_zoom_meeting', [$this, 'disconnectAccount']);
        add_action('wp_ajax_fluent_booking_zoom_auth', [$this, 'handleAuthCallback']);

        /*
         * Booking Level Hooks
         */
        add_action('fluent_booking/pre_after_booking_scheduled', [$this, 'maybeCreateZoomMeeting'], 10, 2);

        /*
         * Location Hooks
         */

        add_filter('fluent_booking/get_location_fields', function ($fields, $calendar) {

            if (!ZoomHelper::isConfigured()) {
                return $fields;
            }

            $config = ZoomHelper::getAccessConfig($calendar);

            if (!$config || empty($config['access_token'])) {
                return $fields;
            }

            $fields['conferencing']['options']['zoom_meeting'] = [
                'title'         => 'Zoom Video',
                'disabled'      => false,
                'location_type' => 'conferencing'
            ];

            return $fields;
        }, 10, 2);
    }

    public function addGlobalMenu($menuItems)
    {
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
    }

    public function getOauthClientSettings($settings)
    {
        $config = ZoomHelper::getApiConfig();
        $config['redirect_url'] = ZoomHelper::getAppRedirectUrl();

        if (!empty($config['constant_defined'])) {
            $config['client_secret'] = '**********';
            $config['client_id'] = '**********';
        } else if (!empty($config['client_secret'])) {
            $config['client_secret'] = '********************';
        }

        return $config;
    }

    public function getOauthClientSettingsFields($items)
    {
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

        $description = '<p>Please read the step-by-step documentation to setup client ID and Client Secret for your app. <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/zoom-integration-with-fluent-booking/">Go to the documentation article</a></p>';

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
    }

    public function addConnectMenu($menuItems, $calendar)
    {
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
    }

    public function getConnectFeedDriver($data, $calendar)
    {
        if (!ZoomHelper::isConfigured()) {
            return $data;
        }

        $driverInfo = [
            'key'            => 'zoom_meeting',
            'title'          => 'Zoom Video Meeting Integration',
            'description'    => 'Create zoom meeting from your booked events. Connect your zoom account to create dynamic meeting for your bookings.',
            'configure_type' => 'oauth',
            'icon'           => App::getInstance()['url.assets'] . 'images/zoom.svg',
            'secure_message' => 'Your Zoom API tokens will be encrypted and stored securely.'
        ];

        $accessConfig = ZoomHelper::getAccessConfig($calendar);
        if ($accessConfig) {
            $me = ZoomHelper::getCalendarApiClient($calendar)->me();

            $errorMessage = '';

            if (is_wp_error($me)) {
                $errorMessage = $me->get_error_message();
                $driverInfo['secure_message'] = '';
            } else {
                $driverInfo['icon'] = $me['pic_url'];
                $driverInfo['secure_message'] = 'Your Zoom API tokens are encrypted and stored securely';

                if (empty($accessConfig['account_email']) || $accessConfig['account_email'] != $me['email']) {
                    $accessConfig['account_email'] = $me['email'];
                    ZoomHelper::updateAccessConfig($accessConfig, $calendar);
                }
            }

            $driverInfo['description'] = 'Create zoom meeting from your booked events. An Account is connected with this calendar. From this Calendar event you can now select Zoom Video as a location to create meeting in zoom automatically event booking.';

            $instruction = 'The following Zoom Account is connected with this calendar. Based on your event location, a zoom meeting will be created for your bookings.';

            if ($errorMessage) {
                $instruction = 'Looks like, the system failed to connect with your connected account. Please reload this page or disconnect the account and connect again.';
            }

            $driverInfo['oauth_content'] = [
                'instruction'     => $instruction,
                'disconnect_text' => 'Disconnect',
                'title'           => ($errorMessage) ? 'Failed to connect Zoom API.' : $me['display_name'] . ' (' . $me['email'] . ')',
                'subtitle'        => ($errorMessage) ? '<span style="color: red;">Error Message From API: ' . $errorMessage . '</span>' : 'Connected Zoom Account',
                'error'           => $errorMessage
            ];
        } else {
            $driverInfo['oauth_content'] = [
                'instruction' => 'To use Zoom Video Meeting feature as a meeting location please connect with your Zoom account.',
                'btn_text'    => 'Connect with Zoom',
                'btn_url'     => ZoomHelper::getOauthRedirectUrl($calendar->id),
                'title'       => 'Zoom Video Meeting',
                'subtitle'    => 'Configure Zoom to use the video meeting feature for your bookings',
            ];
        }

        return [
            'driver' => $driverInfo
        ];
    }

    public function disconnectAccount($calendar)
    {
        $api = ZoomHelper::getCalendarApiClient($calendar);
        if (!is_wp_error($api)) {
            $api->revokeConnection();
        }
        $calendar->updateMeta('_zoom_integration_config', []);
    }

    public function handleAuthCallback()
    {
        if (empty($_REQUEST['code'])) {
            return;
        }

        $code = Arr::get($_REQUEST, 'code');
        $calendarId = (int)Arr::get($_REQUEST, 'state');

        $calendar = Calendar::find($calendarId);

        if (!$calendar || !PermissionManager::hasCalendarAccess($calendar)) {
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

    public function maybeCreateZoomMeeting($booking, $calendarSlot)
    {
        if (Arr::get($booking->location_details, 'type') !== 'zoom_meeting') {
            return false; // not our location
        }

        $calendar = $calendarSlot->calendar;
        if (!ZoomHelper::isConfigured()) {
            return;
        }

        $config = ZoomHelper::getAccessConfig($calendar);

        if (!$config || empty($config['access_token'])) {
            return;
        }

        if ($booking->getMeta('__zoom_meeting_details')) {
            return false; // Already created
        }

        // let's prepare the booking data
        $data = apply_filters('fluent_booking/zoom_meeting_data', [
            'agenda'       => $calendarSlot->title,
            'duration'     => 30,
            'type'         => 2,
            'settings'     => [
                'meeting_invitees' => [
                    [
                        'email' => $booking->email
                    ]
                ],
            ],
            'schedule_for' => $config['account_email'],
            'start_time'   => date('Y-m-d\TH:i:s\Z', strtotime($booking->start_time)),
            'topic'        => sprintf('%1s meeting with %2s', $calendarSlot->title, trim($booking->first_name . ' ' . $booking->last_name)),
        ], $booking, $calendarSlot);

        $api = ZoomHelper::getCalendarApiClient($calendar);
        if (is_wp_error($api)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => 'Zoom API Error',
                'description' => __(sprintf('Failed to create meeting with Zoom API. API Response: %s', $api->lastError->get_error_message()), 'fluent-booking')
            ]);
            return false;
        }

        $response = $api->createMeeting($data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => 'Zoom API Error',
                'description' => __(sprintf('Failed to create meeting with Zoom API. API Response: %s', $api->lastError->get_error_message()), 'fluent-booking')
            ]);
            return false;
        }

        $responseData = Arr::only($response, ['id', 'start_url', 'join_url', 'password']);
        $booking->updateMeta('__zoom_meeting_details', $responseData);

        $location = $booking->location_details;
        $location['online_platform_link'] = Arr::get($responseData, 'join_url');
        $location['online_platform_start_link'] = Arr::get($responseData, 'start_url');
        $booking->location_details = $location;
        $booking->save();

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Zoom Meeting has been created', 'fluent-booking'),
            'description' => __(sprintf('Zoom Meeting has been scheduled. %s', '<a target="_blank" href="' . $location['online_platform_start_link'] . '">' . __('Start Meeting URL', 'fluent-booking') . '</a>'), 'fluent-booking')
        ]);

        return true;
    }
}
