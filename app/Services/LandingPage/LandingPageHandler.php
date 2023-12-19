<?php

namespace FluentBooking\App\Services\LandingPage;

use FluentBooking\App\App;
use FluentBooking\App\Hooks\Handlers\FrontEndHandler;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\BookingFieldService;
use FluentBooking\App\Services\BookingService;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Support\Collection;

class LandingPageHandler
{
    public function boot()
    {
        if (defined('FLUENT_BOOKING_LANDING_SLUG')) {
            add_action('template_redirect', [$this, 'handleSlugDefinedPage'], 1);
        }

        if (isset($_GET['fluent-booking'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            add_action('init', [$this, 'handleUrlParamsPage'], 100);
        }
    }

    public function handleSlugDefinedPage()
    {
        global $wp;
        // remove starting / and end / from $uri
        $uri = trim($wp->request, '/');
        $urlParts = explode('/', $uri);

        if ($urlParts[0] != FLUENT_BOOKING_LANDING_SLUG || count($urlParts) < 2) {
            return;
        }

        $authorSlug = sanitize_text_field($urlParts[1]);

        $this->routeView($authorSlug, Arr::get($urlParts, 2, null));
    }

    public function handleUrlParamsPage()
    {
        $route = sanitize_text_field($_GET['fluent-booking']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if ($route == 'booking') {
            $this->handleAfterBookingPage();
            return;
        }

        if (empty($_REQUEST['host'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            do_action('fluent_booking/landing_page_route_' . $route, $_REQUEST);
            return;
        }

        $authorSlug = sanitize_text_field($_REQUEST['host']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $slotSlug = null;

        if (!empty($_REQUEST['event'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $slotSlug = sanitize_text_field($_REQUEST['event']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        }

        $this->routeView($authorSlug, $slotSlug);
    }

    public function routeView($authorSlug, $slotSlug = null)
    {
        $calendar = Calendar::where('slug', $authorSlug)->first();

        if (!$calendar) {
            return;
        }

        $sharingSettings = LandingPageHelper::getSettings($calendar);

        if (Arr::get($sharingSettings, 'enabled') != 'yes') {
            return '';
        }

        if ($slotSlug) {
            $slot = CalendarSlot::where('calendar_id', $calendar->id)
                ->where('slug', $slotSlug)
                ->first();
            if (!$slot) {
                return;
            }
            $this->renderBookingView($calendar, $slot);
        }

        $this->renderHostView($calendar);
    }

    private function renderHostView($calendar)
    {
        global $wp;
        $settings = LandingPageHelper::getSettings($calendar, 'public');

        $activeEvents = CalendarSlot::where('calendar_id', $calendar->id)
            ->where('status', 'active');

        if ($settings['show_type'] != 'all') {
            $activeEvents = $activeEvents->whereIn('id', $settings['enabled_slots']);
        }

        $activeEvents = $activeEvents->get();

        foreach ($activeEvents as $activeEvent) {
            $activeEvent->public_url = $activeEvent->getPublicUrl();
            if ($activeEvent->description) {
                $activeEvent->description = Helper::excerpt($activeEvent->description);
            } else {
                $activeEvent->description = sprintf(__('Book a meeting with me for %d minutes', 'fluent-booking-pro'), $activeEvent->duration);
            }

            if (Arr::isTrue($activeEvent->settings, 'multi_duration.enabled')) {
                $activeEvent->description = __('Choose your duration and book a meeting with me', 'fluent-booking-pro');
                $activeEvent->duration = Arr::get($activeEvent->settings, 'multi_duration.available_durations');
            }
        }

        $metaDescription = Helper::excerpt($calendar->description);

        $calendar->description = wpautop($calendar->description);

        $authorProfile = $calendar->getAuthorProfile(true);

        $globalVars = (new FrontEndHandler())->getGlobalVars();

        $currentUrl = home_url($wp->request);

        $globalVars['is_landing_page'] = true;
        $globalVars['is_pretty_url'] = defined('FLUENT_BOOKING_LANDING_SLUG');
        $globalVars['base_url'] = rtrim($currentUrl, '/');

        $jsVars = [
            'fluentCalendarPublicVars' => $globalVars,
            'fcal_landing_page' => true
        ];

        $extraJsFiles = [];

        foreach ($activeEvents as $activeEvent) {
            $event = clone $activeEvent;
            $vars = (new FrontEndHandler())->getCalendarEventVars($calendar, $event);
            $extraJs = $this->getEventLandingExtraJsFiles($vars['form_fields'], $event);
            if ($extraJs) {
                $vars['lazy_js_files'] = $extraJs;
            }

            $jsVars['fcal_public_vars_' . $calendar->id . '_' . $activeEvent->id] = $vars;
        }

        $assetUrl = App::getInstance('url.assets');
        $data = [
            'calendar'    => $calendar,
            'events'      => $activeEvents,
            'author'      => $authorProfile,
            'title'       => $authorProfile['name'],
            'description' => $metaDescription,
            'url'         => home_url($wp->request),
            'css_files'   => [
                App::getInstance('url.assets') . 'public/saas.css'
            ],
            'js_files'    => [
                'fluent-booking-public-js' => $assetUrl . 'public/js/app.js',
            ],
            'js_vars'     => $jsVars,
            'header_js_files' => [
                'fluent_booking_team_app-js' => $assetUrl. 'public/js/team_app.js'
            ]
        ];

        if ($extraJsFiles) {
            $extraJsFiles = array_unique($extraJsFiles);
            $data['js_files'] = array_merge($data['js_files'], $extraJsFiles);
            add_action('fluent_booking/main_landing', function () use ($assetUrl) {
                ?>
                <style>
                    .fcal_phone_wrapper .flag {
                        background: url(<?php echo esc_url($assetUrl.'images/flags_responsive.png'); ?>) no-repeat;
                        background-size: 100%;
                    }
                </style>
                <?php
            });
        }


        $app = App::getInstance();
        status_header(200);
        $app->view->render('landing.author_landing', $data);
        exit(200);
    }

    private function renderBookingView($calendar, $calendarEvent, $existingBooking = null)
    {
        $settings = LandingPageHelper::getSettings($calendar, 'public');
        if ($settings['show_type'] != 'all') {
            if (!in_array($calendarEvent->id, $settings['enabled_slots'])) {
                return '';
            }
        }

        global $wp;

        $calendarEvent->max_lookup_date = $calendarEvent->getMaxLookUpDate();
        $calendarEvent->min_lookup_date = $calendarEvent->getMinLookUpDate();

        if (!empty($_REQUEST['booking_id'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $bookingHash = sanitize_text_field($_REQUEST['booking_id']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $booking = Booking::where('hash', $bookingHash)
                ->where('event_id', $calendarEvent->id)
                ->first();
            if ($booking) {
                $this->showBookingConfimationPage($booking, $calendarEvent);
            }
        }

        $authorProfile = $calendarEvent->getAuthorProfile(true);

        $calendarEvent->pre_selects = false;

        if (gmdate('m') != gmdate('m', strtotime($calendarEvent->min_lookup_date))) { // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            $calendarEvent->pre_selects = [
                'month' => gmdate('m', strtotime($calendarEvent->min_lookup_date)), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                'year'  => gmdate('Y', strtotime($calendarEvent->min_lookup_date)) // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            ];
        }

        $assetUrl = App::getInstance('url.assets');

        $eventVars = (new FrontEndHandler())->getCalendarEventVars($calendar, $calendarEvent);

        $data = [
            'calendar'       => $calendar,
            'calendar_event' => $calendarEvent,
            'author'         => $authorProfile,
            'title'          => $calendarEvent->title . ' ' . __('with', 'fluent-booking-pro') . ' ' . $authorProfile['name'],
            'description'    => substr(strip_shortcodes(wp_strip_all_tags(str_replace(PHP_EOL, ' ', $calendarEvent->description))), 0, 300) . '...',
            'url'            => home_url($wp->request),
            'css_files'      => [
                $assetUrl . 'public/saas.css'
            ],
            'js_files'       => [
                'fluent-booking-public-js' => $assetUrl . 'public/js/app.js',
            ],
            'js_vars'        => [
                'fluentCalendarPublicVars'                                     => (new FrontEndHandler())->getGlobalVars(),
                'fcal_public_vars_' . $calendar->id . '_' . $calendarEvent->id => $eventVars,
            ]
        ];


        $extraJs = $this->getEventLandingExtraJsFiles($eventVars['form_fields'], $calendarEvent);

        if ($extraJs) {
            $data['js_files'] = wp_parse_args($data['js_files'], $extraJs);
            add_action('fluent_booking/author_landing_head', function () use ($assetUrl) {
                ?>
                <style>
                    .fcal_phone_wrapper .flag {
                        background: url(<?php echo esc_url($assetUrl.'images/flags_responsive.png'); ?>) no-repeat;
                        background-size: 100%;
                    }
                </style>
                <?php
            });
        }

        $data = apply_filters('fluent_booking/event_landing_page_vars', $data, $calendar, $calendarEvent, $existingBooking);

        $app = App::getInstance();

        status_header(200);
        $app->view->render('landing.booking', $data);
        exit(200);
    }

    private function showBookingConfimationPage($booking, $actionType = 'confirmation')
    {
        $validActions = [
            'confirmation',
            'cancel',
            'reschedule'
        ];

        if (!in_array($actionType, $validActions)) {
            $actionType = 'confirmation';
        }


        if ($actionType == 'reschedule') {
            $this->handleRescheduleView($booking);
        }

        if ($actionType == 'confirmation' && !empty($_REQUEST['ics']) && $_REQUEST['ics'] == 'download') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $icsText = BookingService::generateBookingICS($booking);
            // Output the ICS text
            header('Content-Type: text/calendar; charset=utf-8');
            header('Content-Disposition: attachment; filename=event.ics');
            echo $icsText; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            die();
        }

        $calendarEvent = $booking->calendar_event;
        global $wp;
        $responseHtml = BookingService::getBookingConfirmationHtml($booking, $actionType);

        $authorProfile = $calendarEvent->getAuthorProfile(true);

        $data = [
            'title'       => __('Confirmation: ', 'fluent-booking-pro') . $calendarEvent->title . ' ' . __('with', 'fluent-booking-pro') . ' ' . $authorProfile['name'],
            'body'        => $responseHtml,
            'description' => substr(strip_shortcodes(wp_strip_all_tags(str_replace(PHP_EOL, ' ', $calendarEvent->description))), 0, 300) . '...',
            'css_files'   => [
                App::getInstance('url.assets') . 'public/saas_public.css'
            ],
            'js_files'    => [],
            'js_vars'     => [],
            'author'      => $authorProfile,
            'slot'        => $calendarEvent,
            'url'         => home_url($wp->request),
            'action_type' => $actionType,
            'theme'          => Arr::get(get_option('_fluent_booking_settings'), 'theme','system-default')
        ];

        if ($actionType == 'cancel') {
            $data['js_files']['fluent-booking-public-manage-meeting-js'] = App::getInstance('url.assets') . 'public/js/public-manage-meeting.js';
        }

        $app = App::getInstance();
        status_header(200);
        $app->view->render('landing.confirmation_page', $data);
        exit(200);
    }

    private function handleAfterBookingPage()
    {
        $bookingHash = sanitize_text_field(Arr::get($_REQUEST, 'meeting_hash')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if (!$bookingHash) {
            return;
        }

        $booking = Booking::where('hash', $bookingHash)->first();

        if (!$booking) {
            return;
        }

        $type = Arr::get($_REQUEST, 'type', 'confirmation'); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $this->showBookingConfimationPage($booking, $type);
    }

    private function handleRescheduleView(Booking $booking)
    {
        add_filter('fluent_booking/public_event_vars', function ($eventVars) use ($booking) {
            $onlyFields = [
                'name', 'email'
            ];

            $formFields = $eventVars['form_fields'];

            $formFields = Collection::make($formFields)->filter(function ($field) use ($onlyFields) {
                return in_array($field['name'], $onlyFields);
            })->map(function ($item) {
                $item['disabled'] = true;
                return $item;
            })->toArray();

            $formFields[] = [
                'type'        => 'textarea',
                'name'        => '_rescheduling_reason',
                'label'       => __('Reason of rescheduling', 'fluent-booking-pro'),
                'placeholder' => __('Rescheduling Reason', 'fluent-booking-pro'),
                'required'    => true,
                'disabled'    => false,
                'enabled'     => true
            ];

            $formFields[] = [
                'type'    => 'hidden',
                'name'    => 'rescheduling_hash',
                'enabled' => true
            ];

            $eventVars['form_fields'] = array_values($formFields);
            unset($eventVars['payment_items']);
            unset($eventVars['payment_methods']);

            return $eventVars;
        }, 10, 1);

        add_filter('fluent_calendar/global_booking_vars', function ($vars) use ($booking) {
            $vars['current_person'] = [
                'name'              => trim($booking->first_name . ' ' . $booking->last_name),
                'email'             => $booking->email,
                'rescheduling_hash' => $booking->hash
            ];

            return $vars;
        }, 10, 1);

        add_filter('fluent_booking/public_event_vars', function ($vars, $calendarEvent) {
            $vars['i18']['Schedule_Meeting'] = __('Confirm Reschedule', 'fluent-booking-pro');
            $vars['i18']['Continue_to_Payments'] = __('Confirm Reschedule', 'fluent-booking-pro');
            $vars['i18']['Confirm_Payment'] = __('Confirm Reschedule', 'fluent-booking-pro');
            return $vars;
        }, 10, 2);

        add_action('fluent_booking/before_calendar_event_landing_page', function ($calendarEvent) use ($booking) {
            ?>
            <div class="fcal_rescheduling_wrap">
                <h3> <?php esc_html_e('You are rescheduling the booking: ', 'fluent-booking-pro');
                    echo wp_kses_post($booking->getFullBookingDateTimeText($booking->person_time_zone, true)); ?>
                    (<?php echo esc_html($booking->person_time_zone); ?>) </h3>
            </div>
            <?php
        }, 10, 1);

        add_action('fluent_booking/author_landing_head', function () {
            ?>
            <style>
                .fluent_booking_app {
                    margin-top: 0 !important;
                    padding-top: 0 !important;
                }
            </style>
            <?php
        });

        $this->renderBookingView($booking->calendar, $booking->calendar_event, $booking);
    }

    public function getEventLandingExtraJsFiles($formFields, $calendarEvent)
    {
        $files = [];
        $assetUrl = App::getInstance('url.assets');

        if (BookingFieldService::hasPhoneNumberField($formFields)) {
            $files['fluent-booking-phone-field-js'] = $assetUrl . 'public/js/phone-field.js';
        }

        if ($calendarEvent->type == 'paid') {
            $files['fluent-booking-checkout-sdk-stripe-js'] = 'https://js.stripe.com/v3/';
            $files['fluent-booking-checkout-handler-stripe-js'] = $assetUrl . 'public/js/stripe-checkout.js';
        }

        return $files;
    }
}
