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
                    'permalink' => $baseUrl,
                    'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M18.3333 6.89175V3.52508C18.3333 2.20008 17.8 1.66675 16.475 1.66675H13.1083C11.7833 1.66675 11.25 2.20008 11.25 3.52508V6.89175C11.25 8.21675 11.7833 8.75008 13.1083 8.75008H16.475C17.8 8.75008 18.3333 8.21675 18.3333 6.89175Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8.74996 7.10008V3.31675C8.74996 2.14175 8.21663 1.66675 6.89163 1.66675H3.52496C2.19996 1.66675 1.66663 2.14175 1.66663 3.31675V7.09175C1.66663 8.27508 2.19996 8.74175 3.52496 8.74175H6.89163C8.21663 8.75008 8.74996 8.27508 8.74996 7.10008Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8.74996 16.475V13.1083C8.74996 11.7833 8.21663 11.25 6.89163 11.25H3.52496C2.19996 11.25 1.66663 11.7833 1.66663 13.1083V16.475C1.66663 17.8 2.19996 18.3333 3.52496 18.3333H6.89163C8.21663 18.3333 8.74996 17.8 8.74996 16.475Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.5 12.9167H17.5" stroke="#292D32" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M12.5 16.25H17.5" stroke="#292D32" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>'
                ],
                [
                    'key'       => 'calendars',
                    'label'     => __('Booking Types', 'fluent-booking'),
                    'permalink' => $baseUrl.'calendars',
                    'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" fill="none">
                        <path d="M27.9166 5.93398V3.33398C27.9166 2.65065 27.35 2.08398 26.6666 2.08398C25.9833 2.08398 25.4166 2.65065 25.4166 3.33398V5.83398H14.5833V3.33398C14.5833 2.65065 14.0166 2.08398 13.3333 2.08398C12.65 2.08398 12.0833 2.65065 12.0833 3.33398V5.93398C7.58331 6.35065 5.39998 9.03398 5.06664 13.0173C5.03331 13.5007 5.43331 13.9007 5.89998 13.9007H34.1C34.5833 13.9007 34.9833 13.484 34.9333 13.0173C34.6 9.03398 32.4166 6.35065 27.9166 5.93398Z" fill="#292D32"></path>
                        <path d="M31.6667 25C27.9833 25 25 27.9833 25 31.6667C25 32.9167 25.35 34.1 25.9667 35.1C27.1167 37.0333 29.2333 38.3333 31.6667 38.3333C34.1 38.3333 36.2167 37.0333 37.3667 35.1C37.9833 34.1 38.3333 32.9167 38.3333 31.6667C38.3333 27.9833 35.35 25 31.6667 25ZM35.1167 30.95L31.5667 34.2333C31.3333 34.45 31.0167 34.5667 30.7167 34.5667C30.4 34.5667 30.0833 34.45 29.8333 34.2L28.1833 32.55C27.7 32.0667 27.7 31.2667 28.1833 30.7833C28.6667 30.3 29.4667 30.3 29.95 30.7833L30.75 31.5833L33.4167 29.1167C33.9167 28.65 34.7167 28.6833 35.1833 29.1833C35.65 29.6833 35.6167 30.4667 35.1167 30.95Z" fill="#292D32"></path>
                        <path d="M33.3333 16.4004H6.66667C5.75 16.4004 5 17.1504 5 18.0671V28.3337C5 33.3337 7.5 36.6671 13.3333 36.6671H21.55C22.7 36.6671 23.5 35.5504 23.1333 34.4671C22.8 33.5004 22.5167 32.4337 22.5167 31.6671C22.5167 26.6171 26.6333 22.5004 31.6833 22.5004C32.1667 22.5004 32.65 22.5337 33.1167 22.6171C34.1167 22.7671 35.0167 21.9837 35.0167 20.9837V18.0837C35 17.1504 34.25 16.4004 33.3333 16.4004ZM15.35 30.3504C15.0333 30.6504 14.6 30.8337 14.1667 30.8337C13.7333 30.8337 13.3 30.6504 12.9833 30.3504C12.6833 30.0337 12.5 29.6004 12.5 29.1671C12.5 28.7337 12.6833 28.3004 12.9833 27.9837C13.15 27.8337 13.3167 27.7171 13.5333 27.6337C14.15 27.3671 14.8833 27.5171 15.35 27.9837C15.65 28.3004 15.8333 28.7337 15.8333 29.1671C15.8333 29.6004 15.65 30.0337 15.35 30.3504ZM15.35 24.5171C15.2667 24.5837 15.1833 24.6504 15.1 24.7171C15 24.7837 14.9 24.8337 14.8 24.8671C14.7 24.9171 14.6 24.9504 14.5 24.9671C14.3833 24.9837 14.2667 25.0004 14.1667 25.0004C13.7333 25.0004 13.3 24.8171 12.9833 24.5171C12.6833 24.2004 12.5 23.7671 12.5 23.3337C12.5 22.9004 12.6833 22.4671 12.9833 22.1504C13.3667 21.7671 13.95 21.5837 14.5 21.7004C14.6 21.7171 14.7 21.7504 14.8 21.8004C14.9 21.8337 15 21.8837 15.1 21.9504C15.1833 22.0171 15.2667 22.0837 15.35 22.1504C15.65 22.4671 15.8333 22.9004 15.8333 23.3337C15.8333 23.7671 15.65 24.2004 15.35 24.5171ZM21.1833 24.5171C20.8667 24.8171 20.4333 25.0004 20 25.0004C19.5667 25.0004 19.1333 24.8171 18.8167 24.5171C18.5167 24.2004 18.3333 23.7671 18.3333 23.3337C18.3333 22.9004 18.5167 22.4671 18.8167 22.1504C19.45 21.5337 20.5667 21.5337 21.1833 22.1504C21.4833 22.4671 21.6667 22.9004 21.6667 23.3337C21.6667 23.7671 21.4833 24.2004 21.1833 24.5171Z" fill="#292D32"></path>
                     </svg>'
                ],
                [
                    'key'       => 'scheduled_events',
                    'label'     => __('Scheduled Meetings', 'fluent-booking'),
                    'permalink' => $baseUrl.'scheduled-events?period=upcoming&author=me',
                    'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M6.66669 1.66699V4.16699" stroke="#2653C7" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M13.3333 1.66699V4.16699" stroke="#445164" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M2.91669 7.5752H17.0834" stroke="#445164" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M17.5 7.08366V14.167C17.5 16.667 16.25 18.3337 13.3333 18.3337H6.66667C3.75 18.3337 2.5 16.667 2.5 14.167V7.08366C2.5 4.58366 3.75 2.91699 6.66667 2.91699H13.3333C16.25 2.91699 17.5 4.58366 17.5 7.08366Z" stroke="#445164" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M13.0789 11.4167H13.0864" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M13.0789 13.9167H13.0864" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9.99626 11.4167H10.0037" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9.99626 13.9167H10.0037" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.91191 11.4167H6.91939" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.91191 13.9167H6.91939" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
                ],
                [
                    'key'       => 'settings',
                    'label'     => __('Settings', 'fluent-booking'),
                    'permalink' => $baseUrl.'settings',
                    'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1.66663 10.7334V9.2667C1.66663 8.40003 2.37496 7.68336 3.24996 7.68336C4.75829 7.68336 5.37496 6.6167 4.61663 5.30836C4.18329 4.55836 4.44163 3.58336 5.19996 3.15003L6.64163 2.32503C7.29996 1.93336 8.14996 2.1667 8.54163 2.82503L8.63329 2.98336C9.38329 4.2917 10.6166 4.2917 11.375 2.98336L11.4666 2.82503C11.8583 2.1667 12.7083 1.93336 13.3666 2.32503L14.8083 3.15003C15.5666 3.58336 15.825 4.55836 15.3916 5.30836C14.6333 6.6167 15.25 7.68336 16.7583 7.68336C17.625 7.68336 18.3416 8.3917 18.3416 9.2667V10.7334C18.3416 11.6 17.6333 12.3167 16.7583 12.3167C15.25 12.3167 14.6333 13.3834 15.3916 14.6917C15.825 15.45 15.5666 16.4167 14.8083 16.85L13.3666 17.675C12.7083 18.0667 11.8583 17.8334 11.4666 17.175L11.375 17.0167C10.625 15.7084 9.39163 15.7084 8.63329 17.0167L8.54163 17.175C8.14996 17.8334 7.29996 18.0667 6.64163 17.675L5.19996 16.85C4.44163 16.4167 4.18329 15.4417 4.61663 14.6917C5.37496 13.3834 4.75829 12.3167 3.24996 12.3167C2.37496 12.3167 1.66663 11.6 1.66663 10.7334Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>'
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
        $eventColors = Helper::getEventColors();
        $meetingDurations = Helper::getMeetingDurations();
        $scheduleSchema = Helper::getWeeklyScheduleSchema();
        $editorShortcodes = Helper::getEditorShortCodes();


        return apply_filters('fluent_booking/admin_vars', [
            'slug'  => $slug = $app->config->get('app.slug'),
            'nonce' => wp_create_nonce($slug),
            'rest'  => $this->getRestInfo($app),
            'brand_logo'        => $this->getMenuIcon(),
            'asset_url'         => $assets,
            'event_colors'      => $eventColors,
            'meeting_durations' => $meetingDurations,
            'schedule_schema'   => $scheduleSchema,
            'editor_shortcodes' => $editorShortcodes,
            'me' => [
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
            'settings' => [
                'menu' => [
                    'key'       => 'settings',
                    'label'     => __('Availability', 'fluent-booking'),
                    'svgIcon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M18.3333 9.99935C18.3333 14.5993 14.6 18.3327 9.99999 18.3327C5.39999 18.3327 1.66666 14.5993 1.66666 9.99935C1.66666 5.39935 5.39999 1.66602 9.99999 1.66602C14.6 1.66602 18.3333 5.39935 18.3333 9.99935Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.0917 12.6495L10.5083 11.1078C10.0583 10.8411 9.69168 10.1995 9.69168 9.67448V6.25781" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>',
                    'permalink' => $baseUrl
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

