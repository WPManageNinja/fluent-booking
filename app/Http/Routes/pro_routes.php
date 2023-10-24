<?php

/**
 * @var $router FluentBooking\Framework\Http\Router
 */

$router->prefix('calendars')->withPolicy('CalendarPolicy')->group(function ($router) {
    // Integrations
    $router->get('/{id}/integrations/remote-calendars', 'IntegrationSettingsController@getRemoteCalendars')->int('id');
    $router->post('/{id}/integrations/remote-calendars/patch-conflicts', 'IntegrationSettingsController@patchRemoteCalendarConflictSettings')->int('id');
    $router->post('/{id}/integrations/remote-calendars/sync-settings', 'IntegrationSettingsController@syncCreatbleRemoteCalSettings')->int('id');
    $router->post('/{id}/integrations/remote-calendars/disconnect-calendar', 'IntegrationSettingsController@disconnectRemoteCalendar')->int('id');

    // Zoom Integrations - User Level
    $router->get('/{id}/integrations/zoom-connection', 'ZoomController@getZoomConnectionByCalendarId')->int('id');
    $router->post('/{id}/integrations/zoom-connection/add', 'ZoomController@addConnectionByCalendarId')->int('id');
    $router->post('/{id}/integrations/zoom-connection/disconnect', 'ZoomController@disconnectByCalendarId')->int('id');

    // webhooks
    $router->get('/{id}/slots/{event_id}/webhooks', 'WebhookController@getFeeds')->int('id')->int('event_id');
    $router->post('/{id}/slots/{event_id}/webhooks', 'WebhookController@saveFeed')->int('id')->int('event_id');
    $router->delete('/{id}/slots/{event_id}/webhooks/{webhook_id}', 'WebhookController@deleteFeed')->int('id')->int('event_id')->int('webhook_id');

    // Payment settings route
    $router->get('/{id}/slots/{event_id}/payment-settings', 'PaymentMethodController@getCalendarEventSettings')->int('id')->int('event_id');
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

$router->prefix('settings')->withPolicy('SettingsPolicy')->group(function ($router) {
    /*
     * Team Management Permissions
     */
    $router->get('/team', 'AdminController@getTeamMembers');
    $router->post('/team', 'AdminController@updateMemberPermission');
    $router->delete('/team/{id}', 'AdminController@deleteMember')->int('id');

    $router->get('license', 'LicenseController@getStatus');
    $router->post('license', 'LicenseController@saveLicense');
    $router->delete('license', 'LicenseController@deactivateLicense');
});

$router->prefix('integrations')->withPolicy('SettingsPolicy')->group(function ($router) {
    // Integration Settings
    $router->get('/{host_id}/settings', 'IntegrationSettingsController@index')->int('host_id');
    $router->post('/{host_id}/settings', 'IntegrationSettingsController@update')->int('host_id');
    $router->post('/{host_id}/disconnect', 'IntegrationSettingsController@revoke')->int('host_id');
    $router->get('/menu', 'IntegrationSettingsController@getIntegrationsMenu');

    /*
     * Zoom Integrations
     */
    $router->get('zoom/connected-users', 'ZoomController@get');
    $router->post('zoom/save-user-account', 'ZoomController@save');
    $router->post('zoom/disconnect', 'ZoomController@disconnectByConnectId');

    $router->prefix('settings/payment-methods')->group(function ($router) {
        $router->get('/all', 'PaymentMethodController@index');

        $router->post('/', 'PaymentMethodController@store');
        $router->get('/', 'PaymentMethodController@getSettings');

        $router->get('connect/info', 'PaymentMethodController@connectInfo');
        $router->post('disconnect', 'PaymentMethodController@disconnect');

        $router->get('currencies', 'PaymentMethodController@currencies');
    });
});
