<?php

namespace FluentBooking\App\Services\Integrations\Twilio;

use FluentBooking\App\Services\Integrations\Twilio\TwilioHelper;
use FluentBooking\App\Services\EditorShortCodeParser;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\App;

class Bootstrap
{
    public function register()
    {
        add_filter('fluent_booking/settings_menu_items', [$this, 'addGlobalMenu'], 12, 1);
        add_filter('fluent_booking/get_client_settings_twilio', [$this, 'getOauthClientSettings']);
        add_filter('fluent_booking/get_client_field_settings_twilio', [$this, 'getOauthClientSettingsFields']);
        add_action('fluent_booking/save_client_settings_twilio', [$this, 'saveOauthClientSettings'], 10, 1);

        add_action('fluent_booking/after_booking_scheduled', [$this, 'pushBookingScheduledToQueue'], 10, 2);
        add_action('fluent_booking/after_booking_scheduled_sms_async', [$this, 'bookingScheduledSms'], 10, 1);
        add_action('fluent_booking/booking_schedule_reminder_sms', [$this, 'bookingReminderSms'], 10, 2);
        add_action('fluent_booking/booking_schedule_cancelled', [$this, 'smsOnBookingCancelled'], 10, 1);
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

    public function saveOauthClientSettings($settings)
    {
        TwilioHelper::updateApiConfig($settings);
    }

    public function getOauthClientSettings($settings)
    {
        $config = TwilioHelper::getApiConfig();

        $config['auth_token'] = '********************';

        return $config;
    }

    public function getOauthClientSettingsFields($items)
    {
        $app = App::getInstance();

        $fields = [
            'sender_number' => [
                'type'        => 'text',
                'label'       => __('Number From', 'fluent-booking'),
                'placeholder' => __('Enter Your Twilio Sender Number', 'fluent-booking'),
            ],
            'account_sid' => [
                'type'        => 'text',
                'label'       => __('Account SID', 'fluent-booking'),
                'placeholder' => __('Enter Twilio Account SID', 'fluent-booking'),
            ],
            'auth_token'  => [
                'type'        => 'text',
                'label'       => __('Auth Token', 'fluent-booking'),
                'placeholder' => __('Enter Twilio API Auth Token', 'fluent-booking'),
            ],
        ];

        $description = '<p>Please read the step-by-step documentation to setup Account SID and Auth Token and get the Sender Number for your app. <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/twilio-integration-with-fluentbooking/">Go to the documentation article</a></p>';

        return [
            'logo'          => $app['url.assets'] . 'images/twilio.svg',
            'title'         => __('Twilio SMS Integration', 'fluent-booking'),
            'subtitle'      => __('Configure Twilio API to send SMS notifications on booking events', 'fluent-booking'),
            'description'   => $description,
            'save_btn_text' => __('Save Settings', 'fluent-booking'),
            'fields'        => $fields,
            'will_encrypt'  => true
        ];
    }

    public function sendSmsNotification($booking, $data)
    {
        $message = Arr::get($data, 'message');
        $receiverNumber = Arr::get($data, 'receiver_number');

        if (!$message || !$receiverNumber) {
            return;
        }

        $config = TwilioHelper::getApiConfig();
        
        $message = str_replace('<br />', "\n", $message);
        $message = preg_replace('/\h+/', ' ', sanitize_textarea_field($message));

        $body = [
            'Body' => trim($message),
            'From' => $config['sender_number'],
            'To'   => $receiverNumber
        ];

        $body = apply_filters('fluent_booking/before_send_integration_data_twilio', $body, $booking);

        $api = TwilioHelper::getApiClient();

        $response = $api->sendSMS($config['account_sid'], $body);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => 'Twilio API Error',
                'description' => __('Failed to send sms with Twilio API', 'fluent-booking')
            ]);
            return false;
        }
    }

    private function getReminderTime($time)
    {
        $timestamp = $time['value'] * 60;

        if ($time['unit'] == 'hours') {
            $timestamp = $timestamp * 60;
        } elseif ($time['unit'] == 'days') {
            $timestamp = $timestamp * 60 * 24;
        }

        return $timestamp;
    }

    private function pushRemindersToQueue($booking, $reminderTimes, $emailTo)
    {
        foreach ($reminderTimes as $time) {
            $reminderTimestamp = $this->getReminderTime($time);

            $happeningTimestamp = strtotime($booking->start_time);
            $startingTo = $happeningTimestamp - time();

            $bufferTime = 2 * 60; // 2 Minute Buffer Time
            if ($startingTo > ($reminderTimestamp + $bufferTime)) {
                as_schedule_single_action(($happeningTimestamp - $reminderTimestamp), 'fluent_booking/booking_schedule_reminder_sms', [
                    $booking->id,
                    $emailTo
                ], 'fluent-booking');
            }
        }
    }

    public function pushBookingScheduledToQueue($booking, $bookingEvent)
    {
        if (!TwilioHelper::isConfigured()) {
            return;
        }
        
        $notifications = $bookingEvent->getSmsNotifications();

        if (Arr::isTrue($notifications, 'booking_conf_attendee.enabled') || (Arr::isTrue($notifications, 'booking_conf_host.enabled'))) {
            as_enqueue_async_action('fluent_booking/after_booking_scheduled_sms_async', [
                $booking->id
            ], 'fluent-booking');
        }

        if (Arr::isTrue($notifications, 'reminder_to_attendee.enabled')) {
            $reminderTimes = Arr::get($notifications, 'reminder_to_attendee.sms.times', []);
            $this->pushRemindersToQueue($booking, $reminderTimes, 'guest');
        }

        if (Arr::isTrue($notifications, 'reminder_to_host.enabled')) {
            $reminderTimes = Arr::get($notifications, 'reminder_to_host.sms.times', []);
            $this->pushRemindersToQueue($booking, $reminderTimes, 'host');
        }

    }

    public function bookingScheduledSms($bookingId)
    {
        $booking = Booking::with(['calendar', 'calendar_event'])->find($bookingId);

        if (!$booking || !$booking->calendar_event) {
            return '';
        }

        $notifications = $booking->calendar_event->getSmsNotifications();

        if (Arr::isTrue($notifications, 'booking_conf_attendee.enabled')) {
            $sms = Arr::get($notifications, 'booking_conf_attendee.sms', []);

            $smsData['receiver_number'] = EditorShortCodeParser::parse($sms['number'], $booking);
            $smsData['message'] = EditorShortCodeParser::parse($sms['body'], $booking);

            $this->sendSmsNotification($booking, $smsData);
        }

        if (Arr::isTrue($notifications, 'booking_conf_host.enabled')) {
            $sms = Arr::get($notifications, 'booking_conf_host.sms', []);

            $smsData['receiver_number'] = EditorShortCodeParser::parse($sms['number'], $booking);
            $smsData['message'] = EditorShortCodeParser::parse($sms['body'], $booking);

            $this->sendSmsNotification($booking, $smsData);
        }

        return true;
    }

    public function bookingReminderSms($bookingId, $emailTo)
    {
        $booking = Booking::with(['calendar_event', 'calendar'])->find($bookingId);

        if (!$booking || $booking->status != 'scheduled') {
            return false;
        }

        $notifications = $booking->getSmsNotifications();

        if (!$notifications) {
            return;
        }

        if ('guest' == $emailTo && Arr::isTrue($notifications, 'reminder_to_attendee.enabled')) {
            $sms = Arr::get($notifications, 'reminder_to_attendee.sms', []);

            $smsData['receiver_number'] = EditorShortCodeParser::parse($sms['number'], $booking);
            $smsData['message'] = EditorShortCodeParser::parse($sms['body'], $booking);

            $this->sendSmsNotification($booking, $smsData);

        } elseif ('host' == $emailTo && Arr::isTrue($notifications, 'reminder_to_host.enabled')) {
            $sms = Arr::get($notifications, 'reminder_to_host.sms', []);

            $smsData['receiver_number'] = EditorShortCodeParser::parse($sms['number'], $booking);
            $smsData['message'] = EditorShortCodeParser::parse($sms['body'], $booking);

            $this->sendSmsNotification($booking, $smsData);
        }

    }

    public function smsOnBookingCancelled(Booking $booking)
    {
        if (!TwilioHelper::isConfigured()) {
            return;
        }

        $calendarEvent = $booking->calendar_event;
        if (!$calendarEvent) {
            return;
        }

        $notifications = $calendarEvent->getSmsNotifications();
        if (!$notifications) {
            return;
        }

        $cancelledBy = $booking->getMeta('cancelled_by_type', 'host');

        if ($cancelledBy == 'host') {
            if (Arr::isTrue($notifications, 'cancelled_by_host.enabled')) {
                // This from the host
                $sms = Arr::get($notifications, 'cancelled_by_host.sms', []);
    
                $smsData['receiver_number'] = EditorShortCodeParser::parse($sms['number'], $booking);
                $smsData['message'] = EditorShortCodeParser::parse($sms['body'], $booking);
    
                $this->sendSmsNotification($booking, $smsData);
            }
            return;
        }

        if (Arr::isTrue($notifications, 'cancelled_by_attendee.enabled')) {
            $sms = Arr::get($notifications, 'cancelled_by_attendee.sms', []);

            $smsData['receiver_number'] = EditorShortCodeParser::parse($sms['number'], $booking);
            $smsData['message'] = EditorShortCodeParser::parse($sms['body'], $booking);

            $this->sendSmsNotification($booking, $smsData);
        }
    }
}
