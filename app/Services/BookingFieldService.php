<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\Framework\Support\Arr;

class BookingFieldService
{
    public static function getCustomFieldsData($fieldValues, CalendarSlot $slot)
    {
        $mainFields = ['name', 'email', 'phone_number', 'message'];

        $customFields = self::getBookingFields($slot);

        $formattedValues = [];
        foreach ($customFields as $field) {
            if (!in_array($field['name'], $mainFields) && $field['enabled']) {
                $value = $fieldValues[$field['name']];
                if (empty($value) && $field['required']) {
                    return new \WP_Error('required_field', 'Required Field is missing', [
                        'field' => $field['label']
                    ]);
                }

                $formattedValues[$field['name']] = sanitize_text_field($value);
            }
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

        if ($calendarSlot->isPhoneRequired()) {
            $requiredIndexes[] = 'phone_number';
            $defaultFields['phone_number'] = [
                'index'          => 4,
                'type'           => 'phone',
                'name'           => 'phone_number',
                'label'          => __('Your Phone Number', 'fluent-booking'),
                'required'       => true,
                'enabled'        => true,
                'system_defined' => true,
                'disable_alter'  => true,
                'placeholder'    => esc_attr__('Phone Number', 'fluent-booking'),
            ];
        }

        $existingFields = $calendarSlot->getMeta('booking_fields', []);


        if ($calendarSlot->type == 'paid'){
            $paymentSettings = $calendarSlot->getMeta('payment_settings', []);
            $isEnables = Arr::get($paymentSettings, 'enabled') === 'yes';

//            if($isEnables) {
//                $requiredIndexes[] = 'payment_method';
//                $defaultFields['payment_method'] = [
//                    'index'          => 20,
//                    'type'           => 'payment',
//                    'name'           => 'payment_method',
//                    'required'       => false,
//                    'enabled'        => true,
//                    'system_defined' => true,
//                    'payment_items'  => Arr::get($paymentSettings, 'items'),
//                    'label' => 'Payment Summary',
//                    'currency_sign' => Arr::get($paymentSettings, 'currency_sign'),
//                ];
//            }
        }

        if (!$existingFields) {
            return array_values($defaultFields);
        }

        $validFields = [];

        foreach ($existingFields as $existingField) {
            $name = $existingField['name'];
            if (in_array($name, $requiredIndexes)) {
                // remove from required indexes
                $requiredIndexes = array_diff($requiredIndexes, [$name]);
            }

            $validFields[] = $existingField;
        }

        if ($requiredIndexes) {
            foreach ($requiredIndexes as $requiredIndex) {
                $validFields[] = $defaultFields[$requiredIndex];
            }
        }

        return $validFields;
    }

    public static function getBookingFieldLabels(CalendarSlot $calendarSlot)
    {
        $fields = self::getBookingFields($calendarSlot);
        $labels = [];

        foreach ($fields as $field) {
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

        $labels = self::getBookingFieldLabels($booking->slot);

        $formattedData = [];

        foreach ($customFormData as $dataKey => $value) {
            if(isset($labels[$dataKey])) {
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
}
