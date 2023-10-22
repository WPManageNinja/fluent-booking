<?php

namespace FluentBooking\App\Hooks\Handlers;

use FluentBooking\App\App;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\User;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\PaymentMethods\CurrenciesHelper;
use FluentBooking\App\Services\PermissionManager;

class AdminMenuHandler
{
    public function register()
    {
        add_action('admin_menu', [$this, 'add']);

        add_action('admin_enqueue_scripts', function () {
            if (!isset($_REQUEST['page']) || $_REQUEST['page'] != 'fluent-booking') {
                return;
            }
            $this->enqueueAssets();
        }, 100);
    }

    public function add()
    {
        $capability = PermissionManager::getMenuPermission();
        $menuPriority = 26;

        if (defined('FLUENTCRM')) {
            $menuPriority = 4;
        }

        add_menu_page(
            __('Fluent Booking', 'fluent-booking-pro'),
            __('Fluent Booking', 'fluent-booking-pro'),
            $capability,
            'fluent-booking',
            [$this, 'render'],
            $this->getMenuIcon(),
            $menuPriority
        );

        add_submenu_page(
            'fluent-booking',
            __('Dashboard', 'fluent-booking-pro'),
            __('Dashboard', 'fluent-booking-pro'),
            $capability,
            'fluent-booking',
            ''
        );

        add_submenu_page(
            'fluent-booking',
            __('Calendars', 'fluent-booking-pro'),
            __('Calendars', 'fluent-booking-pro'),
            $capability,
            'admin.php?page=fluent-booking#/calendars',
            ''
        );

        add_submenu_page(
            'fluent-booking',
            __('Bookings', 'fluent-booking-pro'),
            __('Bookings', 'fluent-booking-pro'),
            $capability,
            'admin.php?page=fluent-booking#/scheduled-events',
            ''
        );

        add_submenu_page(
            'fluent-booking',
            __('Availability', 'fluent-booking-pro'),
            __('Availability', 'fluent-booking-pro'),
            $capability,
            'admin.php?page=fluent-booking#/availability',
            ''
        );

        add_submenu_page(
            'fluent-booking',
            __('Settings', 'fluent-booking-pro'),
            __('Settings', 'fluent-booking-pro'),
            'manage_options',
            'admin.php?page=fluent-booking#/settings/general-settings',
            ''
        );
    }

    public function render()
    {
        $app = App::getInstance();

        $config = $app->config;

        $name = $config->get('app.name');

        $slug = $config->get('app.slug');

        $baseUrl = Helper::getAppBaseUrl();

        $isNew = $this->isNew();

        $menuItems = [
            [
                'key'       => 'dashboard',
                'label'     => $isNew ? __('Getting Started', 'fluent-booking-pro') : __('Dashboard', 'fluent-booking-pro'),
                'permalink' => $baseUrl
            ],
            [
                'key'       => 'calendars',
                'label'     => __('Calendars', 'fluent-booking-pro'),
                'permalink' => $baseUrl . 'calendars'
            ],
            [
                'key'       => 'scheduled_events',
                'label'     => __('Bookings', 'fluent-booking-pro'),
                'permalink' => $baseUrl . 'scheduled-events?period=upcoming&author=me',
            ],
            [
                'key'       => 'availability',
                'label'     => __('Availability', 'fluent-booking-pro'),
                'permalink' => $baseUrl . 'availability'
            ]
        ];

        if(current_user_can('manage_options')) {
            $menuItems[] = [
                'key'       => 'settings',
                'label'     => __('Settings', 'fluent-booking-pro'),
                'permalink' => $baseUrl . 'settings/general-settings'
            ];
        }

        $assets = $app['url.assets'];

        $app->view->render('admin.menu', [
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
            'fluent_booing_admin_app', $assets . 'admin/admin.css', [], FLUENT_BOOKING_ASSETS_VERSION
        );

        do_action($slug . '_loading_app');

        wp_enqueue_script(
            $slug . '_admin_app',
            $assets . 'admin/app.js',
            array('jquery'),
            FLUENT_BOOKING_ASSETS_VERSION,
            true
        );

        wp_enqueue_script(
            $slug . '_global_admin',
            $assets . 'admin/global_admin.js',
            array(),
            FLUENT_BOOKING_ASSETS_VERSION,
            true
        );

        if (function_exists('wp_enqueue_editor')) {
            add_filter('user_can_richedit', '__return_true');
            wp_enqueue_editor();
            wp_enqueue_media();
        }

        wp_localize_script($slug . '_admin_app', 'fluentFrameworkAdmin', $this->getDashboardVars($app));
    }

    public function getDashboardVars($app)
    {
        $assets = $app['url.assets'];
        $currentUser = get_user_by('ID', get_current_user_id());

        $isNew = $this->isNew();

        $requireSlug = false;

        if ($isNew) {
            $result = $this->maybeAutoCreateCalendar($currentUser);
            if (!$result) {
                $requireSlug = true;
            }
        }

        $hasAllAccess = false;
        if (PermissionManager::hasAllCalendarAccess()) {
            $hasAllAccess = true;
        }

        $user = User::find($currentUser->ID);
        $eventColors = Helper::getEventColors();
        $meetingDurations = Helper::getMeetingDurations();
        $scheduleSchema = Helper::getWeeklyScheduleSchema();
        $bufferTimes = Helper::getBufferTimes();
        $customFieldTypes = Helper::getCustomFieldTypes();
        $locationFields = (new Calendar())->getLocationFields();

        return apply_filters('fluent_booking/admin_vars', [
            'slug'               => $slug = $app->config->get('app.slug'),
            'nonce'              => wp_create_nonce($slug),
            'rest'               => $this->getRestInfo($app),
            'brand_logo'         => $this->getMenuIcon(),
            'asset_url'          => $assets,
            'event_colors'       => $eventColors,
            'meeting_durations'  => $meetingDurations,
            'buffer_times'       => $bufferTimes,
            'schedule_schema'    => $scheduleSchema,
            'location_fields'    => $locationFields,
            'custom_field_types' => $customFieldTypes,
            'me'                 => [
                'id'          => $currentUser->ID,
                'full_name'   => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                'email'       => $currentUser->user_email,
                'is_admin'    => $hasAllAccess,
                'permissions' => PermissionManager::getUserPermissions($currentUser, false),
            ],
            'is_new'             => $isNew,
            'require_slug'       => $requireSlug,
            'site_url'           => site_url('/'),
            'timezones'          => DateTimeHelper::getTimeZones(true),
            'supported_features' => apply_filters('fluent_booking/supported_featured', [
                'multi_users' => true
            ]),
            'currency'           => CurrenciesHelper::getGlobalCurrency(),
            'currency_sign'      => CurrenciesHelper::getGlobalCurrencySign()
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
        return apply_filters('fluent_booking/is_new', !Calendar::first());
    }

    /**
     * @param $user \WP_User
     * @return bool | Calendar
     */
    protected function maybeAutoCreateCalendar($user)
    {
        if (!apply_filters('fluent_booking/auto_create_calendar', false, $user)) {
            return false;
        }

        $userName = $user->user_login;

        if (is_email($userName)) {
            $userName = explode('@', $userName);
            $userName = $userName[0];
        }

        if (!Helper::isCalendarSlugAvailable($userName, true)) {
            return false;
        }

        $personName = trim($user->first_name . ' ' . $user->last_name);

        if (!$personName) {
            $personName = $user->display_name;
        }

        $data = [
            'user_id' => $user->ID,
            'title'   => $personName,
            'slug'    => $userName
        ];

        return Calendar::create($data);
    }

    public function settingMenuItems()
    {
        return [];
    }
}

