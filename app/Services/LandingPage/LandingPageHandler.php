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

        $onRescheduling = App::getInstance('request')->get('type') === 'reschedule';


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
        ];

        $jsVars = [
            'fcal_public_vars_' . $calendar->id . '_' . $calendarEvent->id => $eventVars,
            'fluentCalendarPublicVars' => (new FrontEndHandler())->getGlobalVars()
        ];

        if ($onRescheduling) {
            $onReschedulingData = [
                'on_rescheduling' => $onRescheduling,
                'existing_booking' => $existingBooking->toArray()
            ];
            $jsVars += $onReschedulingData;
            $data += $onReschedulingData;
        }
        $data['js_vars'] = $jsVars;


        if ($calendarEvent->type == 'paid') {
            $data['js_files'][] = 'https://js.stripe.com/v3/';
            $data['js_files'][] = $assetUrl . 'public/js/stripe-checkout.js';
        }

        $app = App::getInstance();

        status_header(200);
        if ($onRescheduling) {
            $data['on_rescheduling'] = true;
            $data['existing_booking'] = $existingBooking;
        }

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
        $this->renderBookingView($booking->calendar, $booking->calendar_event, $booking);
    }

}
