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

    $router->get('/calendars-slots', 'CalendarController@getCalendarsSlots')->int('id');

    // Landing Page API
    $router->get('/{id}/sharing-settings', 'CalendarController@getSharingSettings')->int('id');
    $router->post('/{id}/sharing-settings', 'CalendarController@saveSharingSettings')->int('id');

    // Integrations
    $router->get('/{id}/integrations/remote-calendars', 'IntegrationSettingsController@getRemoteCalendars')->int('id');
    $router->post('/{id}/integrations/remote-calendars/patch-conflicts', 'IntegrationSettingsController@patchRemoteCalendarConflictSettings')->int('id');
    $router->post('/{id}/integrations/remote-calendars/sync-settings', 'IntegrationSettingsController@syncCreatbleRemoteCalSettings')->int('id');
    $router->post('/{id}/integrations/remote-calendars/disconnect-calendar', 'IntegrationSettingsController@disconnectRemoteCalendar')->int('id');



    // General Integrations
    $router->get('/{id}/integrations/general_integration_feed', 'IntegrationSettingsController@getGeneralIntegrationFeed')->int('id');
    $router->post('/{id}/integrations/general_integration_feed/disconnect', 'IntegrationSettingsController@disconnectGeneralIntegrationFeed')->int('id');

    $router->get('/{id}/slots/{event_id}', 'CalendarController@getSlot')->int('id')->int('event_id');
    $router->post('/{id}/slots/{event_id}', 'CalendarController@updateCalendarSlot')->int('id')->int('event_id');
    $router->put('/{id}/slots/{event_id}', 'CalendarController@patchCalendarSlot')->int('id')->int('event_id');
    $router->delete('/{id}/slots/{event_id}', 'CalendarController@deleteCalendarSlot')->int('id')->int('event_id');

    $router->delete('/{id}', 'CalendarController@deleteCalendar')->int('id');

    $router->get('/{id}/slots/{event_id}/notifications', 'CalendarController@getSlotNotifications')->int('id')->int('event_id');
    $router->post('/{id}/slots/{event_id}/notifications', 'CalendarController@saveSlotNotifications')->int('id')->int('event_id');

    $router->get('/{id}/slots/{event_id}/booking-fields', 'CalendarController@getSlotBookingFields')->int('id')->int('event_id');
    $router->post('/{id}/slots/{event_id}/booking-fields', 'CalendarController@saveSlotBookingFields')->int('id')->int('event_id');

    // webhooks
    $router->get('/{id}/slots/{event_id}/webhooks', 'WebhookController@getFeeds')->int('id')->int('event_id');
    $router->post('/{id}/slots/{event_id}/webhooks', 'WebhookController@saveFeed')->int('id')->int('event_id');
    $router->delete('/{id}/slots/{event_id}/webhooks/{webhook_id}', 'WebhookController@deleteFeed')->int('id')->int('event_id')->int('webhook_id');

    // Payment settings route
    $router->get('/{id}/slots/{event_id}/payment-settings', 'PaymentMethodController@getCalendarSettings')->int('id')->int('event_id');
    $router->post('/{id}/slots/{event_id}/payment-settings', 'PaymentMethodController@updateSettings')->int('id')->int('event_id');

    /*
    * Calendar Integrations
    */
    $router->prefix('{id}/slots/{slot_id}/integrations')->group(function ($router) {
        $router->get('/', 'CalendarIntegrationController@index')->int('id')->int('slot_id');

        $router->prefix('{integration_id}')->group(function ($router) {
            $router->get('/', 'CalendarIntegrationController@find')->int('id')->int('slot_id')->int('integration_id');
            $router->post('/', 'CalendarIntegrationController@update')->int('id')->int('slot_id')->int('integration_id');
            $router->delete('/', 'CalendarIntegrationController@delete')->int('id')->int('slot_id')->int('integration_id');
            
            $router->get('/merge-fields', 'CalendarIntegrationController@integrationListComponent');
        });
    });
});

$router->prefix('admin')->withPolicy('AdminPolicy')->group(function ($router) {
    $router->get('remaining-hosts', 'AdminController@getRemainingHosts');
    $router->get('other-hosts', 'AdminController@getOtherHosts');
});

$router->prefix('schedules')->withPolicy('UserPolicy')->group(function ($router) {
    $router->get('/', 'SchedulesController@index');
    $router->get('/{id}', 'SchedulesController@getBooking')->int('id');
    $router->get('/{id}/slot', 'SchedulesController@getScheduleSpot')->int('id');
    $router->put('/{id}', 'SchedulesController@patchBooking')->int('id');
    $router->get('/{id}/activities', 'SchedulesController@getBookingActivities')->int('id');


    $router->get('/group-bookings/{group_id}/attendees', 'SchedulesController@getGroupAttendees')->int('group_id');

    // Get FluentCrm Profile
    $router->get('/crm-profile/', 'SchedulesController@getCrmProfile');
});

$router->prefix('public')->withPolicy('PublicPolicy')->group(function ($router) {
    $router->get('slots/{event_id}', 'BookingController@getSlots')->int('event_id');
    $router->post('slots/{event_id}/schedule', 'BookingController@bookSlot')->int('event_id');
    $router->get('public_vars', 'WidgetController@getPublicVars');
});

$router->prefix('integrations')->withPolicy('AdminPolicy')->group(function ($router) {
    $router->get('/', 'IntegrationController@index');
    $router->post('/', 'IntegrationController@update');

    // Integration Settings
    $router->get('/{host_id}/settings', 'IntegrationSettingsController@index')->int('host_id');
    $router->post('/{host_id}/settings', 'IntegrationSettingsController@update')->int('host_id');
    $router->post('/{host_id}/disconnect', 'IntegrationSettingsController@revoke')->int('host_id');
    $router->get('/menu', 'IntegrationSettingsController@getIntegrationsMenu');

    $router->prefix('settings/payment-methods')->group(function ($router) {
        $router->get('/all', 'PaymentMethodController@index');

        $router->post('/', 'PaymentMethodController@store');
        $router->get('/', 'PaymentMethodController@getSettings');

        $router->get('connect/info', 'PaymentMethodController@connectInfo');
        $router->post('disconnect', 'PaymentMethodController@disconnect');

        $router->get('currencies', 'PaymentMethodController@currencies');
    });
});

$router->prefix('settings')->withPolicy('UserPolicy')->group(function ($router) {
    $router->get('/general', 'SettingsController@getGeneralSettings');
    $router->post('/general', 'SettingsController@updateGeneralSettings');
    $router->get('/menu', 'SettingsController@getSettingsMenu');
});

$router->prefix('availability')->withPolicy('UserPolicy')->group(function ($router) {
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
    $router->get('/activities', 'ReportController@getActivities');
});
