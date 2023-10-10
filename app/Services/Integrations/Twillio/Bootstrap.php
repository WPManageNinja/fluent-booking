<?php

namespace FluentBooking\App\Services\Integrations\Twillio;


use FluentBooking\App\App;

class Bootstrap
{
    public function register()
    {
        add_filter('fluent_booking/settings_menu_items', function ($menuItems) {
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
        }, 12, 1);
        
    }
}
