<?php

namespace FluentBooking\Framework\Validator;

use BadMethodCallException;
use FluentBooking\Framework\Support\Str;
use FluentBooking\Framework\Foundation\App;
use FluentBooking\Framework\Validator\Rules\In;
use FluentBooking\Framework\Validator\Rules\NotIn;
use FluentBooking\Framework\Validator\Rules\Unique;
use FluentBooking\Framework\Validator\Rules\Exists;
use FluentBooking\Framework\Validator\Rules\RequiredIf;
use FluentBooking\Framework\Validator\Rules\Dimensions;
use FluentBooking\Framework\Validator\Rules\ConditionalRules;
use FluentBooking\Framework\Support\ArrayableInterface;

class Rule
{
    /**
     * Create a new conditional rule set.
     *
     * @param  callable|bool  $condition
     * @param  array|string  $rules
     * @param  array|string  $defaultRules
     * @return \FluentBooking\Framework\Validator\Rules\ConditionalRules
     */
    public static function when($condition, $rules, $defaultRules = [])
    {
        return new ConditionalRules($condition, $rules, $defaultRules);
    }

    /**
     * Get a dimensions constraint builder instance.
     *
     * @param  array  $constraints
     * @return \FluentBooking\Framework\Validator\Rules\Dimensions
     */
    public static function dimensions(array $constraints = [])
    {
        return new Dimensions($constraints);
    }

    /**
     * Get an exists constraint builder instance.
     *
     * @param  string  $table
     * @param  string  $column
     * @return \FluentBooking\Framework\Validator\Rules\Exists
     */
    public static function exists($table, $column = 'NULL')
    {
        return new Exists($table, $column);
    }

    /**
     * Get an in constraint builder instance.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string  $values
     * @return \FluentBooking\Framework\Validator\Rules\In
     */
    public static function in($values)
    {
        if ($values instanceof ArrayableInterface) {
            $values = $values->toArray();
        }

        return new In(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a not_in constraint builder instance.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string  $values
     * @return \FluentBooking\Framework\Validator\Rules\NotIn
     */
    public static function notIn($values)
    {
        if ($values instanceof ArrayableInterface) {
            $values = $values->toArray();
        }

        return new NotIn(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a required_if constraint builder instance.
     *
     * @param  callable|bool  $callback
     * @return \FluentBooking\Framework\Validator\Rules\RequiredIf
     */
    public static function requiredIf($callback)
    {
        return new RequiredIf($callback);
    }

    /**
     * Get a unique constraint builder instance.
     *
     * @param  string  $table
     * @param  string  $column
     * @return \FluentBooking\Framework\Validator\Rules\Unique
     */
    public static function unique($table, $column = 'NULL')
    {
        return new Unique($table, $column);
    }

    /**
     * Add a custom rule.
     * 
     * @param string $rule
     * @param callable $callback
     * @return null
     */
    public static function add($rule, $callback)
    {
        App::make('validator')->extend($rule, $callback);
    }

    /**
     * Handle dynamic calls
     * 
     * @param  string $method
     * @param  array $params
     * @return bool/true
     * @throws BadMethodCallException
     */
    public static function __callStatic($method, $params)
    {
        $method = ucwords($method);

        if ($customRules = App::make('validator')->getExtentions()) {

            if (in_array($method, array_keys($customRules))) {
                return Str::snake($method) . ':' . implode(',', $params);
            }
        }

        throw new BadMethodCallException('Call to undefined method '. __CLASS__ . ':'. $method);
    }
}
