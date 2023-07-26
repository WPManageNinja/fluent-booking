<?php

/**
 * All registered action's handlers should be in app\Hooks\Handlers,
 * addAction is similar to add_action and addCustomAction is just a
 * wrapper over add_action which will add a prefix to the hook name
 * using the plugin slug to make it unique in all wordpress plugins,
 * ex: $app->addCustomAction('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_action('slug-foo', ['FooHandler', 'handleFoo']).
 */

/**
 * @var $app FluentCalendar\Framework\Foundation\Application
 */

/*
 * Register all the grouped action handlers
 */
(new \FluentCalendar\App\Hooks\Handlers\FrontEndHandler())->register();
(new \FluentCalendar\App\Hooks\Handlers\CleanupHandler())->register();
(new \FluentCalendar\App\Hooks\Handlers\NotificationHandler())->register();
(new \FluentCalendar\App\Hooks\Handlers\LogHandler())->register();


$app->addAction('admin_menu', 'AdminMenuHandler@add');


// Load Integrations
require_once FLUENT_CALENDAR_DIR . 'app/Services/Integrations/index.php';
