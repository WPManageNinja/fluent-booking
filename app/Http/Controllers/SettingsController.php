<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Services\GlobalModules\GlobalModules;
use FluentBooking\App\Services\Integrations\PaymentMethods\CurrenciesHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Libs\Countries;
use FluentBooking\Framework\Request\Request;
use FluentBooking\Framework\Support\Arr;

class SettingsController extends Controller
{
    public function getSettingsMenu()
    {
        return [
            'menu_items' => apply_filters('fluent_booking/settings_menu_items', []),
        ];
    }

    public function getGeneralSettings()
    {
        $settings = Helper::getGlobalSettings();

        $settings['emailingFields'] = [
            'from_name'               => [
                'wrapper_class' => 'fc_item_half',
                'type'          => 'input-text',
                'placeholder'   => __('From Name for emails', 'fluent-booking-pro'),
                'label'         => __('From Name', 'fluent-booking-pro'),
                'help'          => __('Default Name that will be used to send email)', 'fluent-booking-pro')
            ],
            'from_email'              => [
                'wrapper_class' => 'fc_item_half',
                'type'          => 'input-or-select',
                'placeholder'   => 'name@domain.com',
                'data_type'     => 'email',
                'options'       => Helper::getVerifiedSenders(),
                'label'         => __('From Email Address', 'fluent-booking-pro'),
                'help'          => __('Provide Valid Email Address that will be used to send emails', 'fluent-booking-pro'),
                'inline_help'   => __('email as per your domain/SMTP settings', 'fluent-booking-pro')
            ],
            'reply_to_name'           => [
                'wrapper_class' => 'fc_item_half',
                'type'          => 'input-text',
                'placeholder'   => __('Reply to Name', 'fluent-booking-pro'),
                'label'         => __('Reply to Name (Optional)', 'fluent-booking-pro'),
                'help'          => __('Default Reply to Name (Optional)', 'fluent-booking-pro')
            ],
            'reply_to_email'          => [
                'wrapper_class' => 'fc_item_half',
                'type'          => 'input-text',
                'placeholder'   => 'name@domain.com',
                'data_type'     => 'email',
                'label'         => __('Reply to Email (Optional)', 'fluent-booking-pro'),
                'help'          => __('Default Reply to Email (Optional)', 'fluent-booking-pro')
            ],
            'use_host_name'           => [
                'wrapper_class'  => 'fc_full_width fc_mb_0',
                'type'           => 'inline-checkbox',
                'checkbox_label' => __('Use host name as From Name for booking emails to guests', 'fluent-booking-pro'),
                'true_label'     => 'yes',
                'false_label'    => 'no',
            ],
            'use_host_email_on_reply' => [
                'wrapper_class'  => 'fc_full_width fc_mb_0',
                'type'           => 'inline-checkbox',
                'checkbox_label' => __('Use host email for reply-to value for booking emails to guests', 'fluent-booking-pro'),
                'true_label'     => 'yes',
                'false_label'    => 'no',
            ],
            'attach_ics_on_confirmation' => [
                'wrapper_class'  => 'fc_full_width fc_mb_0',
                'type'           => 'inline-checkbox',
                'checkbox_label' => __('Include ICS file attachment in booking confirmation emails', 'fluent-booking-pro'),
                'true_label'     => 'yes',
                'false_label'    => 'no',
            ],
            'email_footer'            => [
                'wrapper_class' => 'fc_full_width fc_mb_0 fc_wp_editor',
                'type'          => 'wp-editor-field',
                'label'         => __('Email Footer for Booking related emails (Optional)', 'fluent-booking-pro'),
                'inline_help'   => __('You may include your business name, address etc here, for example: <br />You have received this email because signed up for an event or made a booking on our website.', 'fluent-booking-pro')
            ]
        ];

        $settings['all_countries'] = Countries::get();

        $settings['all_currencies'] = CurrenciesHelper::getFormattedCurrencies();

        return $settings;
    }

    public function updateGeneralSettings(Request $request)
    {
        $settings = [
            'emailing'       => $request->get('emailing', []),
            'administration' => $request->get('administration', []),
        ];

        $formattedSettings = [];

        foreach ($settings as $settingKey => $setting) {
            $santizedSettings = array_map('sanitize_text_field', $setting);
            if ($settingKey == 'emailing') {
                $santizedSettings['email_footer'] = wp_kses_post($setting['email_footer']);
            }
            $formattedSettings[$settingKey] = $santizedSettings;
        }
        $formattedSettings['time_format'] = $request->get('timeFormat');
        $formattedSettings['theme'] = $request->get('theme');

        update_option('_fluent_booking_settings', $formattedSettings, 'no');

        return [
            'message'  => __('Settings updated successfully', 'fluent-booking-pro'),
            'settings' => $formattedSettings
        ];
    }

    public function updatePaymentSettings(Request $request)
    {
        $paymentSettings = $request->get('payments', []);

        $currency = Arr::get($paymentSettings, 'currency');
        $isActive = Arr::get($paymentSettings, 'is_active', 'no');

        update_option('fluent_booking_global_payment_settings', [
            'currency'  => sanitize_textarea_field($currency),
            'is_active' => ($isActive == 'yes') ? 'yes' : 'no'
        ], 'no');

        return [
            'message'  => __('Settings updated successfully', 'fluent-booking-pro')
        ];
    }

    public function getGlobalModules(Request $request)
    {
        $settings = Helper::getGlobalModuleSettings();


        if (!$settings) {
            $settings = (object)[];
        }

        return [
            'settings' => $settings,
            'modules'  => (new GlobalModules())->getAllModules()
        ];
    }

    public function updateGlobalModules(Request $request)
    {
        $settings = $request->get('settings', []);

        $modules = (new GlobalModules())->getAllModules();

        $formattedModules = [];

        foreach ($settings as $settingKey => $value) {
            if (!isset($modules[$settingKey])) {
                continue;
            }
            $formattedModules[$settingKey] = $value == 'yes' ? 'yes' : 'no';
        }

        Helper::updateGlobalModuleSettings($formattedModules);

        return [
            'message' => __('Settings updated successfully', 'fluent-booking-pro'),
        ];
    }
}
