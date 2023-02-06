<?php

/**
 * @var $router FluentCalendar\Framework\Http\Router
 */

$router->get('/welcome', 'WelcomeController@index');

$router->prefix('calendars')->withPolicy('AdminPolicy')->group(function ($router) {
    $router->get('/', 'CalendarController@index');
    $router->post('/', 'CalendarController@create');
    $router->post('check-slug', 'CalendarController@checkSlug');

    $router->get('/{id}', 'CalendarController@getCalendar')->int('id');

    $router->post('/{id}/slots', 'CalendarController@createCalendarSlot')->int('id');
    $router->get('/{id}/slot-schema', 'CalendarController@getSlotSchema')->int('id');

    $router->get('/{id}/slots/{slot_id}', 'CalendarController@getSlot')->int('id')->int('slot_id');
    $router->post('/{id}/slots/{slot_id}', 'CalendarController@updateCalendarSlot')->int('id')->int('slot_id');
    $router->put('/{id}/slots/{slot_id}', 'CalendarController@patchCalendarSlot')->int('id')->int('slot_id');

});

$router->prefix('schedules')->withPolicy('AdminPolicy')->group(function ($router) {
    $router->get('/', 'SchedulesController@index');

    $router->get('spot/{spot_id}', 'SchedulesController@getSpot')->int('spot_id');
    $router->put('spot/{spot_id}', 'SchedulesController@patchSpot')->int('spot_id');
});

$router->prefix('public')->withPolicy('PublicPolicy')->group(function ($router) {
    $router->get('slots/{slot_id}', 'BookingController@getSlots')->int('slot_id');
    $router->post('slots/{slot_id}/schedule', 'BookingController@bookSlot')->int('slot_id');
});
