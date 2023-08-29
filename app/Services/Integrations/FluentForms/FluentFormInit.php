<?php

namespace FluentCalendar\App\Services\Integrations\FluentForms;

use FluentCalendar\App\App;
use FluentCalendar\App\Services\Helper;
use FluentCalendar\Framework\Support\Arr;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Services\DateTimeHelper;
use FluentCalendar\App\Services\BookingService;
use FluentCalendar\App\Services\TimeSlotService;
use FluentCalendar\App\Services\Integrations\FluentForms\BookingElement;

class FluentFormInit 
{
    public function init()
    {
        if (defined('FLUENTFORM')) {
            new BookingElement();
            add_action('fluentform/after_form_validation', [$this, 'handleBooking'], 10, 3);       
        }
    }

    private function makeElementId($data, $formId)
    {
        $instance = \FluentForm\App\Helpers\Helper::$formInstance;

        $name = str_replace(['[', ']', ' '], '_', $data['attributes']['name']);

        $suffix = esc_attr($formId);
        if($instance > 1) {
            $suffix = $suffix.'_'.$instance;
        }
        $suffix .= '_'.$name;

        return 'ff_' . esc_attr($suffix);
    }

    private function prepareBookingData($fields, $formData, $formId)
    {
        $email     = Arr::get($formData, 'email');
        $firstName = Arr::get($formData, 'names.first_name');
        $lastName  = Arr::get($formData, 'names.last_name');
        $name      = trim($firstName.' '.$lastName);

        if (!$email) {
            $email = Helper::getUserEmail();
        }
        if (!$name) {
            $name = Helper::getUserDisplayName();
        }
        
        $bookingsData = [];
        foreach ($fields as $key => $value) {
            if (preg_match('/fcal_booking(_\d+)?/', $key, $matches)) {
                $match     = $matches[0];
                $data      = $formData[$match];
                $elementId = $this->makeElementId(Arr::get($value, 'raw'), $formId);

                $bookingData = [
                    'email'      => $email,
                    'name'       => $name,
                    'rules'      => Arr::get($value, 'rules'),
                    'slot_id'    => Arr::get($value, 'raw.settings.slot_id'),
                    'element_id' => $elementId
                ];

                $data = json_decode($data, true);

                $bookingsData[$match] = $bookingsData[$match] ?? [];
                
                $bookingsData[$match] = array_merge($bookingData, (array)$data);
            }
        }

        return $bookingsData;
    }

    private function handleValidation($data = [])
    {
        $app = App::getInstance();

        $isRequired = Arr::get($data, 'rules.required.value');

        if (!$isRequired && !isset($data['start_time'])) {
            return;
        }
        
        $calendarSlot = CalendarSlot::find($data['slot_id']);
        
        if ($calendarSlot->status != 'active') {
            throw new \Exception('Sorry, This host is not accepting any new bookings at the moment.', 423);
        }
        
        if ($isRequired && !isset($data['start_time'])) {
            throw new \Exception(Arr::get($data, 'rules.required.message'), 423);
        }

        $rules = [
            'name'       => 'required',
            'email'      => 'required|email',
            'timezone'   => 'required',
            'start_time' => 'required'
        ];

        $isPhoneRequired = BookingService::isPhoneRequired($calendarSlot);

        if ($isPhoneRequired) {
            $rules['phone'] = 'required';
        }

        $validator = $app->validator->make($data, $rules, []);

        if ($validator->validate()->fails()) {
            wp_send_json([
                'message' => 'Please fill up the required data',
                'errors'  => $validator->errors()
            ], 422);
        }

        $startDateTime = DateTimeHelper::convertToUtc($data['start_time'], $data['timezone']);
        $endDateTime   = date('Y-m-d H:i:s', strtotime($startDateTime) + ($calendarSlot->duration * 60));

        $timeSlotService = new TimeSlotService($calendarSlot->calendar, $calendarSlot);
        $isSpotAvailable = $timeSlotService->isSpotAvailable($startDateTime, $endDateTime);

        if (!$isSpotAvailable) {
            throw new \Exception('This selected time slot is not available. Maybe someone booked the spot just a few seconds ago.', 423);
        }
    }

    private function bookSlot($data = [])
    {
        $calendarSlot = CalendarSlot::find($data['slot_id']);

        $isPhoneRequired = BookingService::isPhoneRequired($calendarSlot);

        $startDateTime = DateTimeHelper::convertToUtc($data['start_time'], $data['timezone']);

        $bookingData = [
            'start_time'       => $startDateTime,
            'name'             => sanitize_text_field($data['name']),
            'email'            => sanitize_email($data['email']),
            'person_time_zone' => sanitize_text_field($data['timezone']),
            'source'           => 'fluentform',
            'ip_address'       => Helper::getIp()
        ];

        if ($isPhoneRequired) {
            $bookingData['phone'] = sanitize_text_field($data['phone']);
        }

        BookingService::createBooking($bookingData, $calendarSlot);
    }

    // Applied Temporary Solution for Updating Errors to Svelte
    private function setErrorText($formId, $message)
    {
        ?>
            <script>
                jQuery(document).ready(function($) {
                    const elem = $('#fcal_error_ff_3_fcal_booking');
                    elem.text('<?php echo esc_js($message); ?>');
                    const parentElem = elem.prev();
                    const childElem  = parentElem.find(':first-child');
                    childElem.eq(0).css('border-color', '#F56C6C');
                });
            </script>
        <?php
    }

    public function handleBooking($fields, $formData, $formId)
    {
        $bookingsData = $this->prepareBookingData($fields, $formData, $formId);

        $hasError = false;
        foreach ($bookingsData as $bookingData) {
            try {
                $this->handleValidation($bookingData);
            } catch (\Exception $e) {
                $hasError = true;
                $this->setErrorText($bookingData['element_id'], $e->getMessage());
            }
        }

        if ($hasError) {
            wp_send_json([
                'errors' => 'Invalid Request'
            ], 422);
        }

        foreach ($bookingsData as $bookingData) {
            try {
                $this->bookSlot($bookingData);
            } catch (\Exception $e) {
                wp_send_json([
                    $e->getMessage()
                ], $e->getCode());
            }
        }
    }
}
