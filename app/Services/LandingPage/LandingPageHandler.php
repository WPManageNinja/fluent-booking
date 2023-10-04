<?php

namespace FluentBooking\App\Services\LandingPage;

use FluentBooking\App\App;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\Helper;

class LandingPageHandler
{
    public function boot()
    {
        if (defined('FLUENT_BOOKING_LANDING_SLUG')) {
            add_action('template_redirect', [$this, 'handleSlugDefinedPage']);
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

        $user = get_user_by('slug', $authorSlug);

        if (!$user) {
            return;
        }

        // get the calendar
        $calendar = Calendar::where('user_id', $user->ID)->first();

        if (!$calendar) {
            return;
        }
        
        $this->renderCalendarView($calendar);
    }

    private function renderCalendarView($calendar)
    {
        global $wp;
        $settings = LandingPageHelper::getSettings($calendar, 'public');
        if ($settings['enabled'] != 'yes') {
            return;
        }

        $activeSlots = CalendarSlot::where('calendar_id', $calendar->id)
            ->where('status', 'active')
            ->get();

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

        status_header(200);
        $this->render('author_landing', $data);
        exit(200);
    }

    public function handleUrlParamsPage()
    {

    }

    private function render($file, $data)
    {

    }

    private function showErrorPage($title, $description = '')
    {
        die($title);
    }
}
