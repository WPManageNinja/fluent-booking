<?php

namespace FluentBooking\App\Services\Integrations\ZoomMeeting;

use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\ZoomMeeting\ZoomHelper;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Services\Integrations\IntegrationHelper;

class Client
{
    public $clientId;
    public $clientSecret;
    public $redirectUrl;

    private $accessToken;

    public $revokeUrl = 'https://zoom.us/oauth/revoke';
    public $tokenUrl = 'https://zoom.us/oauth/token';


    public function __construct($clientID, $clientSecret, $redirectUrl = null)
    {
        $this->clientId = $clientID;
        $this->clientSecret = $clientSecret;

        if (!$redirectUrl) {
            $redirectUrl = ZoomHelper::getAppRedirectUrl();
        }

        $this->redirectUrl = $redirectUrl;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    private function getAccessHeader()
    {
        return [
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)
        ];
    }

    private function getAuthorizationHeader($accessToken = null)
    {
        if (!$accessToken) {
            $accessToken = $this->accessToken;
        }

        return [
            'Authorization' => 'Bearer ' . $accessToken
        ];
    }

    public function generateAuthCode($code)
    {
        $body = [
            'code'         => $code,
            'grant_type'   => 'authorization_code',
            'redirect_uri' => $this->redirectUrl
        ];

        return $this->makeRequest($this->tokenUrl, $body, 'POST');
    }

    public function reGenerateToken($refreshToken)
    {
        $body = [
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token'
        ];

        return $this->makeRequest($this->tokenUrl, $body, 'POST');
    }

    public function me()
    {
        return $this->makeRequest('https://api.zoom.us/v2/users/me', [], 'GET', $this->getAuthorizationHeader());
    }

    public function revokeConnection()
    {
        return $this->makeRequest($this->revokeUrl, [
            'token' => $this->accessToken
        ], 'POST');
    }

    public function createMeeting($data)
    {
        $header = [];
        $header['Authorization'] = 'Bearer ' . $this->accessToken;
        $header['content-type'] = 'application/json';

        $url = 'https://api.zoom.us/v2/users/me/meetings';
        $data = json_encode($data);
        return $this->makeRequest($url, $data, 'POST', $header);
    }

    public function patchMeeting($meetingId, $data)
    {
        $header = [];
        $header['Authorization'] = 'Bearer ' . $this->accessToken;
        $header['content-type'] = 'application/json';

        $url = 'https://api.zoom.us/v2/meetings/' . $meetingId;
        $data = json_encode($data);
        return $this->makeRequest($url, $data, 'PATCH', $header);
    }

    public function deleteMeeting($meetingId)
    {
        $header = [];
        $header['Authorization'] = 'Bearer ' . $this->accessToken;
        $header['content-type'] = 'application/json';

        $url = 'https://api.zoom.us/v2/meetings/' . $meetingId;
        return $this->makeRequest($url, '', 'DELETE', $header);
    }

    public function makeRequest($url, $body = null, $type = 'GET', $headers = null)
    {
        if (!$headers) {
            $headers = $this->getAccessHeader();
        }

        $args = [
            'headers' => $headers,
            'method'  => $type,
            'timeout' => 20
        ];

        if ($body) {
            if ($type == 'GET') {
                $url = add_query_arg($body, $url);
            } else {
                $args['body'] = $body;
            }
        }

        $request = wp_remote_request($url, $args);

        if (is_wp_error($request)) {
            $message = $request->get_error_message();
            Helper::debugLog([
                'message' => $message,
                'url'     => $url,
                'body'    => $body,
                'method'  => __METHOD__,
                'type'    => 'wp_request_error'
            ]);
            return new \WP_Error('wp_error', $message, $request->get_all_error_data());
        }

        $resCode = wp_remote_retrieve_response_code($request);

        $resBody = json_decode(wp_remote_retrieve_body($request), true);

        if ($resCode > 299) {
            $message = Arr::get($resBody, 'error_description', 'Unexpected error from remote api');

            Helper::debugLog([
                'message' => $message,
                'url'     => $url,
                'body'    => $body,
                'method'  => __METHOD__,
                'type'    => 'api_error'
            ]);

            return new \WP_Error('api_error', $message, $resBody);
        }

        return $resBody;
    }
}
