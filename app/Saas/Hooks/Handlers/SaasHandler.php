<?php

namespace FluentCalendar\App\Saas\Hooks\Handlers;

use FluentCalendar\App\App;
use FluentCalendar\App\Models\Booking;
use FluentCalendar\App\Models\Calendar;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Services\BookingService;
use FluentCalendar\App\Services\DateTimeHelper;

class SaasHandler
{
    public function register()
    {
      //  add_action('template_redirect', [$this, 'maybeCalendarView'], 1);

        add_action('init', [$this, 'registerDashboardRoute'], 1);

    }

    public function registerDashboardRoute()
    {
        if(!defined('FLUENT_ADMIN_PAGE_ID')) {
            return;
        }

      //  add_rewrite_tag( '%board_hash%', '([^&]+)');
        add_rewrite_tag( '%is_cal_page%', '([^&]+)');

        //add rewrite rule that matches /boards
        add_rewrite_rule('^calendar/?','index.php?page_id='.FLUENT_ADMIN_PAGE_ID.'&is_cal_page=1','top');

        //add endpoint, in this case 'blog' to satisfy our rewrite rule /blog, /blog/page/ etc..
        add_rewrite_endpoint( 'calendar', EP_PERMALINK | EP_PAGES );
    }

    public function maybeCalendarView()
    {
        global $wp;
        // remove starting / and end / from $uri
        $uri = trim($wp->request, '/');
        $urlParts = explode('/', $uri);
        if (count($urlParts) < 2) {
            return;
        }

        $slug = $urlParts[0];
        $calendar = Calendar::where('slug', $slug)->first();

        if (!$calendar) {
            return;
        }

        $slot = CalendarSlot::where('calendar_id', $calendar->id)->where('slug', $urlParts[1])->first();

        if (!$slot) {
            return;
        }

        if(!empty($_REQUEST['booking_id'])) {
            $bookingHash = sanitize_text_field($_REQUEST['booking_id']);
            $booking = Booking::where('hash', $bookingHash)
                ->where('slot_id', $slot->id)
                ->first();
            if($booking) {
                $this->showBookingConfimationPage($booking, $slot);
            }
        }

        $formFields = BookingService::getBookingFields($slot);

        $authorProfile = $slot->getAuthorProfile(true);

        $slot->location_settings = (object)[];
        $slot->description = wpautop($slot->description);

        $data = [
            'calendar' => $calendar,
            'slot' => $slot,
            'author' => $authorProfile,
            'title' => $slot->title . ' with ' . $authorProfile['name'],
            'description' => substr(strip_shortcodes( strip_tags( str_replace(PHP_EOL, ' ', $slot->description) ) ), 0, 300).'...',
            'url' => home_url($wp->request),
            'css_files' => [
                App::getInstance('url.assets') . 'public/saas.css'
            ],
            'js_files' => [
                App::getInstance('url.assets') . 'public/js/app.js'
            ],
            'js_vars' => [
                'fcal_public_vars_'.$calendar->id . '_'.$slot->id => [
                    'slot'           => $slot,
                    'calendar'       => $calendar,
                    'author_profile' => $authorProfile,
                    'form_fields' => $formFields
                ],
                'fluentCalendarPublicVars' => $this->getGlobalVars()
            ]
        ];

        status_header( 200 );
        $this->render('booking', $data);
        exit(200);
    }

    private function showBookingConfimationPage($booking, $slot)
    {
        global $wp;
        $responseHtml = BookingService::getBookingConfirmationHtml($booking, $slot, true);

        $authorProfile = $slot->getAuthorProfile(true);

        status_header( 200 );
        $this->render('confirmation_page', [
            'title' => 'Confirmation: '.$slot->title . ' with ' . $authorProfile['name'],
            'body' => $responseHtml,
            'description' => substr(strip_shortcodes( strip_tags( str_replace(PHP_EOL, ' ', $slot->description) ) ), 0, 300).'...',
            'css_files' => [
                App::getInstance('url.assets') . 'public/saas_public.css'
            ],
            'js_files' => [],
            'js_vars' => [],
            'author' => $authorProfile,
            'slot' => $slot,
            'url' => home_url($wp->request),
        ]);
        exit(200);

    }

    private function getGlobalVars()
    {

        $config = App::make('config');
        $ns = $config->get('app.rest_namespace');
        $ver = $config->get('app.rest_version');

        $rest = [
            'base_url'  => esc_url_raw(rest_url()),
            'url'       => rest_url($ns . '/' . $ver).'/public',
            'nonce'     => wp_create_nonce('wp_rest'),
            'namespace' => $ns,
            'version'   => $ver
        ];

        $currentPerson = [
            'name' => '',
            'email' => ''
        ];

        if(is_user_logged_in()) {
            $currentUser = wp_get_current_user();
            $name = trim($currentUser->first_name . ' ' . $currentUser->last_name);
            $currentPerson = [
                'name' => $name ? $name : $currentUser->display_name,
                'email' => $currentUser->user_email,
                'user_id' => $currentUser->ID
            ];
        }

        return [
            'rest' => $rest,
            'timezones' => DateTimeHelper::getFlatGroupedTimeZones(),
            'current_person' => $currentPerson
        ];
    }


    public function render($file, $data = [])
    {
        ob_start();
        extract($data, EXTR_SKIP);

        include FLUENT_CALENDAR_DIR . 'app/Saas/Views/' . $file . '.php';
    }

}
