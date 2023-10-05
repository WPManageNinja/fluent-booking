<?php

/**
 * @var $router FluentBooking\Framework\Http\Router
 */


$router->prefix('calendars')->withPolicy('CalendarPolicy')->group(function ($router) {

    $router->get('/', 'CalendarController@index');
    
    $router->post('/', 'CalendarController@create');
    $router->post('check-slug', 'CalendarController@checkSlug');

    $router->get('/{id}', 'CalendarController@getCalendar')->int('id');
    $router->post('/{id}', 'CalendarController@updateCalendar')->int('id');

    $router->post('/{id}/slots', 'CalendarController@createCalendarSlot')->int('id');
    $router->get('/{id}/slot-schema', 'CalendarController@getSlotSchema')->int('id');

    // Landing Page API
    $router->get('/{id}/sharing-settings', 'CalendarController@getSharingSettings')->int('id');
    $router->post('/{id}/sharing-settings', 'CalendarController@saveSharingSettings')->int('id');

    $router->get('/{id}/slots/{slot_id}', 'CalendarController@getSlot')->int('id')->int('slot_id');
    $router->post('/{id}/slots/{slot_id}', 'CalendarController@updateCalendarSlot')->int('id')->int('slot_id');
    $router->put('/{id}/slots/{slot_id}', 'CalendarController@patchCalendarSlot')->int('id')->int('slot_id');
    $router->delete('/{id}/slots/{slot_id}', 'CalendarController@deleteCalendarSlot')->int('id')->int('slot_id');

    $router->delete('/{id}', 'CalendarController@deleteCalendar')->int('id');

    $router->get('/{id}/slots/{slot_id}/notifications', 'CalendarController@getSlotNotifications')->int('id')->int('slot_id');
    $router->post('/{id}/slots/{slot_id}/notifications', 'CalendarController@saveSlotNotifications')->int('id')->int('slot_id');
});

$router->prefix('admin')->withPolicy('AdminPolicy')->group(function ($router) {
    $router->get('remaining-hosts', 'AdminController@getRemainingHosts');
    $router->get('other-hosts', 'AdminController@getOtherHosts');
});

$router->prefix('schedules')->withPolicy('UserPolicy')->group(function ($router) {
    $router->get('/', 'SchedulesController@index');
    $router->get('/{event_id}', 'SchedulesController@getBooking')->int('event_id');
    $router->get('/{spot_id}/slot', 'SchedulesController@getScheduleSpot')->int('spot_id');
    $router->put('/{booking_id}', 'SchedulesController@patchBooking')->int('booking_id');
    $router->get('/{event_id}/activities', 'SchedulesController@getBookingActivities')->int('event_id');
});

$router->prefix('public')->withPolicy('PublicPolicy')->group(function ($router) {
    $router->get('slots/{slot_id}', 'BookingController@getSlots')->int('slot_id');
    $router->post('slots/{slot_id}/schedule', 'BookingController@bookSlot')->int('slot_id');
    $router->get('public_vars', 'WidgetController@getPublicVars');
});

$router->prefix('integrations')->withPolicy('UserPolicy')->group(function ($router) {
    $router->get('/', 'IntegrationController@index');
    $router->post('/', 'IntegrationController@update');

    // Integration Settings
    $router->get('/{host_id}/settings', 'IntegrationSettingsController@index')->int('host_id');
    $router->post('/{host_id}/settings', 'IntegrationSettingsController@update')->int('host_id');
    $router->post('/{host_id}/disconnect', 'IntegrationSettingsController@revoke')->int('host_id');
    $router->get('/menu', 'IntegrationSettingsController@getIntegrationsMenu');
});

$router->prefix('settings')->withPolicy('UserPolicy')->group(function ($router) {
    $router->get('/', 'SettingsController@index');
});

$router->prefix('availability')->withPolicy('UserPolicy')->group(function ($router) {
    $router->get('/', 'AvailabilityController@index');
    $router->get('/{id}', 'AvailabilityController@getSchedule')->int('id');
    $router->post('/', 'AvailabilityController@createSchedule');
    $router->post('/{schedule_id}', 'AvailabilityController@updateSchedule')->int('schedule_id');
    $router->delete('/{id}', 'AvailabilityController@deleteSchedule')->int('id');
});

$router->prefix('reports')->withPolicy('UserPolicy')->group(function ($router) {
    $router->get('/', 'ReportController@getReports');
    $router->get('/activities', 'ReportController@getActivities');
});
