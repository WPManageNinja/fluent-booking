<?php

namespace FluentBooking\Framework\Foundation;


class App
{
    /**
     * Application instance
     * @var FluentBooking\Framework\Foundation\Application
     */
    protected static $instance = null;

    /**
     * Set the application instance
     * @param FluentBooking\Framework\Foundation\Application $app
     */
    public static function setInstance($app)
    {
        static::$instance = $app;
    }

    /**
     * Get the application instance
     * @param  string $module The binding/key name for a component.
     * @return FluentBooking\Framework\Foundation\Application|mixed
     */
    public static function getInstance($module = null)
    {
        if ($module) {
            return static::$instance[$module];
        }

        return static::$instance;
    }

    /**
     * Retrive a component from the container
     * @param  string $module The binding/key name for a component.
     * @return FluentBooking\Framework\Foundation\Application|mixed
     */
    public static function make($module = null)
    {
        return static::getInstance($module);
    }

    /**
     * Handle dynamic method calls
     * @param  string $method
     * @param  array $params
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        return static::getInstance($method);
    }
}
