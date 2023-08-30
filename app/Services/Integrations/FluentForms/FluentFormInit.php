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
    private $bookingsData = [];

    public function init()
    {
        if (defined('FLUENTFORM')) {
            new BookingElement();
            add_action('fluentform/before_form_validation', [$this, 'handleValidation'], 10, 3);
            add_action('fluentform/before_insert_submission', [$this, 'handleBooking'], 10);       
        }
    }

    private function prepareData($fields, $formData)
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

        foreach ($fields as $key => $value) {
            if (preg_match('/fcal_booking(_\d+)?/', $key, $matches)) {
                $match     = $matches[0];
                $data      = $formData[$match];

                $bookingData = [
                    'email'      => $email,
                    'name'       => $name,
                    'rules'      => Arr::get($value, 'rules'),
                    'slot_id'    => Arr::get($value, 'raw.settings.slot_id')
                ];

                $data = json_decode($data, true);

                $this->bookingsData[$match] = $this->bookingsData[$match] ?? [];
                
                $this->bookingsData[$match] = array_merge($bookingData, (array)$data);
            }
        }
    }

    private function validateBooking($data = [])
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
                'id'       => $data['id'],
                'messages' => $validator->errors()
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

    public function handleBooking()
    {
        $bookings = $this->bookingsData;

        foreach ($bookings as $data) {
            try {
                $this->bookSlot($data);
            } catch (\Exception $e) {
                wp_send_json([
                    $e->getMessage()
                ], $e->getCode());
            }
        }
    }

    public function handleValidation($fields, $formData)
    {
        $this->prepareData($fields, $formData);

        $bookings = $this->bookingsData;

        $hasError = false;
        foreach ($bookings as $data) {
            try {
                $this->validateBooking($data);
            } catch (\Exception $e) {
                wp_send_json([
                    'id'       => $data['id'],
                    'messages' => $e->getMessage(),
                ], $e->getCode());
            }
        }
    }
}
