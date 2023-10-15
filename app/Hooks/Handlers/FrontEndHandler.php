<?php

namespace FluentBooking\App\Hooks\Handlers;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\BookingFieldService;
use FluentBooking\App\Services\BookingService;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\PaymentMethods\CurrenciesHelper;
use FluentBooking\App\Services\LocationService;
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


        add_action('wp_ajax_fcal_cancel_meeting', [$this, 'ajaxHandleCancelMeeting']);
        add_action('wp_ajax_nopriv_fcal_cancel_meeting', [$this, 'ajaxHandleCancelMeeting']);

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

        if (!$calendar) {
            return 'Calendar not found';
        }

        wp_enqueue_script('fluent-booking-public', App::getInstance('url.assets') . 'public/js/app.js', [], FLUENT_BOOKING_ASSETS_VERSION, true);

        $this->loadGlobalVars();
        $localizeData = $this->getCalendarEventVars($calendar, $slot);
        $localizeData['disable_author'] = $atts['disable_author'] == 'yes';

        wp_localize_script(
            'fluent-booking-public',
            'fcal_public_vars_' . $calendar->id . '_' . $slot->id,
            $localizeData
        );

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

        $globalSettings = Helper::getGlobalSettings();
        $startDay =  Arr::get($globalSettings, 'administration.start_day', 'mon');

        return [
            'ajaxurl'        => admin_url('admin-ajax.php'),
            'timezones'      => DateTimeHelper::getFlatGroupedTimeZones(),
            'current_person' => $currentPerson,
            'start_day'      => $startDay
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
            ], 422);
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

        $isAddressRequired = $calendarSlot->isAddressRequired();
        if ($isAddressRequired) {
            $rules['address'] = 'required';
        }

        $validator = $app->validator->make($postedData, $rules, [
            'name.required'       => 'Please enter your name',
            'email.required'      => 'Please enter your email address',
            'email.email'         => 'Please enter provide a valid email address',
            'timezone.required'   => 'Please select timezone first',
            'start_date.required' => 'Please select a date and time',
        ]);
        if ($validator->validate()->fails()) {
            wp_send_json([
                'message' => 'Please fill up the required data',
                'errors'  => $validator->errors()
            ], 422);
            return;
        }

        $customFieldsData = BookingFieldService::getCustomFieldsData($postedData, $calendarSlot);

        if (is_wp_error($customFieldsData)) {
            wp_send_json([
                'message' => $customFieldsData->get_error_message(),
                'errors'  => $customFieldsData->get_error_data()
            ], 422);
            return;
        }

        $startDateTime = DateTimeHelper::convertToUtc($postedData['start_date'], $postedData['timezone']);
        $endDateTime = date('Y-m-d H:i:s', strtotime($startDateTime) + ($calendarSlot->duration * 60));

        $bookingData = [
            'person_time_zone' => sanitize_text_field($postedData['timezone']),
            'start_time'       => $startDateTime,
            'name'             => sanitize_text_field($postedData['name']),
            'email'            => sanitize_email($postedData['email']),
            'message'          => sanitize_textarea_field(Arr::get($postedData, 'message', '')),
            'phone'            => sanitize_textarea_field(Arr::get($postedData, 'phone_number', '')),
            'address'          => sanitize_textarea_field(Arr::get($postedData, 'address', '')),
            'ip_address'       => Helper::getIp(),
            'status'           => 'scheduled',
            'source'           => 'web',
            'event_type'       => $calendarSlot->event_type
        ];

        $sourceUrl = Arr::get($postedData, 'source_url', '');

        if ($sourceUrl) {
            $bookingData['source_url'] = sanitize_url($sourceUrl);
        }

        // Check if the time is available or not for this slot
        $timeSlotService = new TimeSlotService($calendarSlot->calendar, $calendarSlot);
        $isSpotAvailable = $timeSlotService->isSpotAvailable($startDateTime, $endDateTime);

        if (!$isSpotAvailable) {
            wp_send_json([
                'message' => 'This selected time slot is not available. Maybe someone booked the spot just a few seconds ago.'
            ], 422);
        }

        if (isset($postedData['payment_method'])) {
            $customFieldsData['payment_method'] = $postedData['payment_method'];
        }

        try {
            $booking = BookingService::createBooking($bookingData, $calendarSlot, $customFieldsData);

            if (is_wp_error($booking)) {
                throw new \Exception($booking->get_error_message(), 422);
            }

        } catch (\Exception $e) {
            wp_send_json([
                'message' => $e->getMessage()
            ], 422);
            return;
        }

        $html = BookingService::getBookingConfirmationHtml($booking);

        wp_send_json([
            'message'       => 'Booking has been confirmed',
            'response_html' => $html
        ], 200);
    }

    public function ajaxGetAvailableDates()
    {
        $slotId = (int)$_REQUEST['event_id'];
        $slot = CalendarSlot::findOrfail($slotId);

        if (!$slot || $slot->status != 'active') {
            wp_send_json([
                'message' => 'Sorry, the host is not accepting any new bookings at the moment.'
            ], 422);
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

    public function getCalendarEventVars(Calendar $calendar, CalendarSlot $calendarEvent)
    {
        $calendarEvent->max_lookup_date = $calendarEvent->getMaxLookUpDate();
        $calendarEvent->min_lookup_date = $calendarEvent->getMinLookUpDate();

        $calendarEvent->description = wpautop($calendarEvent->description);
        $calendarEvent->location_icon_html = $calendarEvent->defaultLocationHtml();
        $formFields = BookingFieldService::getBookingFields($calendarEvent);

        $paymentSettings = $calendarEvent->getMeta('payment_settings', []);

        if ($paymentSettings && Arr::get($paymentSettings, 'enabled') == 'yes') {
            $total = 0;
            foreach ($paymentSettings['items'] as $payment) {
                $total += (int)$payment['value'];
            }

            $currency = CurrenciesHelper::getCurrencySign();
            $calendarEvent->total_payment = $calendarEvent->defaultPaymentIcon($currency, $total);
        } else {
            $calendarEvent->total_payment = '';
        }

        $eventData = [
            'id'                 => $calendarEvent->id,
            'max_lookup_date'    => $calendarEvent->max_lookup_date,
            'min_lookup_date'    => $calendarEvent->min_lookup_date,
            'duration'           => $calendarEvent->duration,
            'title'              => $calendarEvent->title,
            'location_settings'  => $calendarEvent->location_settings,
            'location_icon_html' => $calendarEvent->location_icon_html,
            'description'        => $calendarEvent->description,
            'pre_selects'        => (object)[]
        ];

        $author = $calendar->getAuthorProfile(true);
        $author['name'] = $calendar->title;

        return apply_filters('fluent_calendar_public_event_vars', [
            'slot'           => $calendarEvent,
            'author_profile' => $author,
            'form_fields'    => $formFields,
        ], $calendarEvent);
    }

    public function ajaxHandleCancelMeeting()
    {
        $data = $_REQUEST;

        $meetingHash = Arr::get($_REQUEST, 'meeting_hash');

        $meeting = Booking::where('hash', $meetingHash)->first();

        if (!$meeting) {
            wp_send_json([
                'message' => 'Sorry! meeting could not be found'
            ], 422);
        }

        if ($meeting->status != 'scheduled' || $meeting->status != 'pending') {
            wp_redirect($meeting->getConfirmationUrl());
            exit;
        }

        $message = sanitize_textarea_field(Arr::get($data, 'cancellation_reason', ''));

        $meeting->cancelMeeting($message, 'guest', get_current_user_id());
        
        if (wp_doing_ajax()) {
            wp_send_json([
                'message' => 'Meeting has been cancelled'
            ], 200);
        }

        wp_redirect($meeting->getConfirmationUrl());
        exit;
    }
}
