<?php

add_action('fluentform/loaded', function () {
    (new \FluentBooking\App\Services\Integrations\FluentForms\FluentFormInit())->init();
});

add_action('fluentcrm_loaded', function () {
    (new \FluentBooking\App\Services\Integrations\FluentCRM\FluentCrmInit());
    (new \FluentBooking\App\Services\Integrations\FluentCRM\Bootstrap());
});

/*
 * Remote calendars
 */
(new \FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarsInit())->boot();
(new \FluentBooking\App\Services\Integrations\Twilio\Bootstrap())->register();
(new \FluentBooking\App\Services\Integrations\ZoomMeeting\Bootstrap())->register();
(new \FluentBooking\App\Services\Integrations\Webhook\WebhookIntegration())->register();

// payment Methods
(new FluentBooking\App\Hooks\Handlers\GlobalPaymentHandler)->register();

add_filter('fluent_booking/calendar_setting_menu_items', function ($items, $calendar) {
    $items['remote_calendars'] = [
        'type'    => 'route',
        'route'   => [
            'name'   => 'remote_calendars',
            'params' => [
                'id' => $calendar->id
            ]
        ],
        'label'   => __('Remote Calendars', 'fluent-booking-pro'),
        'svgIcon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-[16px] w-[16px] stroke-[2px] ltr:mr-2 rtl:ml-2 md:mt-0" data-testid="icon-component"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>'
    ];

    return $items;
}, 1, 2);


add_action('init', function () {
// Woo Integration
    if (defined('WC_PLUGIN_FILE')) {
      //  (new \FluentBooking\App\Services\Integrations\Woo\Bootstrap())->register();
    }
}, 1);
