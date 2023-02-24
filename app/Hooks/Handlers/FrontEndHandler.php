<?php

namespace FluentCalendar\App\Hooks\Handlers;

use FluentCalendar\App\App;
use FluentCalendar\App\Models\Calendar;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Services\BookingService;
use FluentCalendar\App\Services\DateTimeHelper;

class FrontEndHandler
{
    public function register()
    {
        add_shortcode('fluent_calendar', [$this, 'handleShortcode']);
    }

    public function handleShortcode($atts, $content)
    {
        $atts = shortcode_atts([
            'id'      => 0,
            'slot_id' => ''
        ], $atts);

        if (!$atts['id'] || !$atts['slot_id']) {
            return '';
        }

        $calendar = Calendar::find($atts['id']);
        $slot = CalendarSlot::find($atts['slot_id']);

        if($slot) {
            return ;
        }

        $slot->max_lookup_date =  $slot->getMaxLookUpDate();
        $slot->min_lookup_date = $slot->getMinLookUpDate();

        $formFields = BookingService::getBookingFields($slot);

        if (!$slot || !$calendar) {
            return 'Calendar not found';
        }

        wp_enqueue_script('fluent-calendar-public', App::getInstance('url.assets') . 'public/js/app.js', [], App::getInstance('config')->get('app.version'), true);

        $this->loadGlobalVars();

        $slot->location_settings = (object)[];

        $slot->description = wpautop($slot->description);

        wp_localize_script('fluent-calendar-public', 'fcal_public_vars_' . $calendar->id . '_'.$slot->id, [
            'slot'           => $slot,
            'calendar'       => $calendar,
            'author_profile' => $slot->getAuthorProfile(true),
            'form_fields'    => $formFields
        ]);

        return App::make('view')->make('public.calendar', [
            'slot'     => $slot,
            'calendar' => $calendar
        ]);
    }

    private function loadGlobalVars()
    {
        static $loaded;

        if ($loaded) {
            return;
        }

        $loaded = true;

        $config = App::make('config');
        $ns = $config->get('app.rest_namespace');
        $ver = $config->get('app.rest_version');

        $rest = [
            'base_url'  => esc_url_raw(rest_url()),
            'url'       => rest_url($ns . '/' . $ver).'/public',
            'nonce'     => wp_create_nonce('wp_rest'),
            'namespace' => $ns,
            'version'   => $ver
        ];

        $currentPerson = [
            'name' => '',
            'email' => ''
        ];

        if(is_user_logged_in()) {
            $currentUser = wp_get_current_user();
            $name = trim($currentUser->first_name . ' ' . $currentUser->last_name);
            $currentPerson = [
                'name' => $name ? $name : $currentUser->display_name,
                'email' => $currentUser->user_email,
                'user_id' => $currentUser->ID
            ];
        }

        wp_localize_script('fluent-calendar-public', 'fluentCalendarPublicVars', [
            'rest' => $rest,
            'timezones' => DateTimeHelper::getFlatGroupedTimeZones(),
            'current_person' => $currentPerson
        ]);
    }

}
