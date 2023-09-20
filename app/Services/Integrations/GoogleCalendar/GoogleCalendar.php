<?php

namespace FluentBooking\App\Services\Integrations\GoogleCalendar;

use Exception;
use FluentBooking\App\Models\Booking;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Integrations\IntegrationManager;
use FluentBooking\App\Services\Integrations\GoogleCalendar\Client;
use FluentBooking\Framework\Validator\ValidationException;

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
            $this->initClient();
            $this->enqueueAssets();
            $this->initHooks();
        }
    }

    public function initClient()
    {
        $this->client = new Client(
            $this->clientId,
            $this->clientSecret,
            $this->redirectUrl
        );
    }

    public function initHooks()
    {
        add_action('fluent_booking/after_booking_scheduled', [$this, 'updateEvent'], 10, 2);
        add_action('fluent_booking/after_patch_booking_schedule', [$this, 'updateEvent'], 10, 1);
        add_filter('fluent_booking/booked_events', [$this, 'getBookedEvents'], 10, 4);
        add_action('wp_ajax_fluent_booking_g_auth', [$this, 'handleAuthCallback'] );
    }

    public function handleAuthCallback()
    {
        if (!isset($_GET['code'], $_GET['scope'])) {
            return;
        }
        
        $code = sanitize_text_field($_GET['code']);

        $scope = sanitize_text_field($_GET['scope']);

        $this->authenticate($code);

        do_action('fluent_booking/google_calendar_integration', $code, $scope);

        wp_redirect(admin_url('admin.php?page=fluent-booking#/integrations'));

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

        do_action('fluent_booking/google_calendar_authenticated', $authData);
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

    public function isConnected()
    {
        $accessToken = $this->getAccessToken();

        return $accessToken ? true : false;
    }

    protected function getHostEmail($hostId)
    {
        $user = get_user_by('id', $hostId);
        if (!$user) {
            return '';
        }
        return $user->user_email;
    }

    protected function getEventLocation($booking)
    {
        $locationType = Arr::get($booking, 'location_details.location_type');

        $location = Arr::get($booking, 'location_details.location_heading');

        if ('phone' == $locationType) {
            $location = 'Phone Call: ' . $booking->phone;
        } elseif ('google_meet' == $locationType) {
            $location = 'Google Meet';
        }

        return $location;
    }

    protected function getAttendees($email, $attendees)
    {
        $emails = array_column($attendees, 'email');

        if (!in_array($email, $emails)) {
            $attendees[] = ['email' => $email];
        }

        return $attendees;
    }

    public function getClientSettings()
    {
        $defaults = [
            'client_id'     => '',
            'client_secret' => '',
            'redirect_url'  => admin_url('/admin-ajax.php?action=fluent_booking_g_auth'),
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
            'check_conflict'  => false,
            'add_to_calendar' => false
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

    protected function getUpdatedResponse($booking, $header)
    {
        $eventDetails = $this->getResponse($booking->event_id);

        $eventId = Arr::get($eventDetails, 'id');

        if (!$eventId) {
            return [];
        }

        $url = $this->client->calendarEvent . $eventId;

        $response = static::makeRequest($url, '', 'GET', $header);

        if (is_wp_error($response)) {
            return $eventDetails;
        }

        return $response;
    }

    public function updateEvent($booking, $calendarSlot = null)
    {
        if (!$calendarSlot) {
            $calendarSlot = CalendarSlot::findOrFail($booking->slot_id);
        }

        $integrationSettings = $this->getIntegrationDetails($calendarSlot->user_id);
        if (!Arr::isTrue($integrationSettings, 'add_to_calendar')) {
            return;
        }

        $accessToken = $this->getAccessToken($calendarSlot->user_id);
        if (!$accessToken) {
            return;
        }
        
        $header = static::getStandardHeader($accessToken);

        // If event is already created
        $eventDetails = $this->getUpdatedResponse($booking, $header);
        $eventId      = Arr::get($eventDetails, 'id');
        $meetingLink  = Arr::get($eventDetails, 'hangoutLink');
        $attendees    = Arr::get($eventDetails, 'attendees');

        $isNewEvent = !$eventId;

        $method = $isNewEvent ? 'POST' : 'PATCH';
        
        $url = $this->client->calendarEvent . $eventId . '?sendUpdates=all';

        $hostEmail = $this->getHostEmail($calendarSlot->user_id);

        $location = $this->getEventLocation($booking);

        $events = [
            'summary'     => $calendarSlot->title,
            'description' => $booking->message,
            'attendees'   => [
                ['email' => $hostEmail],
                ['email' => $booking->email]
            ],
            'start' => [
                'dateTime' => DateTimeHelper::convertToIso($booking->start_time),
                'timeZone' => $booking->person_time_zone,
            ],
            'end' => [
                'dateTime' => DateTimeHelper::convertToIso($booking->end_time),
                'timeZone' => $booking->person_time_zone,
            ],
            'location' => $location,
            'status' => ('cancelled' == $booking->status) ? 'cancelled' : 'confirmed',
            'extendedProperties' => [
                'shared' => [
                    'created_by' => 'fluent_booking',
                ],
            ],
        ];

        if (!$isNewEvent) {
            $events['attendees'] = $this->getAttendees($booking->email, $attendees);
        }

        if ('Google Meet' == $location && !$meetingLink) {
            $events['conferenceData'] = [
                'createRequest' => [
                    'requestId' => $booking->hash,
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet'
                    ],
                ],
            ];
            $url .= '&conferenceDataVersion=1';
        }

        $response = static::makeRequest($url, $events, $method, $header);

        if (is_wp_error($response)) {
            return;
        }

        $this->updateResponse($booking->event_id, $response);

        $this->updateEventLink($booking->id, $response);

        $this->logBookingActivity($isNewEvent, $booking->id, $response);

        do_action('fluent_booking/google_calendar_event_updated', $booking, $calendarSlot, $response);
    }

    public function getBookedEvents($books, $calendarSlot, $dateRanges, $timeZone)
    {
        $integrationSettings = $this->getIntegrationDetails($calendarSlot->user_id);
        if (!Arr::isTrue($integrationSettings, 'check_conflict')) {
            return $books;
        }

        $accessToken = $this->getAccessToken($calendarSlot->user_id);
        if (!$accessToken) {
            return $books;
        }
        
        $header = static::getStandardHeader($accessToken);
        
        $startRange = DateTimeHelper::convertToIso($dateRanges[0]);
        $endRange   = DateTimeHelper::convertToIso($dateRanges[1]);

        $query = [
            'timeMin' => $startRange,
            'timeMax' => $endRange,
        ];

        $url = $this->client->calendarEvent . '?' . http_build_query($query);

        $response = static::makeRequest($url, '', 'GET', $header);

        if (is_wp_error($response)) {
            return $books;
        }

        $bookedEvents = Arr::get($response, 'items');

        foreach ($bookedEvents as $event){
            
            if ('fluent_booking' == Arr::get($event, 'extendedProperties.shared.created_by')) {
                continue;
            }

            $startTime = Arr::get($event, 'start.dateTime');
            $endTime   = Arr::get($event, 'end.dateTime');

            $start = DateTimeHelper::convertFromIso($startTime, $timeZone);
            $end   = DateTimeHelper::convertFromIso($endTime, $timeZone);
            
            $date = date('Y-m-d', strtotime($start));

            $books[$date] = $books[$date] ?? [];

            $books[$date][] = [
                'start'     => $start,
                'end'       => $end,
                'remaining' => 0
            ];
        }

        return $books;
    }

    public function updateEventLink($bookingId, $response)
    {
        $meetingLink  = Arr::get($response, 'hangoutLink');
        if (!$meetingLink) {
            return;
        }

        $locationSettings = [
            'location_type' => 'google_meet',
            'location_heading' => 'Google Meet',
            'location_settings' => [
                'meeting_link'  => $meetingLink,
            ]
        ];

        $booking = Booking::findOrFail($bookingId);
        $booking->location_details = $locationSettings;
        $booking->save();

        do_action('fluent_booking/after_event_link_updated', $booking, $response);
    }

    public function logBookingActivity($isNewEvent, $bookingId, $response)
    {
        $htmlLink = Arr::get($response, 'htmlLink');
        if (!$isNewEvent || !$htmlLink) {
            return;
        }

        $eventLink = '<a target="_blank" href="' . esc_url($htmlLink) . '">' . esc_html('here') . '</a>';

        do_action('fluent_booking/log_booking_note', [
            'title'       => sprintf(__('Event Created in Google Calendar')),
            'type'        => 'activity',
            'description' => sprintf(__('Find Event in Google Calendar: %s'), $eventLink),
            'booking_id'  => $bookingId
        ]);
    }
}
