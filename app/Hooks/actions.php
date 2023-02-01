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

(new \FluentCalendar\App\Hooks\Handlers\FrontEndHandler())->register();

$app->addAction('admin_menu', 'AdminMenuHandler@add');

/**
 * Enable this line if you want to use custom post types
 */

// $app->addAction('init', 'CPTHandler@registerPostTypes');


if(isset($_GET['cal'])) {
    add_action('init', function () {
        $booking = \FluentCalendar\App\Models\Booking::with('users')->findOrFail(10);
        dd($booking);
    });
}
