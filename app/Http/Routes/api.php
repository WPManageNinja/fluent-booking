<?php

/**
 * @var $router FluentCalendar\Framework\Http\Router
 */


$router->prefix('calendars')->withPolicy('CalendarPolicy')->group(function ($router) {

    $router->get('/', 'CalendarController@index');
    
    $router->post('/', 'CalendarController@create');
    $router->post('check-slug', 'CalendarController@checkSlug');

    $router->get('/{id}', 'CalendarController@getCalendar')->int('id');
    $router->post('/{id}', 'CalendarController@updateCalendar')->int('id');

    $router->post('/{id}/slots', 'CalendarController@createCalendarSlot')->int('id');
    $router->get('/{id}/slot-schema', 'CalendarController@getSlotSchema')->int('id');

    $router->get('/{id}/slots/{slot_id}', 'CalendarController@getSlot')->int('id')->int('slot_id');
    $router->post('/{id}/slots/{slot_id}', 'CalendarController@updateCalendarSlot')->int('id')->int('slot_id');
    $router->put('/{id}/slots/{slot_id}', 'CalendarController@patchCalendarSlot')->int('id')->int('slot_id');
    $router->delete('/{id}/slots/{slot_id}', 'CalendarController@deleteCalendarSlot')->int('id')->int('slot_id');

    $router->get('/{id}/slots/{slot_id}/notifications', 'CalendarController@getSlotNotifications')->int('id')->int('slot_id');
    $router->post('/{id}/slots/{slot_id}/notifications', 'CalendarController@saveSlotNotifications')->int('id')->int('slot_id');

});

$router->prefix('schedules')->withPolicy('UserPolicy')->group(function ($router) {

    $router->get('/', 'SchedulesController@index');
    $router->get('/{booking_id}', 'SchedulesController@getBooking')->int('booking_id');
    $router->put('/{booking_id}', 'SchedulesController@patchBooking')->int('booking_id');
    $router->get('/{booking_id}/activities', 'SchedulesController@getBookingActivities')->int('booking_id');

});

$router->prefix('public')->withPolicy('PublicPolicy')->group(function ($router) {
    $router->get('slots/{slot_id}', 'BookingController@getSlots')->int('slot_id');
    $router->post('slots/{slot_id}/schedule', 'BookingController@bookSlot')->int('slot_id');
    $router->get('public_vars', 'WidgetController@getPublicVars');
});
