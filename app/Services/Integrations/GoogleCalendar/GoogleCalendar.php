<?php

namespace FluentCalendar\App\Services\Integrations\GoogleCalendar;

use Exception;
use FluentCalendar\Framework\Support\Arr;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Services\Integrations\IntegrationManager;
use FluentCalendar\App\Services\Integrations\GoogleCalendar\Client;
use FluentCalendar\Framework\Validator\ValidationException;

class GoogleCalendar extends IntegrationManager
{
    private $client;

    public function __construct()
    {
        parent::__construct(
            'google_calendar',
            'google_calendar_auth',
            'google_calendar_settings',
            '_fcal_google_calendar_client_details'
        );

        $credentials  = $this->getClientDetails();

        $this->clientId     = Arr::get($credentials, 'client_id');
        $this->clientSecret = Arr::get($credentials, 'client_secret');
        $this->redirectUrl  = Arr::get($credentials, 'redirect_url');
        
        if ($credentials) {
            $this->client = new Client(
                $this->clientId,
                $this->clientSecret,
                $this->redirectUrl
            );

            $this->enqueueAssets();

            add_action('template_redirect', [$this, 'init']);
            add_action('fluent_calendar/after_booking_scheduled', [$this, 'updateEvent'], 10, 2);
            add_action('fluent_calendar/after_patch_booking_schedule', [$this, 'updateEvent'], 10, 1);
        }
    }

    public function init()
    {
        if (!isset($_GET['code'], $_GET['scope'])) {
            return;
        }
        
        $code = sanitize_text_field($_GET['code']);

        $scope = sanitize_text_field($_GET['scope']);

        $this->authenticate($code);

        do_action('fluent_calendar/google_calendar_integration', $code, $scope);

        wp_redirect(admin_url('admin.php?page=fluent-calendar#/integrations'));

        exit;
    }

    public function authenticate($code)
    {
        $authData = $this->client->generateAccessToken($code, 'authorization_code');

        if (is_wp_error($authData)) {
            return;
        }

        $authData['code'] = $code;

        $authData['expires_in'] = $authData['expires_in'] + time();

        $this->updateAuthDetails($authData);
    }

    public function isConnected()
    {
        $accessToken = $this->getAccessToken();

        return $accessToken ? true : false;
    }

    public function enqueueAssets()
    {
        $slug = $this->app->config->get('app.slug');

        $assets = $this->app['url.assets'];

        wp_enqueue_script(
            $slug . '_admin_app',
            $assets . 'admin/app.js',
            array('jquery'),
            '1.0',
            true
        );

        wp_localize_script($slug . '_admin_app', 'fluentFramework_' . $this->integrationKey, [
            'auth_url'  => $this->client->getAuthUrl(),
            'connected' => $this->isConnected()
        ]);
    }

    public function getAccessToken($hostId = null)
    {
        $tokens = $this->getAuthDetails($hostId);

        if (!$tokens) {
            return false;
        }

        $expiresIn    = Arr::get($tokens, 'expires_in');
        $refreshToken = Arr::get($tokens, 'refresh_token');

        if (($expiresIn - 30) < time()){
            $tokens = $this->client->reGenerateToken($refreshToken);

            if (!$tokens) {
                return false;
            }
            
            $this->updateAuthDetails($tokens, $hostId);
        }

        return $tokens['access_token'];
    }

    public function getClientSettings()
    {
        $defaults = [
            'client_id'     => '',
            'client_secret' => '',
            'redirect_url'  => site_url('/google-calendar-integration/fluent-calendar'),
        ];

        $clientDetails = $this->getClientDetails();

        $settings = wp_parse_args($clientDetails, $defaults);

        return $settings;
    }

    public function saveClientSettings($settings)
    {
        $rules = [
            'client_id'     => 'required',
            'client_secret' => 'required',
            'redirect_url'  => 'required'
        ];

        $validator = $this->app->validator->make($settings, $rules, []);

        if ($validator->validate()->fails()) {
            throw new ValidationException(
                'Unprocessable Entity!', 422, null, $validator->errors()
            );
        }

        $data = [
            'client_id'     => sanitize_text_field(Arr::get($settings, 'client_id')),
            'client_secret' => sanitize_text_field(Arr::get($settings, 'client_secret')),
            'redirect_url'  => sanitize_url(Arr::get($settings, 'redirect_url'))
        ];

        $this->updateClientDetails($data);

        wp_send_json([
            'message' => __('Your Google Calendar api key has been successfully set'),
            'status'  => true
        ], 200);
    }

    public function getIntegrationSettings()
    {
        $defaults = [
            'check_conflict'  => '',
            'add_to_calendar' => ''
        ];

        $integrationSettings = $this->getIntegrationDetails();

        $settings = wp_parse_args($integrationSettings, $defaults);

        return $settings;
    }

    public function saveIntegrationSettings($settings)
    {
        $data = [
            'check_conflict'  => Arr::isTrue($settings, 'check_conflict'),
            'add_to_calendar' => Arr::isTrue($settings, 'add_to_calendar')
        ];

        $this->updateIntegrationDetails($data);

        wp_send_json([
            'message' => __('Your Google Calendar settings has been successfully set'),
            'status'  => true
        ], 200);
    }

    public function disconnectIntegration()
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new \Exception('You are already disconnected', 423);
        }

        $body = [
            'token' => $accessToken,
        ];

        $response = static::makeRequest($this->client->revokeUrl, $body, 'POST');

        if (is_wp_error($response)) {
            throw new \Exception('Something went wrong. Please try again', 423);
        }

        $this->deleteAuthDetails();

        wp_send_json([
            'message' => __('Your Google Calendar integration has been successfully removed'),
            'status'  => true
        ], 200);
    }

    public function updateEvent($booking, $calendarSlot = null)
    {
        $integrationSettings = $this->getIntegrationDetails();

        if (!Arr::isTrue($integrationSettings, 'add_to_calendar')) {
            return;
        }

        if (!$calendarSlot) {
            $calendarSlot = CalendarSlot::findOrFail($booking->slot_id);
        }

        $accessToken = $this->getAccessToken($calendarSlot->user_id);
        
        if (!$accessToken) {
            return;
        }
        
        $header = static::getStandardHeader($accessToken);

        $eventDetails = $this->getResponse($booking->event_id);

        $eventId     = Arr::get($eventDetails, 'id');
        $meetingLink = Arr::get($eventDetails, 'hangoutLink');

        $method = $eventId ? 'PUT' : 'POST';
        
        $url = $this->client->calendarEvent . $eventId;

        $locationType = Arr::get($booking, 'location_details.location_type');

        $location = ('phone' ==  $locationType) ? $booking->phone : $locationType;

        $events = [
            'summary'     => $calendarSlot->title,
            'location'    => $location,
            'description' => $booking->message,
            'attendees'   => [
                ['email' => $booking->email]
            ],
        ];
        $events['start'] = [
            'dateTime' => date(DATE_ISO8601, strtotime($booking->start_time)),
            'timeZone' => $booking->person_time_zone,
        ];
        $events['end'] = [
            'dateTime' => date(DATE_ISO8601, strtotime($booking->end_time)),
            'timeZone' => $booking->person_time_zone,
        ];

        if ('cancelled' == $booking->status) {
            $events['status'] = 'cancelled';
        } else {
            $events['status'] = 'confirmed';
        }

        if ('google_meet' == $locationType && !$meetingLink) {
            $events['conferenceData'] = [
                'createRequest' => [
                    'requestId' => $booking->hash,
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet'
                    ],
                ],
            ];

            $query = build_query([
                'conferenceDataVersion' => '1',
                'sendUpdates' => 'all',
            ]);

            $url .= '?' . $query;
        }

        $response = static::makeRequest($url, $events, $method, $header);

        if (is_wp_error($response)) {
            return;
        }

        $this->updateResponse($booking->event_id, $response);
    }
}