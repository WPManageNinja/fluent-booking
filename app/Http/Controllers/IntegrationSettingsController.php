<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Services\Helper;

class IntegrationSettingsController extends Controller
{
    /**
     * Request object
     *
     * @var \FluentForm\Framework\Request\Request $request
     */
    protected $request;

    public function index()
    {
        try {
            $settingsKey = sanitize_text_field($this->request->get('settings_key'));

            $settings = apply_filters('fluent_booking/get_integration_settings_' . $settingsKey, []);

            $fieldSettings = apply_filters('fluent_booking/get_integration_field_settings_' . $settingsKey, []);

            return $this->sendSuccess([
                'status'         => true,
                'settings'       => $settings,
                'field_settings' => $fieldSettings,
            ]);

        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function getIntegrationsMenu()
    {
        try {

            $baseUrl   = Helper::getAppBaseUrl();
            $menuItems = apply_filters('fluent_booking/integrations_menu_items', [
                'google_calendar' => [
                    'key'       => 'google_calendar',
                    'label'     => __('Google Calendar', 'fluent-booking'),
                    'svgIcon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M2.5 7.59166V12.4C2.5 14.1667 2.5 14.1667 4.16667 15.2917L8.75 17.9417C9.44167 18.3417 10.5667 18.3417 11.25 17.9417L15.8333 15.2917C17.5 14.1667 17.5 14.1667 17.5 12.4083V7.59166C17.5 5.83333 17.5 5.83333 15.8333 4.70833L11.25 2.05833C10.5667 1.65833 9.44167 1.65833 8.75 2.05833L4.16667 4.70833C2.5 5.83333 2.5 5.83333 2.5 7.59166Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    'permalink' => $baseUrl . '/google_calendar'
                ]
            ]);

            return $this->sendSuccess([
                'status'         => true,
                'menu_items'     => $menuItems
            ]);

        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function update()
    {
        try {
            $settingsKey = sanitize_text_field($this->request->get('settings_key'));

            $settings = wp_unslash($this->request->get('settings'));

            do_action('fluent_booking/save_integration_settings_' . $settingsKey, $settings);

        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
