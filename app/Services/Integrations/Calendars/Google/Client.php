<?php

namespace FluentBooking\App\Services\Integrations\Calendars\Google;

use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Services\Integrations\IntegrationHelper;

class Client
{
    public $clientId;
    public $clientSecret;
    public $redirectUrl;

    public $revokeUrl = 'https://oauth2.googleapis.com/revoke';
    public $tokenUrl = 'https://oauth2.googleapis.com/token';
    public $authUrl = 'https://accounts.google.com/o/oauth2/auth';
    public $authScope = 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/calendar.readonly https://www.googleapis.com/auth/calendar.events';

    public $calendarEvent = 'https://www.googleapis.com/calendar/v3/calendars/primary/events/';

    public function __construct($clientID, $clientSecret)
    {
        $this->clientId = $clientID;
        $this->clientSecret = $clientSecret;

        //$this->redirectUrl = admin_url('admin-ajax.php?action=fluent_booking_g_auth');
        $this->redirectUrl = 'https://fluentbooking.com/wp-admin/admin-ajax.php?action=fluent_booking_g_auth';
    }

    public function generateAuthCode($code)
    {
        $body = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUrl,
            'grant_type'    => 'authorization_code',
            'code'          => $code
        ];

        return $this->makeRequest($this->tokenUrl, $body, 'POST');
    }

    public function reGenerateToken($refreshToken)
    {
        $body = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUrl,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken
        ];

        $tokens = $this->makeRequest($this->tokenUrl, $body, 'POST');

        if (is_wp_error($tokens)) {
            return $tokens;
        }

        $tokens['expires_in'] += time();

        return $tokens;
    }

    public function getCalendarLists($accessToken)
    {
        $lists = $this->makeRequest('https://www.googleapis.com/calendar/v3/users/me/calendarList', [], 'GET', [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json; charset=utf-8'
        ]);

        if (is_wp_error($lists)) {
            return $lists;
        }

        $formattedLists = [];

        foreach ($lists['items'] as $item) {
            $formattedLists[] = [
                'id'        => $item['id'],
                'title'     => $item['summary'],
                'can_write' => in_array($item['accessRole'], ['owner', 'writer']) ? 'yes' : 'no'
            ];
        }

        return $formattedLists;
    }

    public function generateAccessToken($token, $grantType = 'refresh_token')
    {
        $body = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUrl,
            'grant_type'    => $grantType
        ];

        if ($grantType == 'authorization_code') {
            $body['code'] = $token;
        } else {
            $body['refresh_token'] = $token;
        }

        return IntegrationHelper::makeRequest($this->tokenUrl, $body, 'POST');
    }

    public function getAuthorizationHeader($accessToken)
    {
        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json; charset=utf-8'
        ];
    }

    public function makeRequest($url, $body = null, $type = 'GET', $headers = null)
    {
        if (!$headers) {
            $headers = [
                'Content-Type'              => 'application/http',
                'Content-Transfer-Encoding' => 'binary',
                'MIME-Version'              => '1.0',
            ];
        }

        $args = [
            'headers' => $headers,
            'method'  => $type,
            'timeout' => 20
        ];

        if ($body) {
            $args['body'] = json_encode($body);
        }

        $request = wp_remote_request($url, $args);

        if (is_wp_error($request)) {
            $message = $request->get_error_message();
            return new \WP_Error(423, $message, $request->get_all_error_data());
        }

        $resCode = wp_remote_retrieve_response_code($request);

        $resBody = json_decode(wp_remote_retrieve_body($request), true);

        if ($resCode > 299) {
            $message = Arr::get($resBody, 'error_description', 'Unexpected error from google api');
            return new \WP_Error($resCode, $message, $resBody);
        }

        return $resBody;
    }


    public function getAuthUrl($calendarId)
    {
        $authUrl = add_query_arg([
            'client_id'     => $this->clientId,
            'scope'         => urlencode_deep($this->authScope),
            'redirect_uri'  => $this->redirectUrl,
            'response_type' => 'code',
            'access_type'   => 'offline',
            'state'         => $calendarId
        ], $this->authUrl);

        return $authUrl;
    }
}
