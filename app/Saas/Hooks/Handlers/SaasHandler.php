<?php

namespace FluentCalendar\App\Saas\Hooks\Handlers;

use FluentCalendar\App\App;
use FluentCalendar\App\Models\Booking;
use FluentCalendar\App\Models\Calendar;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Services\BookingService;
use FluentCalendar\App\Services\DateTimeHelper;
use FluentCalendar\App\Services\Helper;

class SaasHandler
{
    public function register()
    {
        add_action('template_redirect', [$this, 'maybeCalendarView'], 1);
    }

    public function maybeCalendarView()
    {
        global $wp;
        // remove starting / and end / from $uri
        $uri = trim($wp->request, '/');
        $urlParts = explode('/', $uri);

        if ($urlParts[0] == 'calendar') {
            $this->renderDashboard();
        }

        if (count($urlParts) < 2) {
            $calendar = Calendar::where('slug', $urlParts[0])->first();
            if($calendar) {
                $this->renderCalendarView($calendar);
            }
            return;
        }

        $this->maybeRenderBookingView($urlParts);
    }

    private function renderCalendarView($calendar)
    {
        global $wp;

        if($calendar->visibility != 'public') {
            $this->showErrorPage('Invalid Calendar URL', 'This calendar is not public');
        }

        $activeSlots = CalendarSlot::where('calendar_id', $calendar->id)
            ->where('status', 'active')
            ->get();

        foreach ($activeSlots as $activeSlot) {
            $activeSlot->public_url = site_url($calendar->slug . '/' . $activeSlot->slug);
            $activeSlot->description = Helper::excerpt($activeSlot->description);
        }

        $metaDescription = Helper::excerpt($calendar->description);

        $calendar->description = wpautop($calendar->description);

        $authorProfile = $calendar->getAuthorProfile(true);

        $data = [
            'calendar' => $calendar,
            'slots' => $activeSlots,
            'author' => $authorProfile,
            'title' => $authorProfile['name'],
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


    private function maybeRenderBookingView($urlParts)
    {
        global $wp;
        $slug = sanitize_text_field($urlParts[0]);
        $calendar = Calendar::where('slug', $slug)->first();

        if (!$calendar) {
            return;
        }

        $slot = CalendarSlot::where('calendar_id', $calendar->id)
            ->where('slug', $urlParts[1])
            ->first();

        if (!$slot) {
            $this->showErrorPage('404', 'This URL is not valid');
            return;
        }

        if($slot->status != 'active') {
            $message = '<p>Sorry, this host is not accepting any new bookings at the moment.</p>';
            if($slot->user_id == get_current_user_id()) {
                $message .= '<p>Looks like you are the owner of this calendar event. To enable this schedule event please go to your events dashboard and enable this.</p>';
            }
            $this->showErrorPage('This URL is not valid', $message);
            return;
        }

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

        if (isset($_GET['month'])) {
            $selectedMonth = sanitize_text_field($_GET['month']);
            $selectedMonth = explode('-', $selectedMonth);
            if (count($selectedMonth) == 2) {
                if ($selectedMonth[1] < 13 && $selectedMonth[1] > 0 && is_numeric($selectedMonth[0]) && $selectedMonth[0] >= date('Y')) {
                    $slot->pre_selects = [
                        'month' => $selectedMonth[1],
                        'year'  => (int)$selectedMonth[0]
                    ];
                }

            }
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
                'fluentCalendarPublicVars'                            => $this->getGlobalVars()
            ]
        ];

        status_header(200);
        $this->render('booking', $data);
        exit(200);
    }

    private function showBookingConfimationPage($booking, $slot)
    {
        global $wp;
        $responseHtml = BookingService::getBookingConfirmationHtml($booking, $slot, true);

        $authorProfile = $slot->getAuthorProfile(true);

        status_header(200);
        $this->render('confirmation_page', [
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
            'url'       => rest_url($ns . '/' . $ver) . '/public',
            'nonce'     => wp_create_nonce('wp_rest'),
            'namespace' => $ns,
            'version'   => $ver
        ];

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
            'rest'           => $rest,
            'timezones'      => DateTimeHelper::getFlatGroupedTimeZones(),
            'current_person' => $currentPerson
        ];
    }

    public function renderDashboard()
    {

        $userId = get_current_user_id();

        if (!$userId) {
            wp_redirect(site_url('login'));
            exit();
        }

        $config = App::getInstance('config');

        $name = 'ConvertLeap';

        $slug = $config->get('app.slug');

        $baseUrl = Helper::getAppBaseUrl();

        if ($this->isNew()) {
            $menuItems = [
                [
                    'key'       => 'dashboard',
                    'label'     => __('Getting Started', 'fluent-calendar'),
                    'permalink' => $baseUrl
                ],
            ];
        } else {
            $menuItems = [
                [
                    'key'       => 'dashboard',
                    'label'     => __('Dashboard', 'fluent-calendar'),
                    'permalink' => $baseUrl
                ],
                [
                    'key'       => 'calendars',
                    'label'     => __('Booking Types', 'fluent-calendar'),
                    'permalink' => $baseUrl . 'calendars'
                ],
                [
                    'key'       => 'scheduled_events',
                    'label'     => __('Scheduled Meetings', 'fluent-calendar'),
                    'permalink' => $baseUrl . 'scheduled-events?period=upcoming&author=me'
                ]
            ];
        }

        $app = App::getInstance();
        $assets = $app['url.assets'];

        $body = App::make('view')->make('admin.menu', [
            'name'      => $name,
            'slug'      => $slug,
            'menuItems' => $menuItems,
            'baseUrl'   => $baseUrl,
            'logo'      => $assets . 'images/logo.svg',
            'rightItems' => [
                [
                    'key' => 'logout',
                    'label' => __('Logout', 'fluent-calendar'),
                    'permalink'   => wp_logout_url(site_url())
                ]
            ]
        ]);


        $calendar = Calendar::where('user_id', get_current_user_id())->first();

        if ($calendar) {
            $authorProfile = $calendar->getAuthorProfile(true);
        } else {
            $authorProfile = false;
        }


        $appVars = (new \FluentCalendar\App\Hooks\Handlers\AdminMenuHandler)->getDashboardVars($app);

        $appVars['name'] = 'ConvertLeap';

        status_header(200);
        $this->render('dashboard_app', [
            'title'       => $name,
            'body'        => $body,
            'description' => '',
            'author'      => $authorProfile,
            'url'         => $baseUrl,
            'css_files'   => [
                $assets . 'admin/admin.css',
                $assets . 'public/saas_admin.css'
            ],
            'js_vars'     => [
                'fluentFrameworkAdmin' => $appVars
            ],
            'js_files'    => [
                //'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js',
                site_url('wp-includes/js/jquery/jquery.min.js'),
                $assets . 'admin/app.js'
            ]
        ]);
        exit(200);

    }

    protected function isNew()
    {
        $userId = get_current_user_id();
        return !Calendar::where('user_id', $userId)->first();
    }

    public function render($file, $data = [])
    {
        ob_start();
        extract($data, EXTR_SKIP);

        include FLUENT_CALENDAR_DIR . 'app/Saas/Views/' . $file . '.php';
    }


    public function showErrorPage($title, $description = '')
    {
        $data = [
            'title' => $title,
            'description' => $description,
            'css_files'   => [
                App::getInstance('url.assets') . 'public/saas_public.css'
            ],
        ];

        status_header(200);
        $this->render('error', $data);
        exit(200);
    }

}
