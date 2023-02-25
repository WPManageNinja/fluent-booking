<?php

(new \FluentCalendar\App\Saas\Hooks\Handlers\SaasHandler)->register();

add_filter('fluent_calendar/admin_base_url', function($url) {
    return site_url('calendar/#/');
});

add_filter('fluent_calendar/verify_calendar_api', function ($can, $request) {
    if($can) {
        return $can; // it's and super admin
    }

    $userId = get_current_user_id();
    if(!$userId) {
        return false;
    }

    $calendarId = $request->get('id');

    if(!$calendarId) {
        return true;
    }

    $calendar = \FluentCalendar\App\Models\Calendar::findOrFail($calendarId);
    return $calendar->user_id == $userId;

}, 10, 2);

add_action('fluent_calendar/before_patch_schedule', function ($spot) {
    if($spot->calendar->user_id != get_current_user_id()) {
        throw new \Exception('You are not allowed to edit this schedule');
    }
});

add_action('fluent_calendar/before_create_calendar', function ($data) {
    $exist = \FluentCalendar\App\Models\Calendar::where('user_id', get_current_user_id())->first();
    if($exist) {
        throw new \Exception('You already have a calendar. You can not create more than one calendar');
    }
});

