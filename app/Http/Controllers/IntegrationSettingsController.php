<?php

namespace FluentBooking\App\Http\Controllers;

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
