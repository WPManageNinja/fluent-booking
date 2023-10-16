<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\Integrations\PaymentMethods\PaymentHelper;
use FluentBooking\App\Services\Integrations\PaymentMethods\CurrenciesHelper;
use FluentBooking\Framework\Support\Arr;

class BookingFieldService
{
    public static function getCustomFieldsData($postedData, CalendarSlot $slot)
    {
        $customFields = self::getCustomFields($slot, true);

        $errors = [];

        $formattedValues = [];

        foreach ($customFields as $fieldKey => $customField) {
            $value = Arr::get($postedData, $fieldKey);
            if (!$value && Arr::isTrue($customField, 'required')) {
                $errors[$fieldKey . '.required'] = sprintf('%s is required', $customField['label']);
                continue;
            }

            if (is_array($value)) {
                $value = array_map('sanitize_text_field', $value);
            } else if ($customField['type'] == 'textarea') {
                $value = sanitize_textarea_field($value);
            } else {
                $value = sanitize_text_field($value);
            }

            $formattedValues[$fieldKey] = $value;
        }

        if ($errors) {
            return new \WP_Error('required_field', __('Please fill up the required data', 'fluent-booking'), $errors);
        }

        return $formattedValues;
    }

    public static function getBookingFields(CalendarSlot $calendarSlot)
    {
        $requiredIndexes = ['name', 'email', 'message'];

        $defaultFields = [
            'name'    => [
                'index'          => 1,
                'type'           => 'text',
                'name'           => 'name',
                'label'          => __('Your Name', 'fluent-booking'),
                'required'       => true,
                'enabled'        => true,
                'system_defined' => true,
                'disable_alter'  => true,
                'is_visible'     => true,
                'placeholder'    => __('Your Name', 'fluent-booking'),
            ],
            'email'   => [
                'index'          => 2,
                'type'           => 'email',
                'name'           => 'email',
                'label'          => __('Your Email', 'fluent-booking'),
                'required'       => true,
                'enabled'        => true,
                'system_defined' => true,
                'disable_alter'  => true,
                'is_visible'     => true,
                'placeholder'    => __('Your Email', 'fluent-booking'),
            ],
            'message' => [
                'index'          => 3,
                'type'           => 'textarea',
                'name'           => 'message',
                'label'          => __('What is this meeting about?', 'fluent-booking'),
                'required'       => false,
                'enabled'        => true,
                'system_defined' => true,
                'disable_alter'  => false,
            ],
        ];

        if ($calendarSlot->isLocationFieldRequired()) {
            $requiredIndexes[] = 'location';
            $defaultFields['location'] = [
                'index'          => 4,
                'type'           => 'radio',
                'name'           => 'location',
                'label'          => __('Location', 'fluent-booking'),
                'options'        => LocationService::getLocationOptions($calendarSlot),
                'required'       => true,
                'enabled'        => true,
                'system_defined' => true,
                'disable_alter'  => true,
                'placeholder'    => esc_attr__('Location', 'fluent-booking'),
            ];
        } else if ($calendarSlot->isPhoneRequired()) {
            $requiredIndexes[] = 'phone_number';
            $defaultFields['phone_number'] = [
                'index'          => 5,
                'type'           => 'text',
                'name'           => 'phone_number',
                'label'          => __('Your Phone Number', 'fluent-booking'),
                'required'       => true,
                'enabled'        => true,
                'system_defined' => true,
                'disable_alter'  => true,
                'placeholder'    => esc_attr__('Phone Number', 'fluent-booking'),
            ];
        } else if ($calendarSlot->isAddressRequired()) {
            $requiredIndexes[] = 'address';
            $defaultFields['address'] = [
                'index'          => 6,
                'type'           => 'text',
                'name'           => 'address',
                'label'          => __('Your Address', 'fluent-booking'),
                'required'       => true,
                'enabled'        => true,
                'system_defined' => true,
                'disable_alter'  => true,
                'placeholder'    => esc_attr__('Address', 'fluent-booking'),
            ];
        }

        $dbFields = $calendarSlot->getMeta('booking_fields', []);

        if (!$dbFields) {
            $dbFields = array_values($defaultFields);
        }

        $existingFields = [];
        foreach ($dbFields as $dbField) {
            $name = $dbField['name'];
            $existingFields[$name] = $dbField;
        }

        if (empty($defaultFields['location'])) {
            unset($existingFields['location']);
        }

        if (empty($defaultFields['phone_number'])) {
            unset($existingFields['phone_number']);
        }

        if (empty($defaultFields['address'])) {
            unset($existingFields['address']);
        }

        foreach ($requiredIndexes as $index) {
            if (empty($existingFields[$index]) && !empty($defaultFields[$index])) {
                $existingFields[$index] = $defaultFields[$index];
            }
        }

        if ($calendarSlot->type == 'paid' && Helper::isPaymentEnabled()) {
            $paymentSettings = $calendarSlot->getMeta('payment_settings', []);
            $isEnables = Arr::get($paymentSettings, 'enabled') === 'yes';
            if ($isEnables) {
                $exist = Arr::get($existingFields, 'payment_method', []);
                if (!$exist) {
                    $exist = [
                        'index'          => 20,
                        'type'           => 'payment',
                        'name'           => 'payment_method',
                        'required'       => false,
                        'enabled'        => true,
                        'system_defined' => true,
                        'payment_items'  => PaymentHelper::getReceiptTemplate(Arr::get($paymentSettings, 'items')),
                        'label'          => __('Payment Summary', 'fluent-booking'),
                        'currency_sign'  => CurrenciesHelper::getGlobalCurrencySign(),
                    ];
                } else {
                    $exist['currency_sign'] = CurrenciesHelper::getGlobalCurrencySign();
                    $exist['payment_items'] = PaymentHelper::getReceiptTemplate(Arr::get($paymentSettings, 'items'));
                }

                $existingFields['payment_method'] = $exist;
            } else {
                unset($existingFields['payment_method']);
            }
        } else {
            unset($existingFields['payment_method']);
        }

        return array_values($existingFields);
    }

    public static function getBookingFieldLabels(CalendarSlot $calendarSlot, $enabledOnly = false)
    {
        $fields = self::getBookingFields($calendarSlot);
        $labels = [];

        foreach ($fields as $field) {
            if ($enabledOnly && !Arr::isTrue($field, 'enabled')) {
                continue;
            }

            $labels[$field['name']] = $field['label'];
        }

        return $labels;
    }

    public static function getFormattedCustomBookingData(Booking $booking)
    {
        $customFormData = $booking->getMeta('custom_fields_data', []);
        if (!$customFormData) {
            return [];
        }

        $labels = self::getBookingFieldLabels($booking->calendar_event);

        $formattedData = [];

        foreach ($customFormData as $dataKey => $value) {
            if (isset($labels[$dataKey])) {
                $label = $labels[$dataKey];
            } else {
                $label = $dataKey;
            }
            $formattedData[$dataKey] = [
                'label' => $label,
                'value' => is_array($value) ? implode(', ', $value) : $value
            ];
        }

        return $formattedData;
    }

    public static function getCustomFields($calendarSlot, $withConfig = false)
    {
        $existingFields = $calendarSlot->getMeta('booking_fields', []);

        if (!$existingFields) {
            return [];
        }

        $customFields = [];

        foreach ($existingFields as $existingField) {
            if (Arr::get($existingField, 'system_defined') || !Arr::isTrue($existingField, 'enabled')) {
                continue;
            }
            if ($withConfig) {
                $customFields[$existingField['name']] = $existingField;
            } else {
                $customFields[$existingField['name']] = $existingField['label'];
            }
        }

        return $customFields;
    }
}
