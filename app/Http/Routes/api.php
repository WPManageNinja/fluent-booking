<?php

/**
 * @var $router FluentBooking\Framework\Http\Router
 */

$router->prefix('calendars')->withPolicy('CalendarPolicy')->group(function ($router) {

    $router->get('/', 'CalendarController@getAllCalendars')->meta('calendar_type', 'booking');

    $router->post('/', 'CalendarController@createCalendar');
    $router->post('check-slug', 'CalendarController@checkSlug');

    $router->get('/{id}', 'CalendarController@getCalendar')->int('id');
    $router->post('/{id}', 'CalendarController@updateCalendar')->int('id');
    $router->delete('/{id}', 'CalendarController@deleteCalendar')->int('id');

    $router->post('/{id}/slots', 'CalendarController@createCalendarSlot')->int('id');
    $router->get('/{id}/slot-schema', 'CalendarController@getSlotSchema')->int('id');

    $router->get('/calendars-slots', 'CalendarController@getCalendarsSlots')->int('id');

    // Landing Page API
    $router->get('/{id}/sharing-settings', 'CalendarController@getSharingSettings')->int('id');
    $router->post('/{id}/sharing-settings', 'CalendarController@saveSharingSettings')->int('id');

    // General Integrations
    $router->get('/{id}/integrations/general_integration_feed', 'IntegrationSettingsController@getGeneralIntegrationFeed')->int('id');
    $router->post('/{id}/integrations/general_integration_feed/disconnect', 'IntegrationSettingsController@disconnectGeneralIntegrationFeed')->int('id');

    $router->get('/{id}/slots/{event_id}', 'CalendarController@getSlot')->int('id')->int('event_id');
    $router->post('/{id}/slots/{event_id}', 'CalendarController@updateCalendarSlot')->int('id')->int('event_id');
    $router->put('/{id}/slots/{event_id}', 'CalendarController@patchCalendarSlot')->int('id')->int('event_id');
    $router->delete('/{id}/slots/{event_id}', 'CalendarController@deleteCalendarEvent')->int('id')->int('event_id');

    $router->get('/{id}/slots/{event_id}/email-notifications', 'CalendarController@getSlotEmailNotifications')->int('id')->int('event_id');
    $router->post('/{id}/slots/{event_id}/email-notifications', 'CalendarController@saveSlotEmailNotifications')->int('id')->int('event_id');

    $router->get('/{id}/slots/{event_id}/booking-fields', 'CalendarController@getSlotBookingFields')->int('id')->int('event_id');
    $router->post('/{id}/slots/{event_id}/booking-fields', 'CalendarController@saveSlotBookingFields')->int('id')->int('event_id');
});

$router->prefix('admin')->withPolicy('AdminPolicy')->group(function ($router) {
    $router->get('remaining-hosts', 'AdminController@getRemainingHosts');
    $router->get('other-hosts', 'AdminController@getOtherHosts');
});

$router->prefix('schedules')->withPolicy('MeetingPolicy')->group(function ($router) {
    $router->get('/', 'SchedulesController@index'); // Need to check permission on the controller method
    $router->get('/{id}', 'SchedulesController@getBooking')->int('id');
    $router->get('/{id}/slot', 'SchedulesController@getScheduleSpot')->int('id');
    $router->put('/{id}', 'SchedulesController@patchBooking')->int('id');
    $router->get('/{id}/activities', 'SchedulesController@getBookingActivities')->int('id');

    $router->get('/group-bookings/{group_id}/attendees', 'SchedulesController@getGroupAttendees')->int('group_id');

    // Get FluentCrm Profile
    $router->get('/crm-profile/', 'SchedulesController@getCrmProfile');
});

$router->prefix('integrations')->withPolicy('SettingsPolicy')->group(function ($router) {
    $router->get('/', 'IntegrationController@index');
    $router->post('/', 'IntegrationController@update');
});

$router->prefix('settings')->withPolicy('SettingsPolicy')->group(function ($router) {
    $router->get('/general', 'SettingsController@getGeneralSettings');
    $router->post('/general', 'SettingsController@updateGeneralSettings');
    $router->get('/menu', 'SettingsController@getSettingsMenu');
});

$router->prefix('availability')->withPolicy('AvailabilityPolicy')->group(function ($router) {
    $router->get('/', 'AvailabilityController@index');
    $router->post('/', 'AvailabilityController@createSchedule');
    $router->post('/clone', 'AvailabilityController@cloneSchedule');

    $router->get('/{schedule_id}', 'AvailabilityController@getSchedule')->int('schedule_id');
    $router->get('/{schedule_id}/usages', 'AvailabilityController@getAvailabilityUsages')->int('schedule_id');
    $router->post('/{schedule_id}', 'AvailabilityController@updateSchedule')->int('schedule_id');
    $router->post('/{schedule_id}/update-title', 'AvailabilityController@updateScheduleTitle')->int('schedule_id');
    $router->post('/{schedule_id}/update-status', 'AvailabilityController@updateDefaultStatus')->int('schedule_id');
    $router->delete('/{schedule_id}', 'AvailabilityController@deleteSchedule')->int('schedule_id');

});

$router->prefix('reports')->withPolicy('UserPolicy')->group(function ($router) {
    $router->get('/', 'ReportController@getReports');
    $router->get('/graph-reports', 'ReportController@getGraphReports');
    $router->get('/activities', 'ReportController@getActivities');
});

if (defined('FLUENT_BOOKING_PRO_DIR_FILE')) {
    require_once __DIR__ . '/pro_routes.php';
}
