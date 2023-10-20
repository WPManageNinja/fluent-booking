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
use FluentBooking\App\Services\ReceiptHelper;
use FluentBooking\App\Services\TimeSlotService;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Support\Collection;
use FluentBooking\Framework\Validator\ValidationException;

class FrontEndHandler
{
    public function register()
    {
        add_shortcode('fluent_booking', [$this, 'handleShortcode']);

        add_shortcode('fluent_booking_receipt', [$this, 'handleReceiptShortcode']);


        add_action('wp_ajax_fluent_cal_schedule_meeting', [$this, 'ajaxScheduleMeeting']);
        add_action('wp_ajax_nopriv_fluent_cal_schedule_meeting', [$this, 'ajaxScheduleMeeting']);


        add_action('wp_ajax_fcal_cancel_meeting', [$this, 'ajaxHandleCancelMeeting']);
        add_action('wp_ajax_nopriv_fcal_cancel_meeting', [$this, 'ajaxHandleCancelMeeting']);

        add_action('wp_ajax_fluent_cal_get_available_dates', [$this, 'ajaxGetAvailableDates']);
        add_action('wp_ajax_nopriv_fluent_cal_get_available_dates', [$this, 'ajaxGetAvailableDates']);

        /*
         * Rescheduing Handlers
         */

        add_action('fluent_booking/starting_scheduling_ajax', function ($data) {
            if (empty($data['rescheduling_hash'])) {
                return;
            }

            add_filter('fluent_booking/schedule_custom_field_data', function ($array) {
                return [];
            });

            add_filter('fluent_booking/schedule_validation_rules_data', function ($data) {
                return [
                    'messages' => [
                        '_rescheduling_reason.required' => __('Please provide a rescheduling reason', 'fluent-booking-pro')
                    ],
                    'rules'    => [
                        '_rescheduling_reason' => 'required'
                    ]
                ];
            });

            add_action('fluent_calendar/before_creating_schedule', function ($bookingData, $postedData) {
                $existingHash = Arr::get($postedData, 'rescheduling_hash');
                $existingBooking = Booking::where('hash', $existingHash)->first();

                if (!$existingBooking) {
                    wp_send_json([
                        'message' => __('Invalid rescheduling request', 'fluent-booking-pro')
                    ], 422);
                }

                if ($existingBooking->status != 'scheduled') {
                    wp_send_json([
                        'message' => __('Sorry, you can not reschedule this meeting.', 'fluent-booking-pro')
                    ], 422);
                }
 
                $endDateTime = date('Y-m-d H:i:s', strtotime($bookingData['start_time']) + ($existingBooking->calendar_event->duration * 60));

                $previousBooking = clone $existingBooking;

                $existingBooking->start_time = $bookingData['start_time'];
                $existingBooking->person_time_zone = $bookingData['person_time_zone'];
                $existingBooking->end_time = $endDateTime;
                $existingBooking->save();

                $reschedulingMessage = sanitize_textarea_field(Arr::get($postedData, '_rescheduling_reason'));
                $existingBooking->updateMeta('reschedule_reason', $reschedulingMessage);
                $existingBooking->updateMeta('rescheduled_by_type', 'guest');
                $existingBooking->updateMeta('previous_meeting_time', $previousBooking->start_time);

                do_action('fluent_booking/log_booking_activity', [
                    'title'       => 'Meeting Rescheduled',
                    'description' => 'Meeting has been rescheduled by guest from Web UI'
                ]);

                do_action('fluent_booking/after_booking_rescheduled', $existingBooking, $previousBooking);

                add_filter('fluent_booking/schedule_receipt_data', function ($data) {
                    $data['title'] = __('Your meeting has been rescheduled', 'fluent-booking-pro');
                    return $data;
                });

                $html = BookingService::getBookingConfirmationHtml($existingBooking);

                wp_send_json([
                    'message'       => 'Booking has been confirmed',
                    'response_html' => $html,
                    'booking_hash'  => $existingBooking->hash
                ], 200);

            }, 10, 2);
        });

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

        $calendarEvent = CalendarSlot::query()->find($atts['id']);

        if (!$calendarEvent) {
            return '';
        }

        $calendar = $calendarEvent->calendar;

        if (!$calendar) {
            return 'Calendar not found';
        }

        $assetUrl = App::getInstance('url.assets');

        $localizeData = $this->getCalendarEventVars($calendar, $calendarEvent);
        $localizeData['disable_author'] = $atts['disable_author'] == 'yes';
        $localizeData['time_format'] = get_option('_fluent_booking_settings')['time_format'];;

        if (BookingFieldService::hasPhoneNumberField($localizeData['form_fields'])) {

            wp_enqueue_script('fluent-booking-phone-field', App::getInstance('url.assets') . 'public/js/phone-field.js', [], FLUENT_BOOKING_ASSETS_VERSION, true);

            add_action('fluent_booking/short_code_render', function () use ($assetUrl) {
                ?>
                <style>
                    .fcal_phone_wrapper .flag {
                        background: url(<?php echo $assetUrl.'images/flags_responsive.png' ?>) no-repeat;
                        background-size: 100%;
                    }
                </style>
                <?php
            });
        }

        wp_enqueue_script('fluent-booking-public', $assetUrl . 'public/js/app.js', [], FLUENT_BOOKING_ASSETS_VERSION, true);
        $this->loadGlobalVars();
        wp_localize_script(
            'fluent-booking-public',
            'fcal_public_vars_' . $calendar->id . '_' . $calendarEvent->id,
            $localizeData,
        );

        return App::make('view')->make('public.calendar', [
            'calenderEvent' => $calendarEvent
        ]);
    }

    public function handleReceiptShortcode($atts, $content)
    {
        if (!isset($_REQUEST['hash'])) {
            return 'Booking hash is missing!';
        }
        return (new ReceiptHelper())->getReceipt($_REQUEST['hash']);
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

            if (!$name) {
                $name = $currentUser->display_name;
            }

            $currentPerson = [
                'name'    => $name,
                'email'   => $currentUser->user_email,
                'user_id' => $currentUser->ID
            ];
        } else {
            // Check for url params
            if (isset($_REQUEST['invitee_name'])) {
                $currentPerson['name'] = sanitize_text_field($_REQUEST['invitee_name']);
            }

            if (isset($_REQUEST['invitee_email'])) {
                $email = sanitize_email($_REQUEST['invitee_email']);
                if (is_email($email)) {
                    $currentPerson['email'] = $email;
                }
            }
        }

        if (empty($currentPerson['email'])) {
            // Let's try to get from FluentCRM is exists
            if (defined('FLUENTCRM')) {
                $contactApi = FluentCrmApi('contacts');
                $contact = $contactApi->getCurrentContact();
                if ($contact) {
                    $currentPerson['email'] = $contact->email;
                    $currentPerson['name'] = $contact->full_name;
                }
            }
        }

        $globalSettings = Helper::getGlobalSettings();
        $startDay = Arr::get($globalSettings, 'administration.start_day', 'mon');

        $data = [
            'ajaxurl'        => admin_url('admin-ajax.php'),
            'timezones'      => DateTimeHelper::getFlatGroupedTimeZones(),
            'current_person' => $currentPerson,
            'start_day'      => $startDay,
            'i18'            => [
                'Timezone'             => __('Timezone', 'fluent-booking-pro'),
                'minutes'              => __('minutes', 'fluent-booking-pro'),
                'Enter Details'        => __('Enter Details', 'fluent-booking-pro'),
                'Payment Details'      => __('Payment Details', 'fluent-booking-pro'),
                'Total Payment'        => __('Total Payment', 'fluent-booking-pro'),
                'Pay Now'              => __('Pay Now', 'fluent-booking-pro'),
                'Confirm Payment'      => __('Confirm Payment', 'fluent-booking-pro'),
                'processing'           => __('Processing', 'fluent-booking-pro'),
                'Schedule Meeting'     => __('Schedule Meeting', 'fluent-booking-pro'),
                'Continue to Payments' => __('Continue to Payments', 'fluent-booking-pro')
            ]
        ];

        if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            $data['user_country'] = sanitize_text_field($_REQUEST['HTTP_CF_IPCOUNTRY']);
        }

        return apply_filters('fluent_calendar/global_booking_vars', $data);
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

        do_action('fluent_booking/starting_scheduling_ajax', $postedData);

        $rules = [
            'name'       => 'required',
            'email'      => 'required|email',
            'timezone'   => 'required',
            'start_date' => 'required'
        ];

        $messages = [
            'name.required'       => 'Please enter your name',
            'email.required'      => 'Please enter your email address',
            'email.email'         => 'Please enter provide a valid email address',
            'timezone.required'   => 'Please select timezone first',
            'start_date.required' => 'Please select a date and time',
        ];

        if ($calendarSlot->isPhoneRequired()) {
            $rules['phone_number'] = 'required';
            $messages['phone_number.required'] = __('Please provide your phone number', 'fluent-booking-pro');
        } else if ($calendarSlot->isAddressRequired()) {
            $rules['address'] = 'required';
            $messages['phone_number.required'] = __('Please provide your Address', 'fluent-booking-pro');
        } else if ($calendarSlot->isLocationFieldRequired()) {
            $rules['location_config.driver'] = 'required';
            $messages['location_config.driver'] = __('Please select location', 'fluent-booking-pro');
            $selectedLocationDriver = Arr::get($postedData, 'location_config.driver');
            // is user input required
            if (in_array($selectedLocationDriver, ['in_person_guest', 'phone_guest'])) {
                $rules['location_config.user_location_input'] = 'required';
                if ($selectedLocationDriver == 'in_person_guest') {
                    $messages['location_config.user_location_input.required'] = __('Please provide your address', 'fluent-booking-pro');
                } else {
                    $messages['location_config.user_location_input.required'] = __('Please provide your phone number', 'fluent-booking-pro');
                }
            }
        }

        $validationConfig = apply_filters('fluent_booking/schedule_validation_rules_data', [
            'rules'    => $rules,
            'messages' => $messages
        ], $postedData, $calendarSlot);

        $validator = $app->validator->make($postedData, $validationConfig['rules'], $validationConfig['messages']);
        if ($validator->validate()->fails()) {
            wp_send_json([
                'message' => 'Please fill up the required data',
                'errors'  => $validator->errors()
            ], 422);
            return;
        }

        $customFieldsData = BookingFieldService::getCustomFieldsData($postedData, $calendarSlot);
        $customFieldsData = apply_filters('fluent_booking/schedule_custom_field_data', $customFieldsData, $customFieldsData, $calendarSlot);

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
            'event_type'       => $calendarSlot->event_type,
        ];

        $selectedLocation = LocationService::getLocationDetails($calendarSlot, Arr::get($postedData, 'location_config', []), $postedData);
        if ($selectedLocation['type'] == 'phone_guest') {
            $bookingData['phone'] = $selectedLocation['description'];
        }

        $bookingData['location_details'] = $selectedLocation;

        if ($sourceUrl = Arr::get($postedData, 'source_url', '')) {
            $bookingData['source_url'] = sanitize_url($sourceUrl);
        }

        // Check if the time is available or not for this slot
        $timeSlotService = new TimeSlotService($calendarSlot->calendar, $calendarSlot);
        $isSpotAvailable = $timeSlotService->isSpotAvailable($startDateTime, $endDateTime);

        if (!$isSpotAvailable) {
            wp_send_json([
                'message' => __('This selected time slot is not available. Maybe someone booked the spot just a few seconds ago.', 'fluent-booking-pro')
            ], 422);
        }

        if (!empty($postedData['payment_method'])) {
            $customFieldsData['payment_method'] = $postedData['payment_method'];
        }

        do_action('fluent_calendar/before_creating_schedule', $bookingData, $postedData, $calendarSlot);

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
            'response_html' => $html,
            'booking_hash'  => $booking->hash
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

        $eventData = [
            'id'                 => $calendarEvent->id,
            'max_lookup_date'    => $calendarEvent->max_lookup_date,
            'min_lookup_date'    => $calendarEvent->min_lookup_date,
            'duration'           => $calendarEvent->duration,
            'title'              => $calendarEvent->title,
            'location_settings'  => $calendarEvent->location_settings,
            'location_icon_html' => $calendarEvent->location_icon_html,
            'description'        => $calendarEvent->description,
            'pre_selects'        => null,
            'settings'           => $calendarEvent->settings,
            'type'               => $calendarEvent->type,
        ];

        $paymentSettings = $calendarEvent->getMeta('payment_settings', []);

        if ($paymentSettings && Arr::get($paymentSettings, 'enabled') == 'yes') {
            $total = 0;
            foreach ($paymentSettings['items'] as $payment) {
                $total += (int)$payment['value'];
            }
            $currency = CurrenciesHelper::getCurrencySign();
            $eventData['total_payment'] = $calendarEvent->defaultPaymentIcon($currency, $total);
        } else {
            $eventData['total_payment'] = '';
        }

        $author = $calendar->getAuthorProfile(true);
        $author['name'] = $calendar->title;

        $eventVars = [
            'slot'           => $eventData,
            'author_profile' => $author,
            'form_fields'    => $formFields,
        ];

        $eventVars['form_fields'] = array_values($eventVars['form_fields']);

        return apply_filters('fluent_booking/public_event_vars', $eventVars, $calendarEvent);
    }

    public function ajaxHandleCancelMeeting()
    {
        $data = $_REQUEST;

        $meetingHash = Arr::get($_REQUEST, 'meeting_hash');

        $meeting = Booking::where('hash', $meetingHash)->first();

        if (!$meeting) {
            wp_send_json([
                'message' => __('Sorry! meeting could not be found', 'fluent-booking-pro')
            ], 422);
        }

        $message = sanitize_textarea_field(Arr::get($data, 'cancellation_reason', ''));

        if (!$message) {
            wp_send_json([
                'message' => __('Please provide a reason for cancellation', 'fluent-booking-pro')
            ], 422);
        }


        $result = $meeting->cancelMeeting($message, 'guest', get_current_user_id());

        if (is_wp_error($result)) {
            if (!wp_doing_ajax()) {
                wp_redirect($meeting->getConfirmationUrl());
                exit();
            }

            wp_send_json([
                'message' => $result->get_error_message()
            ], 422);
        }

        if (wp_doing_ajax()) {
            wp_send_json([
                'message' => __('Meeting has been cancelled', 'fluent-booking-pro')
            ], 200);
        }

        wp_redirect($meeting->getConfirmationUrl());
        exit;
    }
}
