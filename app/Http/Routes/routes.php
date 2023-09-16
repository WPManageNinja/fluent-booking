<?php

/**
 * @var $router FluentCalendar\Framework\Http\Router
 */

$router->namespace('FluentCalendar\App\Http\Controllers')->group(function($router) {
    require_once __DIR__ . '/api.php';
});
