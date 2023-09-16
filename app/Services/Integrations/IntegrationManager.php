<?php

namespace FluentBooking\App\Services\Integrations;

use FluentBooking\App\App;
use FluentBooking\App\Services\Integrations\IntegrationHelper;

abstract class IntegrationManager extends IntegrationHelper
{
    public $app;
    public $userId;
    public $clientKey;
    public $authKey;
    public $settingsKey;
    public $integrationKey;

    public function __construct($integrationKey, $authKey, $settingsKey, $clientKey)
    {
        parent::__construct(
            $integrationKey,
            $authKey,
            $settingsKey,
            $clientKey
        );

        $this->app    = App::getInstance();
        $this->userId = get_current_user_id();

        $this->authKey        = $authKey;
        $this->settingsKey    = $settingsKey;
        $this->clientKey      = $clientKey;
        $this->integrationKey = $integrationKey;

        $this->registerAdminHooks();
    }

    public function registerAdminHooks()
    {
        add_filter('fluent_booking/get_client_settings_' . $this->integrationKey, [$this, 'getClientSettings'], 10, 1);
        add_filter('fluent_booking/get_integration_settings_' . $this->integrationKey, [$this, 'getIntegrationSettings'], 10, 1);

        add_action('fluent_booking/save_client_settings_' . $this->integrationKey, [$this, 'saveClientSettings'], 10, 1);
        add_action('fluent_booking/save_integration_settings_' . $this->integrationKey, [$this, 'saveIntegrationSettings'], 10, 1);

        add_action('fluent_booking/disconnect_integration_' . $this->integrationKey, [$this, 'disconnectIntegration'], 10, 0);
    }

    abstract public function getClientSettings();

    abstract public function saveClientSettings($settings);
    
    abstract public function getIntegrationSettings();

    abstract public function saveIntegrationSettings($settings);

    abstract public function disconnectIntegration();
}
