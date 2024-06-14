<?php

use FluentBooking\App\Services\Integrations\PaymentMethods\CurrenciesHelper;

/*
 * Remote calendars
 */
(new \FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarsInit())->boot();
(new \FluentBooking\App\Services\Integrations\Twilio\Bootstrap())->register();
(new \FluentBooking\App\Services\Integrations\ZoomMeeting\Bootstrap())->register();
(new \FluentBooking\App\Services\Integrations\Webhook\WebhookIntegration())->register();

// Global Modules Intialization
(new \FluentBooking\App\Services\GlobalModules\GlobalModules())->register();

// Global Notification Handler
(new \FluentBooking\App\Hooks\Handlers\GlobalNotificationHandler())->register();

// payment Methods
(new FluentBooking\App\Hooks\Handlers\GlobalPaymentHandler)->register();

add_action('fluentform/loaded', function () {
    (new \FluentBooking\App\Services\Integrations\FluentForms\FluentFormInit())->init();
});

add_action('fluentcrm_loaded', function () {
    (new \FluentBooking\App\Services\Integrations\FluentCRM\FluentCrmInit());
    (new \FluentBooking\App\Services\Integrations\FluentCRM\Bootstrap());
});

add_action('fluent_boards_loaded', function () {
    (new \FluentBooking\App\Services\Integrations\FluentBoards\Bootstrap());
});

add_filter('fluent_booking/calendar_setting_menu_items', function ($items, $calendar) {
    $items['remote_calendars'] = [
        'type'    => 'route',
        'route'   => [
            'name'   => 'remote_calendars',
            'params' => [
                'calendar_id' => $calendar->id
            ]
        ],
        'label'   => __('Remote Calendars', 'fluent-booking-pro'),
        'svgIcon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-[16px] w-[16px] stroke-[2px] ltr:mr-2 rtl:ml-2 md:mt-0" data-testid="icon-component"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>'
    ];

    return $items;
}, 1, 2);

add_filter('fluent_booking/calendar_setting_menu_items', function ($items, $calendar) {
    if ($calendar->type == 'team') {
        return [
            'calendar_settings' => [
                'type'    => 'route',
                'route'   => [
                    'name'   => 'calendar_settings',
                    'params' => [
                        'calendar_id' => $calendar->id
                    ]
                ],
                'label'   => __('Team Settings', 'fluent-booking-pro'),
                'svgIcon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 12.8799V11.1199C2 10.0799 2.85 9.21994 3.9 9.21994C5.71 9.21994 6.45 7.93994 5.54 6.36994C5.02 5.46994 5.33 4.29994 6.24 3.77994L7.97 2.78994C8.76 2.31994 9.78 2.59994 10.25 3.38994L10.36 3.57994C11.26 5.14994 12.74 5.14994 13.65 3.57994L13.76 3.38994C14.23 2.59994 15.25 2.31994 16.04 2.78994L17.77 3.77994C18.68 4.29994 18.99 5.46994 18.47 6.36994C17.56 7.93994 18.3 9.21994 20.11 9.21994C21.15 9.21994 22.01 10.0699 22.01 11.1199V12.8799C22.01 13.9199 21.16 14.7799 20.11 14.7799C18.3 14.7799 17.56 16.0599 18.47 17.6299C18.99 18.5399 18.68 19.6999 17.77 20.2199L16.04 21.2099C15.25 21.6799 14.23 21.3999 13.76 20.6099L13.65 20.4199C12.75 18.8499 11.27 18.8499 10.36 20.4199L10.25 20.6099C9.78 21.3999 8.76 21.6799 7.97 21.2099L6.24 20.2199C5.33 19.6999 5.02 18.5299 5.54 17.6299C6.45 16.0599 5.71 14.7799 3.9 14.7799C2.85 14.7799 2 13.9199 2 12.8799Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>'
            ]
        ];
    }
    return $items;
}, 20, 2);

add_filter('fluent_booking/calendar_event_setting_menu_items', function ($items, $event) {
    $items['sms_notification'] = [
        'type'    => 'route',
        'route'   => [
            'name'   => 'sms_notification',
            'params' => [
                'calendar_id' => $event->calendar_id,
                'event_id'    => $event->id
            ]
        ],
        'label'   => __('SMS Notification', 'fluent-booking-pro'),
        'elIcon' => 'Notification'
    ];
    $items['advanced_settings'] = [
        'type'    => 'route',
        'route'   => [
            'name'   => 'advanced_settings',
            'params' => [
                'calendar_id' => $event->calendar_id,
                'event_id'    => $event->id
            ]
        ],
        'label'   => __('Advanced Settings', 'fluent-booking-pro'),
        'elIcon' => 'Operation'
    ];
    $items['payment_settings'] = [
        'type'    => 'route',
        'route'   => [
            'name'   => 'payment_settings',
            'params' => [
                'calendar_id' => $event->calendar_id,
                'event_id'    => $event->id
            ]
        ],
        'label'   => __('Payment Settings', 'fluent-booking-pro'),
        'elIcon' => 'Money'
    ];
    $items['webhook_settings'] = [
        'type'    => 'route',
        'route'   => [
            'name'   => 'webhook_settings',
            'params' => [
                'calendar_id' => $event->calendar_id,
                'event_id'    => $event->id
            ]
        ],
        'label'   => __('Webhooks Feeds', 'fluent-booking-pro'),
        'elIcon' => 'Link'
    ];
    $items['integrations'] = [
        'type'    => 'route',
        'route'   => [
            'name'   => 'integrations',
            'params' => [
                'calendar_id' => $event->calendar_id,
                'event_id'    => $event->id
            ]
        ],
        'label'   => __('Integrations', 'fluent-booking-pro'),
        'elIcon' => 'Connection'
    ];
    return $items;
}, 1, 2);

add_filter('fluent_booking/general_settings', function ($settings) {
    $settings['all_currencies'] = CurrenciesHelper::getFormattedCurrencies();
    return $settings;
});

add_filter('fluent_booking/admin_vars', function ($vars) {
    $vars['currency'] = CurrenciesHelper::getGlobalCurrency();
    $vars['currency_sign'] = CurrenciesHelper::getGlobalCurrencySign();
    return $vars;
});

add_filter('fluent_booking/total_payment_widget', function ($widget, $paymentWidget) {
    if (isset($paymentWidget['totalPayment']) && $paymentWidget['totalPayment'] != 0) {
        $currencySign = CurrenciesHelper::getGlobalCurrencySign();
        $widget = [
            'title'   => __('Total Payment', 'fluent-booking-pro'),
            'number'  => $currencySign . $paymentWidget['totalPayment'],
            'content' => $paymentWidget['paymentComparison'],
            'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                <path d="M15.0235 10.5932C13.8049 10.9264 13.0704 11.8615 13.0704 12.7449C13.0704 13.6283 13.8049 14.5634 15.0235 14.8966V10.5932Z" fill="white"/>
                <path d="M16.9766 17.1036V21.407C18.1953 21.0738 18.9298 20.1387 18.9298 19.2553C18.9298 18.3719 18.1953 17.4368 16.9766 17.1036Z" fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M29.0209 16.0001C29.0209 23.1913 23.1913 29.0209 16.0001 29.0209C8.80887 29.0209 2.97925 23.1913 2.97925 16.0001C2.97925 8.80887 8.80887 2.97925 16.0001 2.97925C23.1913 2.97925 29.0209 8.80887 29.0209 16.0001ZM16.0001 7.21102C16.5394 7.21102 16.9766 7.64824 16.9766 8.18758V8.6C19.0996 8.98012 20.8829 10.5751 20.8829 12.7449C20.8829 13.2842 20.4457 13.7214 19.9063 13.7214C19.367 13.7214 18.9298 13.2842 18.9298 12.7449C18.9298 11.8615 18.1953 10.9264 16.9766 10.5932V15.1104C19.0996 15.4905 20.8829 17.0855 20.8829 19.2553C20.8829 21.4251 19.0996 23.02 16.9766 23.4002V23.8126C16.9766 24.3519 16.5394 24.7891 16.0001 24.7891C15.4607 24.7891 15.0235 24.3519 15.0235 23.8126V23.4002C12.9006 23.02 11.1173 21.4251 11.1173 19.2553C11.1173 18.716 11.5545 18.2787 12.0938 18.2787C12.6332 18.2787 13.0704 18.716 13.0704 19.2553C13.0704 20.1387 13.8049 21.0738 15.0235 21.407V16.8898C12.9006 16.5096 11.1173 14.9147 11.1173 12.7449C11.1173 10.5751 12.9006 8.98012 15.0235 8.6V8.18758C15.0235 7.64824 15.4607 7.21102 16.0001 7.21102Z" fill="white"/>
                </svg>',
            'stat'    => $paymentWidget['paymentStat']
        ];
    }
    return $widget;
}, 10, 2);

add_action('init', function () {
// Woo Integration
    if (defined('WC_PLUGIN_FILE')) {
        (new \FluentBooking\App\Services\Integrations\Woo\Bootstrap())->register();
    }
}, 1);
