<?php

namespace FluentCalendar\App\Hooks\Handlers;

use FluentCalendar\App\App;
use FluentCalendar\App\Models\Calendar;
use FluentCalendar\App\Services\DateTimeHelper;
use FluentCalendar\App\Services\Helper;

class AdminMenuHandler
{
    public function add()
    {
        $capability = 'manage_options';

        add_menu_page(
            __('Fluent Calendar', 'fluent-calendar'),
            __('Fluent Calendar', 'fluent-calendar'),
            $capability,
            'fluent-calendar',
            [$this, 'render'],
            $this->getMenuIcon(),
            6
        );
    }

    public function render()
    {
        $this->enqueueAssets();

        $config = App::getInstance('config');

        $name = $config->get('app.name');

        $slug = $config->get('app.slug');

	    $baseUrl = Helper::getAppBaseUrl();

        if($this->isNew()) {
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
                    'permalink' => $baseUrl.'calendars'
                ],
                [
                    'key'       => 'scheduled_events',
                    'label'     => __('Scheduled Events', 'fluent-calendar'),
                    'permalink' => $baseUrl.'scheduled-events?period=upcoming&author=me'
                ]
            ];
        }



	    $app = App::getInstance();
	    $assets = $app['url.assets'];

	    App::make('view')->render('admin.menu', [
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

	    $currentUser = get_user_by('ID', get_current_user_id());

        $isNew = $this->isNew();

        $requireSlug = false;
        if($isNew) {
            $result = $this->maybeAutoCreateCalendar($currentUser);
            if(!$result) {
                $requireSlug = true;
            }
        }

	    wp_localize_script($slug . '_admin_app', 'fluentFrameworkAdmin', [
		    'slug'  => $slug = $app->config->get('app.slug'),
		    'nonce' => wp_create_nonce($slug),
		    'rest'  => $this->getRestInfo($app),
		    'brand_logo' => $this->getMenuIcon(),
		    'asset_url' => $assets,
		    'me'          => [
			    'id'        => $currentUser->ID,
			    'full_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
			    'email'     => $currentUser->user_email
		    ],
            'is_new' => $isNew,
            'require_slug' => $requireSlug,
            'site_url' => site_url('/'),
            'timezones' => DateTimeHelper::getTimeZones(true),
            'supported_features' => apply_filters('fluent_calendar/supported_featured', [
                'multi_users' => false
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
        return 'dashicons-wordpress-alt';
    }

    protected function isNew()
    {
        $userId = get_current_user_id();
        return ! Calendar::where('user_id', $userId)->first();
    }

    /**
     * @param $user \WP_User
     * @return bool | Calendar
     */
    protected function maybeAutoCreateCalendar($user)
    {
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
}

