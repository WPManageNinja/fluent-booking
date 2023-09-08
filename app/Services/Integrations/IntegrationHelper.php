<?php

namespace FluentCalendar\App\Services\Integrations;

use FluentCalendar\App\Services\Helper;
use FluentCalendar\Framework\Support\Arr;

class IntegrationHelper
{
    public $clientKey;
    public $authKey;
    public $settingsKey;
    public $integrationKey;

    public function __construct($integrationKey, $authKey, $settingsKey, $clientKey)
    {
        $this->clientKey      = $clientKey;
        $this->authKey        = $authKey;
        $this->settingsKey    = $settingsKey;
        $this->integrationKey = $integrationKey;
    }

    public function getClientDetails()
    {
        return get_option($this->clientKey);
    }

    public function updateClientDetails($data)
    {
        update_option($this->clientKey, $data, 'no');
    }

    public function getAuthDetails($id = null)
    {
        $id = $id ?: $this->userId;

        return Helper::getMeta($this->integrationKey, $id, $this->authKey);
    }

    public function updateAuthDetails($data, $id = null)
    {
        $id = $id ?: $this->userId;

        Helper::updateMeta($this->integrationKey, $id,  $this->authKey, $data);
    }

    public function deleteAuthDetails($id = null)
    {
        $id = $id ?: $this->userId;

        Helper::deleteMeta($this->integrationKey, $id,  $this->authKey);
    }

    public function getIntegrationDetails($id = null)
    {
        $id = $id ?: $this->userId;

        return Helper::getMeta($this->integrationKey, $id, $this->settingsKey);
    }

    public function updateIntegrationDetails($data, $id = null)
    {
        $id = $id ?: $this->userId;

        Helper::updateMeta($this->integrationKey, $id,  $this->settingsKey, $data);
    }

    public function getResponse($eventId)
    {
        return Helper::getBookingMeta($eventId, $this->integrationKey);
    }

    public function updateResponse($eventId, $response)
    {
        Helper::updateBookingMeta($eventId, $this->integrationKey, $response);
    }

    public static function getStandardHeader($accessToken)
    {        
        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json; charset=utf-8'
        ];
    }

    public static function getGeneralHeader()
    {   
        return [
            'Content-Type'              => 'application/http',
            'Content-Transfer-Encoding' => 'binary',
            'MIME-Version'              => '1.0',
        ];
    }

    public static function makeRequest($url, $body = null, $type = 'GET', $headers = null)
    {
        $headers = $headers ?: static::getGeneralHeader();

        $args = [
            'headers' => $headers,
            'method'  => $type
        ];

        if ($body) {
            $args['body'] = json_encode($body);
        }

        $request = wp_remote_request($url, $args);

        if (is_wp_error($request)) {
            $message = $request->get_error_message();
            return new \WP_Error(423, $message);
        }

        $resCode = wp_remote_retrieve_response_code($request);

        $resBody = json_decode(wp_remote_retrieve_body($request), true);

        if ($resCode != 200 && $resCode != 201) {
            $message = Arr::get($resBody, 'error.message', 'Something went wrong');
            return new \WP_Error($resCode, $message);
        }

        return $resBody;
    }
}
