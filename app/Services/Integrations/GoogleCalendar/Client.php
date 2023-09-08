<?php

namespace FluentCalendar\App\Services\Integrations\GoogleCalendar;

use FluentCalendar\Framework\Support\Arr;
use FluentCalendar\App\Services\Integrations\IntegrationHelper;

class Client
{
    public $clientId;
    public $clientSecret;
    public $redirectUrl;

    public $revokeUrl = 'https://oauth2.googleapis.com/revoke';
    public $tokenUrl  = 'https://oauth2.googleapis.com/token';
    public $authUrl   = 'https://accounts.google.com/o/oauth2/auth';
    public $authScope = 'https://www.googleapis.com/auth/calendar';

    public $calendarEvent = 'https://www.googleapis.com/calendar/v3/calendars/primary/events/';

    public function __construct($clientID, $clientSecret, $redirectUrl)
    {
        $this->clientId     = $clientID;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl  = $redirectUrl;
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

    public function reGenerateToken($refreshToken)
    {
        $tokens = $this->generateAccessToken($refreshToken);

        if (is_wp_error($tokens)) {
            return false;
        }

        $tokens['refresh_token'] = $refreshToken;

        $tokens['expires_in'] = $tokens['expires_in'] + time();

        return $tokens;
    }

    public function getAuthUrl()
    {
        $authUrl = add_query_arg([
            'client_id'     => $this->clientId,
            'scope'         => urlencode_deep($this->authScope),
            'redirect_uri'  => $this->redirectUrl,
            'response_type' => 'code',
            'access_type'   => 'offline'
		], $this->authUrl);

        return $authUrl;
    }
}