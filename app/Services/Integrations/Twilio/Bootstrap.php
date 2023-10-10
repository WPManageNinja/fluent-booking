<?php

namespace FluentBooking\App\Services\Integrations\Twilio;


use FluentBooking\App\App;

class Bootstrap
{
    public function register()
    {
        add_filter('fluent_booking/settings_menu_items', [$this, 'addGlobalMenu'], 12, 1);
        add_filter('fluent_booking/get_client_settings_twilio', [$this, 'getOauthClientSettings']);
        add_filter('fluent_booking/get_client_field_settings_twilio', [$this, 'getOauthClientSettingsFields']);
    }

    public function addGlobalMenu($menuItems)
    {
        $app = App::getInstance();
        $menuItems['twilio'] = [
            'title' => __('SMS by Twilio', 'fluent-booking'),
            'icon_url' => $app['url.assets'] . 'images/twilio.svg',
            'component_type' => 'GlobalSettingsComponent',
            'route' => [
                'name' => 'configure-integrations',
                'params' => [
                    'settings_key' => 'twilio'
                ]
            ]
        ];
        return $menuItems;
    }

    public function getOauthClientSettings($settings)
    {
        return [
            'client_id' => '',
            'client_secret' => ''
        ];
    }

    public function getOauthClientSettingsFields($items)
    {
        $app = App::getInstance();

        $fields = null;

        return [
            'logo'          => $app['url.assets'] . 'images/twilio.svg',
            'title'         => __('Twilio SMS Integration', 'fluent_booking'),
            'subtitle'      => __('Configure Twilio API to send SMS notifications on booking events', 'fluent_booking'),
            'description'   => '<h3>Twilio SMS integration will be available in our next version.</h3>',
            'save_btn_text' => __('Save Settings', 'fluent_booking'),
            'fields'        => $fields,
            'will_encrypt'  => false
        ];
    }
}
