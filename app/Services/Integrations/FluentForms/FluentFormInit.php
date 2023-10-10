<?php

namespace FluentBooking\App\Services\Integrations\FluentForms;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\BookingService;
use FluentBooking\App\Services\TimeSlotService;
use FluentBooking\App\Services\Integrations\FluentForms\BookingElement;
use FluentBooking\App\Services\Integrations\FluentForms\FormDataUpdate;


class FluentFormInit 
{
    private $bookingIds   = [];
    private $bookingsData = [];

    public function init()
    {
        if (defined('FLUENTFORM')) {
            $this->registerHooks();
            $this->registerIntegrations();
        }
    }

    public function registerHooks()
    {
        add_action('fluentform/before_form_validation', [$this, 'handleValidations'], 10, 2);
        add_action('fluentform/before_insert_submission', [$this, 'handleBookings'], 10);
        add_action('fluentform/notify_on_form_submit', [$this, 'updateSubmissionId'], 10, 1);
    }

    public function registerIntegrations()
    {
        new BookingElement();
        new FormDataUpdate();
    }

    private function getName($value)
    {
        if (is_array($value) || is_object($value)) {
            $values = array_filter(array_values((array) $value));
            $value  = Helper::fcalImplodeRecursive(' ', $values);
        }

        if (!$value) {
            $value = Helper::getUserDisplayName();
        }

        return $value;
    }

    private function getEmail($value)
    {
        if (!$value) {
            return Helper::getUserEmail();
        }
        return $value;
    }

    private function prepareData($fields, $formData)
    {
        foreach ($fields as $key => $value)
        {
            if (preg_match('/fcal_booking(_\d+)?/', $key, $matches)) {
                $match = $matches[0];
                $data  = Arr::get($formData, $match);

                $nameField  = Arr::get($value, 'raw.settings.cal_guest_fields.name_field');
                $emailField = Arr::get($value, 'raw.settings.cal_guest_fields.email_field');
                $nameValue  = Arr::get($formData, $nameField);
                $emailValue = Arr::get($formData, $emailField);

                $bookingData = [
                    'email'      => $this->getEmail($emailValue),
                    'name'       => $this->getName($nameValue),
                    'rules'      => Arr::get($value, 'rules'),
                    'event_id'    => Arr::get($value, 'raw.settings.event_id'),
                    'source_url' => site_url(Arr::get($formData, '_wp_http_referer')),
                ];

                $data = json_decode($data, true);

                $this->bookingsData[$match] = $this->bookingsData[$match] ?? [];
                
                $this->bookingsData[$match] = array_merge($bookingData, (array)$data);
            }
        }
    }

    private function validateBooking($key, $data = [])
    {
        $app = App::getInstance();

        $isRequired = Arr::get($data, 'rules.required.value');

        if (!$isRequired && !isset($data['start_time'])) {
            unset($this->bookingsData[$key]);
            return;
        }
        
        $calendarSlot = CalendarSlot::find($data['event_id']);
        
        if ($calendarSlot->status != 'active') {
            throw new \Exception('Sorry, This host is not accepting any new bookings at the moment.', 423);
        }
        
        if ($isRequired && !isset($data['start_time'])) {
            throw new \Exception(Arr::get($data, 'rules.required.message'), 423);
        }

        $rules = [
            'email'      => 'required|email',
            'timezone'   => 'required',
            'start_time' => 'required'
        ];

        $validator = $app->validator->make($data, $rules, []);

        if ($validator->validate()->fails()) {
            wp_send_json([
                'id'       => $data['id'],
                'messages' => $validator->errors(),
                'errors'   => 'Booking Failed'
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
        $calendarSlot = CalendarSlot::find($data['event_id']);

        $startDateTime = DateTimeHelper::convertToUtc($data['start_time'], $data['timezone']);

        $bookingData = [
            'start_time'       => $startDateTime,
            'name'             => sanitize_text_field($data['name']),
            'email'            => sanitize_email($data['email']),
            'person_time_zone' => sanitize_text_field($data['timezone']),
            'source'           => 'fluentform',
            'source_url'       => sanitize_url($data['source_url']),
            'ip_address'       => Helper::getIp()
        ];

        $booking = BookingService::createBooking($bookingData, $calendarSlot);

        $this->bookingIds[] = $booking->id;
    }

    public function handleBookings()
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

    public function handleValidations($fields, $formData)
    {
        $this->prepareData($fields, $formData);

        $bookings = $this->bookingsData;

        foreach ($bookings as $key => $data) {
            try {
                $this->validateBooking($key, $data);
            } catch (\Exception $e) {
                wp_send_json([
                    'id'       => $data['id'],
                    'messages' => $e->getMessage(),
                    'errors'   => 'Booking Failed'
                ], $e->getCode());
            }
        }
    }

    public function updateSubmissionId($submissionId)
    {
        if (!$this->bookingIds) {
            return;
        }

        try {
            Booking::whereIn('id', $this->bookingIds)
                ->update(
                    ['source_id' => $submissionId]
                );
            } catch (\Exception $e) {
                wp_send_json([
                    $e->getMessage()
                ], $e->getCode());
            }
    }
}
