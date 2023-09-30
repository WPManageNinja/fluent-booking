<?php

namespace FluentBooking\App\Hooks\Handlers;

use FluentBooking\App\App;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\User;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Helper;

class AdminMenuHandler
{

    protected $app;

    public function __construct()
    {
        $this->app = App::getInstance();
    }

    public function add()
    {
        $capability = 'manage_options';

        add_menu_page(
            __('Fluent Booking', 'fluent-booking'),
            __('Fluent Booking', 'fluent-booking'),
            $capability,
            'fluent-booking',
            [$this, 'render'],
            $this->getMenuIcon(),
            6
        );

        add_submenu_page(
            'fluent-booking',
            __('Dashboard', 'fluent-booking'),
            __('Dashboard', 'fluent-booking'),
            $capability,
            'fluent-booking',
            ''
        );

        add_submenu_page(
            'fluent-booking',
            __('Booking Types', 'fluent-booking'),
            __('Booking Types', 'fluent-booking'),
            $capability,
            'admin.php?page=fluent-booking#/calendars',
            ''
        );

        add_submenu_page(
            'fluent-booking',
            __('Meetings', 'fluent-booking'),
            __('Meetings', 'fluent-booking'),
            $capability,
            'admin.php?page=fluent-booking#/scheduled-events',
            ''
        );

        add_submenu_page(
            'fluent-booking',
            __('Settings', 'fluent-booking'),
            __('Settings', 'fluent-booking'),
            $capability,
            'admin.php?page=fluent-booking#/settings',
            ''
        );
    }

    public function render()
    {
        $this->enqueueAssets();

        $config = $this->app->config;
        
        $name = $config->get('app.name');

        $slug = $config->get('app.slug');

	    $baseUrl = Helper::getAppBaseUrl();

        if($this->isNew()) {
            $menuItems = [
                [
                    'key'       => 'dashboard',
                    'label'     => __('Getting Started', 'fluent-booking'),
                    'permalink' => $baseUrl
                ],
            ];
        } else {
            $menuItems = [
                [
                    'key'       => 'dashboard',
                    'label'     => __('Dashboard', 'fluent-booking'),
                    'permalink' => $baseUrl
                ],
                [
                    'key'       => 'calendars',
                    'label'     => __('Booking Types', 'fluent-booking'),
                    'permalink' => $baseUrl.'calendars'
                ],
                [
                    'key'       => 'scheduled_events',
                    'label'     => __('Scheduled Meetings', 'fluent-booking'),
                    'permalink' => $baseUrl.'scheduled-events?period=upcoming&author=me'
                ],
                [
                    'key'       => 'settings',
                    'label'     => __('Settings', 'fluent-booking'),
                    'permalink' => $baseUrl.'settings'
                ]
            ];
        }

	    $assets = $this->app['url.assets'];

	    $this->app->view->render('admin.menu', [
		    'name'      => $name,
		    'slug'      => $slug,
		    'menuItems' => $menuItems,
		    'baseUrl'   => $baseUrl,
		    'logo'      => $assets . 'images/logo.svg',
	    ]);
    }

    public function enqueueAssets()
    {
        $app = App::getInstance();

        $assets = $app['url.assets'];

        $slug = $app->config->get('app.slug');

        wp_enqueue_style(
            $slug . '_admin_app', $assets . 'admin/admin.css'
        );

        do_action($slug . '_loading_app');

        wp_enqueue_script(
            $slug . '_admin_app',
            $assets . 'admin/app.js',
            array('jquery'),
            '1.0',
            true
        );

	    wp_enqueue_script(
		    $slug . '_global_admin',
		    $assets . 'admin/global_admin.js',
		    array(),
		    '1.0',
		    true
	    );

        if (function_exists('wp_enqueue_editor')) {
            add_filter('user_can_richedit', '__return_true');
            wp_enqueue_editor();
        }

	    wp_localize_script($slug . '_admin_app', 'fluentFrameworkAdmin', $this->getDashboardVars($app));
    }

    public function getDashboardVars($app)
    {
        $assets = $app['url.assets'];
        $currentUser = get_user_by('ID', get_current_user_id());

        $isNew = $this->isNew();

        $requireSlug = false;

        if($isNew) {
            $result = $this->maybeAutoCreateCalendar($currentUser);
            if(!$result) {
                $requireSlug = true;
            }
        }

        $user = User::find($currentUser->ID);
        $eventTypes = Helper::getEventTypesSchema();
        $editorShortcodes = Helper::getEditorShortCodes();


        return apply_filters('fluent_booking/admin_vars', [
            'slug'  => $slug = $app->config->get('app.slug'),
            'nonce' => wp_create_nonce($slug),
            'rest'  => $this->getRestInfo($app),
            'brand_logo'  => $this->getMenuIcon(),
            'asset_url'   => $assets,
            'event_types' => $eventTypes,
            'editor_shortcodes' => $editorShortcodes,
            'me'          => [
                'id'        => $currentUser->ID,
                'full_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                'email'     => $currentUser->user_email
            ],
            'is_new' => $isNew,
            'require_slug' => $requireSlug,
            'site_url' => site_url('/'),
            'timezones' => DateTimeHelper::getTimeZones(true),
            'supported_features' => apply_filters('fluent_booking/supported_featured', [
                'multi_users' => true
            ])
        ]);
    }

    protected function getRestInfo($app)
    {
        $ns = $app->config->get('app.rest_namespace');
        $ver = $app->config->get('app.rest_version');

        return [
            'base_url'  => esc_url_raw(rest_url()),
            'url'       => rest_url($ns . '/' . $ver),
            'nonce'     => wp_create_nonce('wp_rest'),
            'namespace' => $ns,
            'version'   => $ver
        ];
    }

    protected function getMenuIcon()
    {
        return 'data:image/svg+xml;base64,' . base64_encode('<svg width="96" height="101" viewBox="0 0 96 101" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="25.5746" width="6.39365" height="15.9841" rx="3.19683" fill="white"/><rect x="63.9365" width="6.39365" height="15.9841" rx="3.19683" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M54.878 53.0655C54.544 55.6678 53.4646 58.035 51.8535 59.9427C50.1623 61.9572 47.886 63.4614 45.2863 64.1988L45.1741 64.2309L44.9203 64.2976L44.8989 64.303L24.7671 69.7V65.019C24.7671 64.9148 24.7671 64.8106 24.7778 64.7064C24.8953 62.748 26.127 61.0862 27.8476 60.3514C28.0427 60.2659 28.2431 60.1938 28.4515 60.1377L28.6412 60.0869L54.8753 53.0575V53.0655H54.878Z" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M71.1411 35.8059C70.4571 41.1467 66.6178 45.5017 61.5494 46.9391L61.4372 46.9712L61.1861 47.038H61.1834L61.162 47.0433L24.7671 56.7953V52.1144C24.7671 50.0197 26.0362 48.2216 27.8476 47.4468L28.4515 47.233L28.6385 47.1823L71.1384 35.7952V35.8059H71.1411Z" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M19.9802 11.1889H75.9246C83.4282 11.1889 89.5111 17.2718 89.5111 24.7754V70H95.9048V24.7754C95.9048 13.7406 86.9593 4.79523 75.9246 4.79523H19.9802C8.94542 4.79523 0 13.7406 0 24.7754V80.7198C0 91.7546 8.94542 100.7 19.9802 100.7L64.9524 100.7V94.3063H19.9802C12.4765 94.3063 6.39365 88.2234 6.39365 80.7198V24.7754C6.39365 17.2718 12.4765 11.1889 19.9802 11.1889Z" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M95.9524 70.7477V69.7H64.9524V100.7H66.0001L95.9524 70.7477Z" fill="white"/></svg>');
    }

    protected function isNew()
    {
        return apply_filters('fluent_booking/is_new', ! Calendar::first());
    }

    /**
     * @param $user \WP_User
     * @return bool | Calendar
     */
    protected function maybeAutoCreateCalendar($user)
    {
        if(!apply_filters('fluent_booking/auto_create_calendar', false, $user)) {
            return false;
        }

        $userName = $user->user_login;

        if(is_email($userName)) {
            $userName = explode('@', $userName);
            $userName = $userName[0];
        }

        if(!Helper::isCalendarSlugAvailable($userName, true)) {
            return false;
        }

        $data = [
            'user_id' => $user->ID,
            'title' => sprintf('Booking schedule with %s', trim($user->first_name.' '.$user->last_name)),
            'slug' => $userName
        ];

        return Calendar::create($data);
    }

    public function settingMenuItems()
    {
        $baseUrl = Helper::getAppBaseUrl();

        $menuItems = apply_filters('fluent_booking/settings_menu_items', [
            'general' => [
                'menu' => [
                    'key'       => 'settings',
                    'label'     => __('General', 'fluent-booking'),
                    'svgIcon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M2.5 7.59166V12.4C2.5 14.1667 2.5 14.1667 4.16667 15.2917L8.75 17.9417C9.44167 18.3417 10.5667 18.3417 11.25 17.9417L15.8333 15.2917C17.5 14.1667 17.5 14.1667 17.5 12.4083V7.59166C17.5 5.83333 17.5 5.83333 15.8333 4.70833L11.25 2.05833C10.5667 1.65833 9.44167 1.65833 8.75 2.05833L4.16667 4.70833C2.5 5.83333 2.5 5.83333 2.5 7.59166Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    'permalink' => $baseUrl
                ]
            ],
            'profile' => [
                'menu' => [
                    'key'       => 'profile-settings',
                    'label'     => __('Profile', 'fluent-booking'),
                    'svgIcon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10.0002 10C12.3013 10 14.1668 8.13452 14.1668 5.83334C14.1668 3.53215 12.3013 1.66667 10.0002 1.66667C7.69898 1.66667 5.8335 3.53215 5.8335 5.83334C5.8335 8.13452 7.69898 10 10.0002 10Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M17.1585 18.3333C17.1585 15.1083 13.9501 12.5 10.0001 12.5C6.05013 12.5 2.8418 15.1083 2.8418 18.3333" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    'permalink' => $baseUrl . 'profile-settings'
                ]
            ],
            'configurations' => [
                'menu' => [
                    'key'       => 'configure-integrations',
                    'label'     => __('Configure Integrations', 'fluent-booking'),
                    'svgIcon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4.7915 13.425V6.57499C5.8665 6.29999 6.6665 5.33332 6.6665 4.16666C6.6665 2.78332 5.54984 1.66666 4.1665 1.66666C2.78317 1.66666 1.6665 2.78332 1.6665 4.16666C1.6665 5.33332 2.4665 6.29999 3.5415 6.57499V13.4167C2.4665 13.7 1.6665 14.6667 1.6665 15.8333C1.6665 17.2167 2.78317 18.3333 4.1665 18.3333C5.54984 18.3333 6.6665 17.2167 6.6665 15.8333C6.6665 14.6667 5.8665 13.7 4.7915 13.425Z" fill="#445164" stroke="#445164" stroke-width="0.5" /><path d="M16.4583 13.425V5.41666C16.4583 4.14999 15.4333 3.12499 14.1667 3.12499H11.725L12.9 2.14999C13.1667 1.92499 13.2 1.53333 12.9833 1.26666C12.7583 0.999994 12.3667 0.96666 12.1 1.18333L9.6 3.26666C9.45833 3.38333 9.375 3.55833 9.375 3.74999C9.375 3.94166 9.45833 4.10833 9.6 4.23333L12.1 6.31666C12.2167 6.41666 12.3583 6.45833 12.5 6.45833C12.675 6.45833 12.8583 6.38333 12.9833 6.23333C13.2083 5.96666 13.1667 5.57499 12.9 5.34999L11.725 4.37499H14.1667C14.7417 4.37499 15.2083 4.84166 15.2083 5.41666V13.425C14.1333 13.7 13.3333 14.6667 13.3333 15.8333C13.3333 17.2167 14.45 18.3333 15.8333 18.3333C17.2167 18.3333 18.3333 17.2167 18.3333 15.8333C18.3333 14.6667 17.5333 13.7 16.4583 13.425Z" fill="#445164" stroke="#445164" stroke-width="0.5" /></svg>',
                    'permalink' => $baseUrl . 'configure-integration'
                ]
            ],
            'integrations' => [
                'menu' => [
                    'key'       => 'integrations',
                    'label'     => __('Integrations', 'fluent-booking'),
                    'svgIcon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M15.8333 13.3333V5.41667C15.8333 4.5 15.0833 3.75 14.1667 3.75H9.58334" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.6667 1.66602L9.16666 3.74935L11.6667 5.83268" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.8333 18.334C17.2141 18.334 18.3333 17.2147 18.3333 15.834C18.3333 14.4533 17.2141 13.334 15.8333 13.334C14.4526 13.334 13.3333 14.4533 13.3333 15.834C13.3333 17.2147 14.4526 18.334 15.8333 18.334Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.16666 6.66602V14.5827C4.16666 15.4993 4.91666 16.2493 5.83332 16.2493H10.4167" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.33334 18.3327L10.8333 16.2493L8.33334 14.166" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.16666 6.66602C5.54737 6.66602 6.66666 5.54673 6.66666 4.16602C6.66666 2.7853 5.54737 1.66602 4.16666 1.66602C2.78594 1.66602 1.66666 2.7853 1.66666 4.16602C1.66666 5.54673 2.78594 6.66602 4.16666 6.66602Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    'permalink' => $baseUrl . 'integrations'
                ]
            ]
        ]);

        return $menuItems;
    }
}

