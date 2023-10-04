<?php

namespace FluentBooking\App\Services\LandingPage;

use FluentBooking\App\App;
use FluentBooking\App\Hooks\Handlers\FrontEndHandler;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\BookingService;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class LandingPageHandler
{
    public function boot()
    {
        if (defined('FLUENT_BOOKING_LANDING_SLUG')) {
            add_action('template_redirect', [$this, 'handleSlugDefinedPage'], 1);
        } else if (isset($_GET['fluent-booking']) && $_GET['fluent-booking'] == 'calendar') {
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
        $user = get_user_by('slug', $authorSlug);
        if (!$user) {
            return;
        }

        // get the calendar
        $calendar = Calendar::where('user_id', $user->ID)->first();

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

        $activeSlots = CalendarSlot::where('calendar_id', $calendar->id)
            ->where('status', 'active');

        if ($settings['show_type'] != 'all') {
            $activeSlots = $activeSlots->whereIn('id', $settings['enabled_slots']);
        }

        $activeSlots = $activeSlots->get();

        foreach ($activeSlots as $activeSlot) {
            $activeSlot->public_url = $activeSlot->getPublicUrl();
            if ($activeSlot->description) {
                $activeSlot->description = Helper::excerpt($activeSlot->description);
            } else {
                $activeSlot->description = sprintf('Book a meeting with me for %d minutes', $activeSlot->duration);
            }
        }

        $metaDescription = Helper::excerpt($calendar->description);

        $calendar->description = wpautop($calendar->description);

        $authorProfile = $calendar->getAuthorProfile(true);

        $data = [
            'calendar'    => $calendar,
            'slots'       => $activeSlots,
            'author'      => $authorProfile,
            'title'       => $authorProfile['name'],
            'description' => $metaDescription,
            'url'         => home_url($wp->request),
            'css_files'   => [
                App::getInstance('url.assets') . 'public/saas.css'
            ],
        ];

        $app = App::getInstance();
        status_header(200);
        $app->view->render('landing.author_landing', $data);
        exit(200);
    }

    private function renderBookingView($calendar, $slot)
    {
        $settings = LandingPageHelper::getSettings($calendar, 'public');
        if ($settings['show_type'] != 'all') {
            if (!in_array($slot->id, $settings['enabled_slots'])) {
                return '';
            }
        }

        global $wp;

        $slot->max_lookup_date = $slot->getMaxLookUpDate();
        $slot->min_lookup_date = $slot->getMinLookUpDate();

        if (!empty($_REQUEST['booking_id'])) {
            $bookingHash = sanitize_text_field($_REQUEST['booking_id']);
            $booking = Booking::where('hash', $bookingHash)
                ->where('slot_id', $slot->id)
                ->first();
            if ($booking) {
                $this->showBookingConfimationPage($booking, $slot);
            }
        }

        $formFields = BookingService::getBookingFields($slot);

        $authorProfile = $slot->getAuthorProfile(true);

        $slot->location_settings = (object)[];
        $slot->description = wpautop($slot->description);

        $slot->pre_selects = false;

        if (date('m') != date('m', strtotime($slot->min_lookup_date))) {
            $slot->pre_selects = [
                'month' => date('m', strtotime($slot->min_lookup_date)),
                'year'  => date('Y', strtotime($slot->min_lookup_date))
            ];
        }

        $data = [
            'calendar'    => $calendar,
            'slot'        => $slot,
            'author'      => $authorProfile,
            'title'       => $slot->title . ' with ' . $authorProfile['name'],
            'description' => substr(strip_shortcodes(strip_tags(str_replace(PHP_EOL, ' ', $slot->description))), 0, 300) . '...',
            'url'         => home_url($wp->request),
            'css_files'   => [
                App::getInstance('url.assets') . 'public/saas.css'
            ],
            'js_files'    => [
                App::getInstance('url.assets') . 'public/js/app.js'
            ],
            'js_vars'     => [
                'fcal_public_vars_' . $calendar->id . '_' . $slot->id => [
                    'slot'           => $slot,
                    'calendar'       => $calendar,
                    'author_profile' => $authorProfile,
                    'form_fields'    => $formFields
                ],
                'fluentCalendarPublicVars'                            => (new FrontEndHandler())->getGlobalVars()
            ]
        ];

        $app = App::getInstance();

        status_header(200);
        $app->view->render('landing.booking', $data);
        exit(200);
    }


    private function showBookingConfimationPage($booking, $slot)
    {
        global $wp;
        $responseHtml = BookingService::getBookingConfirmationHtml($booking, $slot, true);

        $authorProfile = $slot->getAuthorProfile(true);

        $data = [
            'title'       => 'Confirmation: ' . $slot->title . ' with ' . $authorProfile['name'],
            'body'        => $responseHtml,
            'description' => substr(strip_shortcodes(strip_tags(str_replace(PHP_EOL, ' ', $slot->description))), 0, 300) . '...',
            'css_files'   => [
                App::getInstance('url.assets') . 'public/saas_public.css'
            ],
            'js_files'    => [],
            'js_vars'     => [],
            'author'      => $authorProfile,
            'slot'        => $slot,
            'url'         => home_url($wp->request),
        ];

        $app = App::getInstance();
        status_header(200);
        $app->view->render('landing.confirmation_page', $data);
        exit(200);
    }


}
