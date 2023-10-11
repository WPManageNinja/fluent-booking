<?php

namespace FluentBooking\App\Hooks\Handlers;

use FluentBooking\App\App;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\BookingService;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\TimeSlotService;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Validator\ValidationException;

class FrontEndHandler
{
    public function register()
    {
        add_shortcode('fluent_booking', [$this, 'handleShortcode']);

        add_action('wp_ajax_fluent_cal_schedule_meeting', [$this, 'ajaxScheduleMeeting']);
        add_action('wp_ajax_nopriv_fluent_cal_schedule_meeting', [$this, 'ajaxScheduleMeeting']);

        add_action('wp_ajax_fluent_cal_get_available_dates', [$this, 'ajaxGetAvailableDates']);
        add_action('wp_ajax_nopriv_fluent_cal_get_available_dates', [$this, 'ajaxGetAvailableDates']);
    }

    public function handleShortcode($atts, $content)
    {
        $atts = shortcode_atts([
            'id'             => 0,
            'disable_author' => 'no'
        ], $atts);

        if (!$atts['id']) {
            return '';
        }

        $slot = CalendarSlot::find($atts['id']);

        if (!$slot) {
            return '';
        }

        $calendar = $slot->calendar;

        $slot->max_lookup_date = $slot->getMaxLookUpDate();
        $slot->min_lookup_date = $slot->getMinLookUpDate();

        $formFields = BookingService::getBookingFields($slot);

        if (!$slot || !$calendar) {
            return 'Calendar not found';
        }

        wp_enqueue_script('fluent-booking-public', App::getInstance('url.assets') . 'public/js/app.js', [], App::getInstance('config')->get('app.version'), true);

        $this->loadGlobalVars();

        $slot->location_settings = (object)[];

        $slot->description = wpautop($slot->description);

        wp_localize_script('fluent-booking-public', 'fcal_public_vars_' . $calendar->id . '_' . $slot->id, [
            'slot'           => $slot,
            'calendar'       => $calendar,
            'author_profile' => $slot->getAuthorProfile(true),
            'form_fields'    => $formFields,
            'disable_author' => $atts['disable_author'] == 'yes',
        ]);

        return App::make('view')->make('public.calendar', [
            'slot'     => $slot,
            'calendar' => $calendar
        ]);
    }

    private function loadGlobalVars()
    {
        static $loaded;

        if ($loaded) {
            return;
        }

        $loaded = true;

        wp_localize_script('fluent-booking-public', 'fluentCalendarPublicVars', $this->getGlobalVars());
    }

    public function getGlobalVars()
    {
        $currentPerson = [
            'name'  => '',
            'email' => ''
        ];

        if (is_user_logged_in()) {
            $currentUser = wp_get_current_user();
            $name = trim($currentUser->first_name . ' ' . $currentUser->last_name);
            $currentPerson = [
                'name'    => $name ? $name : $currentUser->display_name,
                'email'   => $currentUser->user_email,
                'user_id' => $currentUser->ID
            ];
        }

        return [
            'ajaxurl'        => admin_url('admin-ajax.php'),
            'timezones'      => DateTimeHelper::getFlatGroupedTimeZones(),
            'current_person' => $currentPerson
        ];
    }

    public function ajaxScheduleMeeting()
    {
        $app = App::getInstance();

        $slotId = (int)$_REQUEST['event_id'];

        $calendarSlot = CalendarSlot::find($slotId);

        if (!$calendarSlot || $calendarSlot->status != 'active') {
            wp_send_json([
                'message' => 'Sorry, this host is not accepting any new bookings at the moment'
            ], 423);
        }

        $postedData = $_REQUEST;

        $rules = [
            'name'       => 'required',
            'email'      => 'required|email',
            'timezone'   => 'required',
            'start_date' => 'required'
        ];

        $isPhoneRequired = $calendarSlot->isPhoneRequired();
        if ($isPhoneRequired) {
            $rules['phone_number'] = 'required';
        }

        $validator = $app->validator->make($postedData, $rules, []);
        if ($validator->validate()->fails()) {
            wp_send_json([
                'message' => 'Please fill up the required data',
                'errors'  => $validator->errors()
            ], 422);
        }

        $customFieldsData = BookingService::getCustomFieldsData($postedData, $calendarSlot);

        $startDateTime = DateTimeHelper::convertToUtc($postedData['start_date'], $postedData['timezone']);
        $endDateTime = date('Y-m-d H:i:s', strtotime($startDateTime) + ($calendarSlot->duration * 60));

        $bookingData = [
            'person_time_zone' => sanitize_text_field($postedData['timezone']),
            'start_time'       => $startDateTime,
            'name'             => sanitize_text_field($postedData['name']),
            'email'            => sanitize_email($postedData['email']),
            'message'          => sanitize_textarea_field(Arr::get($postedData, 'message', '')),
            'phone'            => sanitize_textarea_field(Arr::get($postedData, 'phone_number', '')),
            'ip_address'       => Helper::getIp(),
            'status'           => 'scheduled',
            'event_type'       => $calendarSlot->event_type
        ];

        $sourceUrl = Arr::get($postedData, 'source_url', '');

        if ($sourceUrl) {
            $bookingData['source_url'] = sanitize_url($sourceUrl);
        }

        if ($isPhoneRequired) {
            $bookingData['phone'] = sanitize_text_field($postedData['phone']);
        }

        // Check if the time is available or not for this slot
        $timeSlotService = new TimeSlotService($calendarSlot->calendar, $calendarSlot);
        $isSpotAvailable = $timeSlotService->isSpotAvailable($startDateTime, $endDateTime);

        if (!$isSpotAvailable) {
            wp_send_json([
                'message' => 'This selected time slot is not available. Maybe someone booked the spot just a few seconds ago.'
            ], 423);
        }

        try {
            $booking = BookingService::createBooking($bookingData, $calendarSlot);
            if ($customFieldsData) {
                Helper::updateBookingMeta($booking->id, 'custom_fields_data', $customFieldsData);
            }
        } catch (\Exception $e) {
            wp_send_json([
                'message' => $e->getMessage()
            ], 423);
            return;
        }

        $author = $calendarSlot->getAuthorProfile(true);

        $confirmationData = [
            'sub_heading' => sprintf(__('You are scheduled with %s', 'fluent-booking'), $author['name']),
            'slot'        => $calendarSlot,
            'booking'     => $booking,
            'message'     => 'A confirmation has been sent to your email address along with meeting location details.'
        ];

        $confirmationData = apply_filters('fluent_booking/booking_confirmation_data', $confirmationData, $booking, $calendarSlot);

        $responseHtml = (string)App::make('view')->make('public.booking_confirmation', $confirmationData);

        wp_send_json([
            'message'       => 'Booking has been confirmed',
            'response_html' => $responseHtml
        ], 200);
    }

    public function ajaxGetAvailableDates()
    {
        $slotId = (int)$_REQUEST['event_id'];
        $slot = CalendarSlot::findOrfail($slotId);

        if (!$slot || $slot->status != 'active') {
            wp_send_json([
                'message' => 'Sorry, the host is not accepting any new bookings at the moment.'
            ], 423);
        }

        $calendar = $slot->calendar;
        $startDate = Arr::get($_REQUEST, 'start_date');

        if (!$startDate) {
            $startDate = date('Y-m-d H:i:s');
        }

        $timeZone = Arr::get($_REQUEST, 'timezone');

        if (!$timeZone) {
            $timeZone = wp_timezone_string();
        }

        if (!in_array($timeZone, \DateTimeZone::listIdentifiers())) {
            $timeZone = $calendar->author_timezone;
        }

        $timeSlotService = new TimeSlotService($calendar, $slot);

        $availableSpots = $timeSlotService->getAvailableSpots($startDate, $timeZone);

        if (is_wp_error($availableSpots)) {
            wp_send_json([
                'available_slots' => [],
                'timezone'        => $timeZone,
                'invalid_dates'   => true,
                'max_lookup_date' => $slot->getMaxLookUpDate(),
            ], 200);
        }

        $availableSpots = array_filter($availableSpots);
        $availableSpots = apply_filters('fluent_booking/available_slots_for_view', $availableSpots, $slot, $calendar, $timeZone);

        wp_send_json([
            'available_slots' => $availableSpots,
            'timezone'        => $timeZone,
            'max_lookup_date' => $slot->getMaxLookUpDate(),
        ], 200);
    }
}
