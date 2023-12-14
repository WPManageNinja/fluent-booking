<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\App;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\BookingService;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\TimeSlotService;
use FluentBooking\App\Services\BookingFieldService;
use FluentBooking\App\Hooks\Handlers\FrontEndHandler;
use FluentBooking\Framework\Request\Request;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class BookingController extends Controller
{
    public function getSlots(Request $request, $slotId)
    {
        $slot = CalendarSlot::findOrfail($slotId);

        if ($slot->status != 'active') {
            return $this->sendError([
                'message' => __('Sorry, the host is not accepting any new bookings at the moment.', 'fluent-booking-pro')
            ]);
        }

        $calendar = $slot->calendar;
        $startDate = $request->get('start_date', gmdate('Y-m-d H:i:s'));
        $timeZone = $request->get('timezone', 'UTC');

        if (!$timeZone) {
            $timeZone = 'UTC';
        }

        $timeSlotService = new TimeSlotService($calendar, $slot);

        $availableSpots = $timeSlotService->getAvailableSpots($startDate, $timeZone);

        if(is_wp_error($availableSpots)) {
            return [
                'available_slots' => [],
                'timezone'        => $timeZone,
                'invalid_dates'   => true,
                'max_lookup_date' => $slot->getMaxLookUpDate(),
            ];
        }

        return [
            'available_slots' => array_filter($availableSpots),
            'timezone'        => $timeZone,
            'max_lookup_date' => $slot->getMaxLookUpDate(),
        ];
    }

    public function createBooking(Request $request, $eventId)
    {
        $calendarEvent = CalendarSlot::findOrfail($eventId);

        if ($calendarEvent->status != 'active') {
            return $this->sendError([
                'message' => __('Sorry, the host is not accepting any new bookings at the moment.', 'fluent-booking-pro')
            ]);
        }

        $postedData = $request->all();

        $rules = [
            'name'       => 'required',
            'email'      => 'required|email',
            'timezone'   => 'required',
            'event_time' => 'required'
        ];

        $messages = [
            'name.required'       => __("Please enter attendee's name", 'fluent-booking-pro'),
            'email.required'      => __("Please enter attendee's email address", 'fluent-booking-pro'),
            'email.email'         => __('Please provide a valid email address', 'fluent-booking-pro'),
            'timezone.required'   => __('Please select the timezone', 'fluent-booking-pro'),
            'event_time.required' => __('Please select a date and time', 'fluent-booking-pro')
        ];

        $locationType = Arr::get($postedData, 'location_type');

        if ($calendarEvent->isPhoneRequired()) {
            $rules['location_description'] = 'required';
            $messages['location_description.required'] = __("Please provide attendee's phone number", 'fluent-booking-pro');
        } else if ($calendarEvent->isAddressRequired()) {
            $rules['location_description'] = 'required';
            $messages['location_description.required'] = __("Please provide attendee's address", 'fluent-booking-pro');
        }

        $requiredFields = array_filter($calendarEvent->getMeta('booking_fields', []), function ($field) {
            return Arr::isTrue($field, 'required') && Arr::isTrue($field, 'enabled') && Arr::get($field, 'name') == 'message';
        });

        foreach ($requiredFields as $field) {
            if (empty($rules[$field['name']])) {
                $rules[$field['name']] = 'required';
                $messages[$field['name'] . '.required'] = __('This field is required', 'fluent-booking-pro');
            }
        }

        $validationConfig = apply_filters('fluent_booking/schedule_validation_rules_data', [
            'rules'    => $rules,
            'messages' => $messages
        ], $postedData, $calendarEvent);

        $app = App::getInstance();

        $validator = $app->validator->make($postedData, $validationConfig['rules'], $validationConfig['messages']);
        if ($validator->validate()->fails()) {
            wp_send_json([
                'message' => __('Please fill up the required data', 'fluent-booking-pro'),
                'errors'  => $validator->errors()
            ], 422);
            return;
        }

        $customFieldsData = BookingFieldService::getCustomFieldsData(Arr::get($postedData, 'custom_fields', []), $calendarEvent);
        $customFieldsData = apply_filters('fluent_booking/schedule_custom_field_data', $customFieldsData, $calendarEvent);

        if (is_wp_error($customFieldsData)) {
            wp_send_json([
                'message' => $customFieldsData->get_error_message(),
                'errors'  => $customFieldsData->get_error_data()
            ], 422);
            return;
        }

        $duration = $calendarEvent->duration;
        if (Arr::isTrue($calendarEvent->settings, 'multi_duration.enabled')) {
            $duration = Arr::get($calendarEvent->settings, 'multi_duration.default_duration');
        }

        $startDateTime = DateTimeHelper::convertToUtc($postedData['event_time'], $postedData['timezone']);
        $endDateTime   = gmdate('Y-m-d H:i:s', strtotime($startDateTime) + ($duration * 60));

        $bookingData = [
            'person_time_zone' => sanitize_text_field($postedData['timezone']),
            'start_time'       => $startDateTime,
            'name'             => sanitize_text_field($postedData['name']),
            'email'            => sanitize_email($postedData['email']),
            'message'          => sanitize_textarea_field(wp_unslash(Arr::get($postedData, 'message', ''))),
            'phone'            => sanitize_textarea_field(Arr::get($postedData, 'phone_number', '')),
            'address'          => sanitize_textarea_field(Arr::get($postedData, 'address', '')),
            'ip_address'       => Helper::getIp(),
            'status'           => sanitize_text_field($postedData['status']),
            'source'           => 'web',
            'event_type'       => $calendarEvent->event_type,
            'slot_minutes'     => $duration
        ];

        $eventLocations = [];
        $locationSettings = $calendarEvent->location_settings;
        foreach ($locationSettings as $index => $location) {
            $eventLocations[$location['type']] = $location;
        }

        $locationDetails['type'] = $locationType;
        if ($locationType == 'phone_organizer') {
            $locationDetails['description'] = $eventLocations[$locationType]['host_phone_number'];
        } else if ($locationType == 'phone_guest') {
            $bookingData['phone'] = Arr::get($postedData, 'location_description', '');
        } else if ($locationType == 'in_person_guest') {
            $locationDetails['description'] = Arr::get($postedData, 'location_description', '');
        } else if (in_array($locationType, ['custom', 'in_person_organizer'])) {
            $locationDetails['description'] = $eventLocations[$locationType]['description'];
        } else if (in_array($locationType, ['google_meet', 'online_meeting', 'zoom_meeting', 'ms_teams'])) {
            $locationDetails['description'] = $eventLocations[$locationType]['meeting_link'];
        }

        $bookingData['location_details'] = $locationDetails;

        if ($sourceUrl = Arr::get($postedData, 'source_url', '')) {
            $bookingData['source_url'] = sanitize_url($sourceUrl);
        }

        // Check if the time is available or not for this slot
        if (!Arr::isTrue($postedData, 'ignore_availability')) {
            $timeSlotService = new TimeSlotService($calendarEvent->calendar, $calendarEvent);
            $isSpotAvailable = $timeSlotService->isSpotAvailable($startDateTime, $endDateTime);
            
            if (!$isSpotAvailable) {
                wp_send_json([
                    'message' => __('This selected time slot is not available. Maybe someone booked the spot just a few seconds ago.', 'fluent-booking-pro')
                ], 422);
            }
        }

        do_action('fluent_booking/before_creating_schedule', $bookingData, $postedData, $calendarEvent);

        try {
            $booking = BookingService::createBooking($bookingData, $calendarEvent, $customFieldsData);
        } catch (\Exception $e) {
            wp_send_json([
                'message' => $e->getMessage()
            ], $e->getCode());
        }

        return [
            'booking' => $booking,
            'message' => __('Booking has been created', 'fluent-booking-pro'),
        ];
    }

    public function getEvent(Request $request)
    {
        $eventId = $request->get('event_id');
        
        $calendarEvent = CalendarSlot::query()->find($eventId);

        if (!$calendarEvent || $calendarEvent->status != 'active') {
            wp_send_json([
                'message' => __('Sorry, the host is not accepting any new bookings at the moment.', 'fluent-booking-pro')
            ], 422);
        }

        $calendar = $calendarEvent->calendar;

        if (!$calendar) {
            return $this->sendError([
                'message' => __('Calendar not found', 'fluent-booking-pro')
            ]);
        }

        $calendarEventVars = (new FrontEndHandler())->getCalendarEventVars($calendar, $calendarEvent);

        $startDate = $request->get('start_date');

        if (!$startDate) {
            $startDate = gmdate('Y-m-d H:i:s');
        }

        $timeZone = $request->get('timezone');

        if (!$timeZone) {
            $timeZone = wp_timezone_string();
        }

        if (!in_array($timeZone, \DateTimeZone::listIdentifiers())) {
            $timeZone = $calendar->author_timezone;
        }

        $timeSlotService = new TimeSlotService($calendar, $calendarEvent);

        $availableSpots = $timeSlotService->getAvailableSpots($startDate, $timeZone);

        if (is_wp_error($availableSpots)) {
            wp_send_json([
                'available_slots' => [],
                'calendar_event'  => $calendarEventVars,
                'timezone'        => $timeZone,
                'invalid_dates'   => true
            ], 200);
        }

        $availableSpots = apply_filters('fluent_booking/available_slots_for_view', array_filter($availableSpots), $calendarEvent, $calendar, $timeZone);

        return [
            'calendar_event'  => $calendarEventVars,
            'available_slots' => $availableSpots
        ];
    }
}
