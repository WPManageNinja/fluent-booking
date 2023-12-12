<?php

/**
 * All registered filter's handlers should be in app\Hooks\Handlers,
 * addFilter is similar to add_filter and addCustomFlter is just a
 * wrapper over add_filter which will add a prefix to the hook name
 * using the plugin slug to make it unique in all wordpress plugins,
 * ex: $app->addCustomFilter('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_filter('slug-foo', ['FooHandler', 'handleFoo']).
 */

/**
 * @var $app FluentBooking\Framework\Foundation\Application
 */

 $app->addFilter('fluent_booking/get_calendar_event_settings', function($settings) {
    if (!isset($settings['buffer_time_before'], $settings['buffer_time_after'])) {
        $settings['buffer_time_before'] = '0';
        $settings['buffer_time_after'] = '0';
    }

    if (!isset($settings['slot_interval'])) {
        $settings['slot_interval'] = '';
    }

    if (!isset($settings['booking_frequency'], $settings['booking_duration'])) {
        $settings['booking_frequency'] = [
            'enabled' => false,
            'limits'  => [
                ['unit'  => 'per_day', 'value' => 5]
            ]
        ];
        $settings['booking_duration'] = [
            'enabled' => false,
            'limits'  => [
                ['unit'  => 'per_day', 'value' => 120]
            ]
        ];
    }

    if (!isset($settings['can_cancel'], $settings['can_reschedule'])) {
        $settings['can_cancel'] = 'yes';
        $settings['can_reschedule'] = 'yes';
    }

    if (!isset($settings['custom_redirect'])) {
        $settings['custom_redirect'] = [
            'enabled'         => false,
            'redirect_url'    => '',
            'is_query_string' => 'no',
            'query_string'    => ''
        ];
    }

    if (!isset($settings['multi_duration'])) {
        $settings['multi_duration'] = [
            'enabled'             => false,
            'default_duration'    => '',
            'available_durations' => []
        ];
    }
    return $settings;
}, 10, 1);