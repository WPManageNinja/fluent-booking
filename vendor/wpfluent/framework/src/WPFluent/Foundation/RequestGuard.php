<?php

namespace FluentBooking\Framework\Foundation;

use FluentBooking\Framework\Foundation\App;
use FluentBooking\Framework\Validator\Validator;
use FluentBooking\Framework\Validator\ValidationException;

abstract class RequestGuard
{
    /**
     * Retrive the validation rules
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Retrive the validation messages set by the developer
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Allow the developer tinker with data before the validation.
     * @return array
     */
    public function beforeValidation()
    {
        return [];
    }

    /**
     * Allow the developer tinker with data after the validation.
     * @return array
     */
    public function afterValidation()
    {
        return [];
    }

    /**
     * Validate ther request
     * @param  FluentBooking\Framework\Validator\Validator $validator
     * @return array
     * @throws FluentBooking\Framework\Validator\ValidationException
     */
    public function validate(Validator $validator = null)
    {
        try {

            $validator = $validator ?: App::make(Validator::class);

            if (!($rules = (array) $this->rules())) return;

            $validator = $validator->make($data = $this->all(), $rules, (array) $this->messages());

            if ($validator->validate()->fails()) {
                throw new ValidationException('Unprocessable Entity!', 422, null, $validator->errors());
            }

            return $data;

        } catch (ValidationException $e) {

            if (defined('REST_REQUEST') && REST_REQUEST) {
                throw $e;
            } else {
                App::getInstance()->doCustomAction('handle_exception', $e);
            }
        }
    }

    /**
     * Handles validation including before and after calls
     *
     * @throws FluentBooking\Framework\Validator\ValidationException
     * @return null
     */
    public static function applyValidation()
    {
        $instance = new static;

        $request = App::getInstance('request');

        $request->merge($instance->beforeValidation());

        $instance->validate(App::make(Validator::class));

        $request->merge($instance->afterValidation());
    }

    /**
     * Get an input element from the request.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Handle the dynamic method calls
     * @param  string $method
     * @param  array $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        return call_user_func_array(
            [App::getInstance('request'), $method], $params
        );
    }
}
