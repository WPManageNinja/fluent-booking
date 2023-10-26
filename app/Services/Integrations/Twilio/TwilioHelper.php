<?php

namespace FluentBooking\App\Services\Integrations\Twilio;

use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Services\Integrations\Twilio\Client;

class TwilioHelper
{
    public static function getApiConfig()
    {
        $defaults = [
            'sender_number'   => '',
            'sender_whatsapp' => '',
            'account_sid'     => '',
            'auth_token'      => '',
            'verified'        => false
        ];

        $settings = get_option('_fcal_twilio_client_details', []);

        $settings = wp_parse_args($settings, $defaults);

        if (!empty($settings['auth_token'])) {
            $settings['auth_token'] = Helper::decryptKey($settings['auth_token']);
        }

        return $settings;
    }

    public static function updateApiConfig($settings)
    {
        $settings = Arr::only($settings, ['sender_number', 'sender_whatsapp', 'account_sid', 'auth_token', 'verified']);

        if (!empty($settings['auth_token'])) {

            if ($settings['auth_token'] == '********************') {
                $oldSettings = self::getApiConfig();
                $settings['auth_token'] = $oldSettings['auth_token'];
            }

            $settings['verified'] = false;

            $api = self::getApiClient($settings);

            $checkAuth = $api->authTest();
            
            if (!is_wp_error($checkAuth)) {
                $settings['verified'] = true;
            }

            $settings['auth_token'] = Helper::encryptKey($settings['auth_token']);
        }

        update_option('_fcal_twilio_client_details', $settings, 'no');

        return $settings;
    }

    public static function getApiClient($config = null)
    {
        $config = $config ?? self::getApiConfig();

        $client = new Client($config['account_sid'], $config['auth_token']);

        return $client;
    }

    public static function isConnected()
    {
        $config = self::getApiConfig();
        
        return Arr::isTrue($config, 'verified');
    }

    public static function isConfigured()
    {
        $config = self::getApiConfig();
        
        return !empty($config['account_sid']) && !empty($config['auth_token']);
    }

    public static function getDefaultSmsNotificationSettings()
    {
        $defaultSettings = apply_filters('fluent_booking/default_sms_notification_settings', [
            'booking_conf_attendee' => [
                'enabled' => false,
                'title'   => __('Booking Confirmation SMS to Attendee', 'fluent-booking'),
                'sms'   => [
                    'send_to' => 'phone',
                    'number' => '',
                    'body'   => "Your event has been scheduled."."\r\n"."Event: {{booking.event_name}} with {{host.name}} At {{booking.full_start_end_guest_timezone}}"."\r\n"."Where: {{booking.location_details_html}}"."\r\n"."Additional Notes: {{guest.note}}"
                ],
            ],
            'booking_conf_host'     => [
                'enabled' => false,
                'is_host' => true,
                'title'   => __('Booking Confirmation SMS to Organizer (You)', 'fluent-booking'),
                'sms'   => [
                    'send_to' => 'phone',
                    'receiver'=> 'host_number',
                    'number' => '',
                    'body'   => "An event has been scheduled."."\r\n"."Event: {{booking.event_name}} with {{guest.full_name}} At {{booking.full_start_end_guest_timezone}}"."\r\n"."Where: {{booking.location_details_html}}"."\r\n"."Additional Notes: {{guest.note}}"
                ],
            ],
            'reminder_to_attendee'  => [
                'enabled' => false,
                'title'   => __('Configure Meeting Reminder to Attendee', 'fluent-booking'),
                'sms'   => [
                    'send_to' => 'phone',
                    'number' => '',
                    'body'    => "Reminder: Your meeting will start in {{booking.start_time_human_format}}"."\r\n"."Event: {{booking.event_name}} with {{host.name}}"."\r\n"."At {{booking.full_start_end_guest_timezone}}"."\r\n"."Where: {{booking.location_details_html}}"."\r\n"."Additional Notes: {{guest.note}}",
                    'times'   => [
                        [
                            'unit'  => 'minutes',
                            'value' => 15,
                        ]
                    ]
                ],
            ],
            'reminder_to_host'      => [
                'enabled' => false,
                'is_host' => true,
                'title'   => __('Configure Meeting Reminder to Organizer (You)', 'fluent-booking'),
                'sms'   => [
                    'number' => '',
                    'send_to' => 'phone',
                    'receiver'=> 'host_number',
                    'body'    => "Reminder: Your meeting will start in {{booking.start_time_human_format}}"."\r\n"."Event: {{booking.event_name}} with {{host.name}}"."\r\n"."At {{booking.full_start_end_guest_timezone}}"."\r\n"."Where: {{booking.location_details_html}}"."\r\n"."Additional Notes: {{guest.note}}",
                    'times'  => [
                        [
                            'unit'  => 'minutes',
                            'value' => 15,
                        ]
                    ]
                ],
            ],
            'cancelled_by_attendee' => [
                'enabled' => false,
                'is_host' => true,
                'title'   => __('Booking Cancelled by Attendee (SMS to Organizer)', 'fluent-booking'),
                'sms'   => [
                    'send_to' => 'phone',
                    'receiver'=> 'host_number',
                    'number' => '',
                    'body'   => "Your scheduled meeting has been cancelled."."\r\n"."Event: {{booking.event_name}} with {{guest.full_name}} At {{booking.full_start_end_host_timezone}} (Cancelled)"."\r\n"."Cancellation Reason: {{booking.cancel_reason}}"."\r\n"."Where: {{booking.location_details_html}}"."\r\n"."Additional Notes: {{guest.note}}"
                ],
            ],
            'cancelled_by_host'     => [
                'enabled' => false,
                'title'   => __('Booking Cancelled by Organizer (SMS to Attendee)', 'fluent-booking'),
                'sms'   => [
                    'send_to' => 'phone',
                    'number' => '',
                    'body'   => "Your scheduled meeting has been cancelled."."\r\n"."Event: {{booking.event_name}} with {{guest.full_name}} At {{booking.full_start_end_host_timezone}} (Cancelled)"."\r\n"."Cancellation Reason: {{booking.cancel_reason}}"."\r\n"."Where: {{booking.location_details_html}}"."\r\n"."Additional Notes: {{guest.note}}"
                ],
            ],
            'rescheduled_by_attendee' => [
                'enabled' => false,
                'is_host' => true,
                'title'   => __('Booking Rescheduled by Attendee (email to Organizer)', 'fluent-booking-pro'),
                'sms'   => [
                    'send_to' => 'phone',
                    'receiver'=> 'host_number',
                    'number' => '',
                    'body'   => "A scheduled meeting has been rescheduled."."\r\n"."Event: {{booking.event_name}} with {{guest.full_name}}"."\r\n"."New Time: {{booking.full_start_end_host_timezone}}"."\r\n"."Previous Time: {{booking.previous_meeting_time}}"."\r\n"."Rescheduling Reason: {{booking.reschedule_reason}}"."\r\n"."Where: {{booking.location_details_html}}"."\r\n"."Additional Notes: {{guest.note}}",
                ],
            ],
            'rescheduled_by_host'     => [
                'enabled' => false,
                'title'   => __('Booking Rescheduled by Organizer (email to Attendee)', 'fluent-booking-pro'),
                'sms'   => [
                    'send_to' => 'phone',
                    'number' => '',
                    'body'   => "Your scheduled meeting has been rescheduled."."\r\n"."Event: {{booking.event_name}} with {{guest.full_name}}"."\r\n"."New Time: {{booking.full_start_end_host_timezone}}"."\r\n"."Previous Time: {{booking.previous_meeting_time}}"."\r\n"."Rescheduling Reason: {{booking.reschedule_reason}}"."\r\n"."Where: {{booking.location_details_html}}"."\r\n"."Additional Notes: {{guest.note}}",
                ],
            ]
        ]);

        if (!self::isConnected()) {
            return '';
        }
        
        return $defaultSettings;
    }
}
