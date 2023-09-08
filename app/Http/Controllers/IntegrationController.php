<?php

namespace FluentCalendar\App\Http\Controllers;

class IntegrationController extends Controller
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

            $settings = apply_filters('fluent_calendar/get_client_settings_' . $settingsKey, []);

            return $this->sendSuccess([
                'status'   => true,
                'settings' => $settings,
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

            do_action('fluent_calendar/save_client_settings_' . $settingsKey, $settings);

        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function revoke()
    {
        try {
            $settingsKey = sanitize_text_field($this->request->get('settings_key'));
            
            do_action('fluent_calendar/disconnect_integration_' . $settingsKey);

        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
