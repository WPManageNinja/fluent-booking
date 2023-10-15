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

        if (isset($_GET['fluent-booking'])) {
            add_action('init', [$this, 'handleUrlParamsPage']);
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
        $route = sanitize_text_field($_GET['fluent-booking']);


        if ($route == 'booking') {
            $this->handleAfterBookingPage();
            return;
        }

        if (empty($_REQUEST['host'])) {
            return;
        }

        $authorSlug = sanitize_text_field($_REQUEST['host']);

        $slotSlug = null;

        if (!empty($_REQUEST['event'])) {
            $slotSlug = sanitize_text_field($_REQUEST['event']);
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

        $this->renderCalendarView($calendar);
    }

    private function renderCalendarView($calendar)
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
                $activeEvent->description = sprintf('Book a meeting with me for %d minutes', $activeEvent->duration);
            }
        }

        $metaDescription = Helper::excerpt($calendar->description);

        $calendar->description = wpautop($calendar->description);

        $authorProfile = $calendar->getAuthorProfile(true);

        $data = [
            'calendar' => $calendar,
            'events' => $activeEvents,
            'author' => $authorProfile,
            'title' => $authorProfile['name'],
            'description' => $metaDescription,
            'url' => home_url($wp->request),
            'css_files' => [
                App::getInstance('url.assets') . 'public/saas.css'
            ],
        ];

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

        if (!empty($_REQUEST['booking_id'])) {
            $bookingHash = sanitize_text_field($_REQUEST['booking_id']);
            $booking = Booking::where('hash', $bookingHash)
                ->where('event_id', $calendarEvent->id)
                ->first();
            if ($booking) {
                $this->showBookingConfimationPage($booking, $calendarEvent);
            }
        }

        $authorProfile = $calendarEvent->getAuthorProfile(true);

        $calendarEvent->pre_selects = false;

        if (date('m') != date('m', strtotime($calendarEvent->min_lookup_date))) {
            $calendarEvent->pre_selects = [
                'month' => date('m', strtotime($calendarEvent->min_lookup_date)),
                'year' => date('Y', strtotime($calendarEvent->min_lookup_date))
            ];
        }

        $assetUrl = App::getInstance('url.assets');

        $eventVars = (new FrontEndHandler())->getCalendarEventVars($calendar, $calendarEvent);


        $data = [
            'calendar' => $calendar,
            'calendar_event' => $calendarEvent,
            'author' => $authorProfile,
            'title' => $calendarEvent->title . ' with ' . $authorProfile['name'],
            'description' => substr(strip_shortcodes(strip_tags(str_replace(PHP_EOL, ' ', $calendarEvent->description))), 0, 300) . '...',
            'url' => home_url($wp->request),
            'css_files' => [
                $assetUrl . 'public/saas.css'
            ],
            'js_files' => [
                includes_url('js/jquery/jquery.min.js'),
                $assetUrl . 'public/js/app.js',
            ],
            'js_vars' => [
                'fcal_public_vars_' . $calendar->id . '_' . $calendarEvent->id => $eventVars,
                'fluentCalendarPublicVars' => (new FrontEndHandler())->getGlobalVars()
            ]
        ];

        if ($calendarEvent->type == 'paid') {
            $data['js_files'][] = 'https://js.stripe.com/v3/';
            $data['js_files'][] = $assetUrl . 'public/js/stripe-checkout.js';
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

        if ($actionType == 'confirmation' && !empty($_REQUEST['ics']) && $_REQUEST['ics'] == 'download') {
            $icsText = BookingService::generateBookingICS($booking);
            // Output the ICS text
            header('Content-Type: text/calendar; charset=utf-8');
            header('Content-Disposition: attachment; filename=event.ics');
            echo $icsText;
        }

        $calendarEvent = $booking->calendar_event;
        global $wp;
        $responseHtml = BookingService::getBookingConfirmationHtml($booking, $actionType);

        $authorProfile = $calendarEvent->getAuthorProfile(true);

        $data = [
            'title' => 'Confirmation: ' . $calendarEvent->title . ' with ' . $authorProfile['name'],
            'body' => $responseHtml,
            'description' => substr(strip_shortcodes(strip_tags(str_replace(PHP_EOL, ' ', $calendarEvent->description))), 0, 300) . '...',
            'css_files' => [
                App::getInstance('url.assets') . 'public/saas_public.css'
            ],
            'js_files' => [],
            'js_vars' => [],
            'author' => $authorProfile,
            'slot' => $calendarEvent,
            'url' => home_url($wp->request),
            'action_type' => $actionType
        ];

        if ($actionType == 'cancel') {
            $data['js_files'][] = App::getInstance('url.assets') . 'public/js/public-manage-meeting.js';
        }

        $app = App::getInstance();
        status_header(200);
        $app->view->render('landing.confirmation_page', $data);
        exit(200);
    }

    private function handleAfterBookingPage()
    {
        $bookingHash = sanitize_text_field($_REQUEST['meeting_hash']);
        $booking = Booking::where('hash', $bookingHash)->first();

        if (!$booking) {
            return;
        }

        $type = Arr::get($_REQUEST, 'type', 'confirmation');

        $this->showBookingConfimationPage($booking, $type);
    }

    private function handleRescheduleView(Booking $booking)
    {
        add_filter('fluent_calendar_public_event_vars', function ($eventVars) use ($booking) {
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
                'type' => 'textarea',
                'name' => '_rescheduling_reason',
                'label' => __('Reason of rescheduling', 'fluent-booking'),
                'placeholder' => __('Rescheduling Reason', 'fluent-booking'),
                'required' => true,
                'disabled' => false,
                'enabled' => true
            ];

            $formFields[] = [
                'type' => 'hidden',
                'name' => 'rescheduling_hash',
                'enabled' => true
            ];

            $eventVars['form_fields'] = array_values($formFields);
            unset($eventVars['payment_items']);
            unset($eventVars['payment_methods']);

            return $eventVars;
        }, 10, 1);

        add_filter('fluent_calendar/global_booking_vars', function ($vars) use ($booking) {
            $vars['current_person'] = [
                'name' => trim($booking->first_name .' '.$booking->last_name),
                'email' => $booking->email,
                'rescheduling_hash' => $booking->hash
            ];

            return $vars;
        });

        add_action('fluent_booking/before_calendar_event_landing_page', function ($calendarEvent) use ($booking) {
            ?>
            <div class="fcal_rescheduling_wrap">
                <h3>You are rescheduling the booking: <?php echo $booking->getFullBookingDateTimeText($booking->person_time_zone, true); ?> (<?php echo $booking->person_time_zone; ?>) </h3>
            </div>
            <?php
        });

        $this->renderBookingView($booking->calendar, $booking->calendar_event, $booking);
    }
}
