<?php

namespace FluentCalendar\Framework\Foundation;

use FluentCalendar\Framework\Foundation\App;
use FluentCalendar\Framework\Validator\Validator;
use FluentCalendar\Framework\Validator\ValidationException;

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
     * @param  FluentCalendar\Framework\Validator\Validator $validator
     * @return array
     * @throws FluentCalendar\Framework\Validator\ValidationException
     */
    public function validate(Validator $validator)
    {
        try {

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
