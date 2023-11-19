<?php

namespace FluentBooking\App\Services\Integrations\Calendars\AppleCalendar;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\Calendars\BaseCalendar;
use FluentBooking\App\Services\Integrations\Calendars\CalendarCache;
use FluentBooking\Framework\Support\Arr;

class Bootstrap extends BaseCalendar
{
    public function register()
    {
        $app = App::getInstance();

        $this->calendarKey = 'apple_calendar';
        $this->calendarTitle = __('Apple Calendar', 'fluent-booking-pro');
        $this->logo = $app['url.assets'] . 'images/apple-cal.svg';
        $this->boot();

        add_action('fluent_booking/before_get_all_calendars', function () {
            if (!$this->isConfigured()) {
                return;
            }
            // Show the Google last error
            add_action('fluent_booking/calendar', function (&$calendar, $type) {
                if ($type != 'lists') {
                    return $calendar;
                }

                $metas = Meta::where('object_type', '_apple_cal_user_token')
                    ->where('object_id', $calendar->user_id)
                    ->get();

                foreach ($metas as $meta) {
                    if (empty(Arr::get($meta->value, 'last_error'))) {
                        continue;
                    }

                    $error = Arr::get($meta->value, 'last_error');
                    $calendar->generic_error = '<p style="color: red; margin:0;">' . __('Apple Calendar API Error:', 'fluent-booking-pro') . ' ' . $error . '. <a href="' . Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars') . '">' . __('Click Here to Review', 'fluent-booking-pro') . '</a></p>';
                }

                return $calendar;
            }, 10, 2);
        });
    }

    public function getClientSettingsForView($settings)
    {
        return AppleHelper::getApiConfig();
    }

    public function getClientFieldSettings($settings)
    {
        $description = '<p>' . __('To use Apple Calendar Integration for your Booking forms, please enable the integration.', 'fluent-booking-pro') . ' <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/apple-calendar-integration-with-fluent-booking/">' . __('Read the documentation', 'fluent-booking-pro') . '</a></p>';

        $fields = [
            'is_enabled' => [
                'type'           => 'yes_no_checkbox',
                'label'          => __('Status', 'fluent-booking-pro'),
                'checkbox_label' => __('Enable Apple Calendar Integration', 'fluent-booking-pro'),
            ]
        ];

        return [
            'logo'          => $this->logo,
            'title'         => $this->calendarTitle,
            'subtitle'      => __('Enable/Disable Apple Calendar to sync your events', 'fluent-booking-pro'),
            'description'   => $description,
            'save_btn_text' => __('Save Settings', 'fluent-booking-pro'),
            'fields'        => $fields,
            'will_encrypt'  => false
        ];
    }

    public function addAsProvider($providers, $userId)
    {
        $providers[$this->calendarKey] = [
            'key'                  => $this->calendarKey,
            'icon'                 => $this->logo,
            'is_caldav'            => true,
            'caldav_settings'      => [
                'heading'        => __('Connect to Apple Server', 'fluent-booking-pro'),
                'description'    => __(sprintf('To connect to Apple Server, please enter your Apple Email and app specific password. Generate App Specific Password at %s Your credentials will be stored as encrypted.', '<a target="_blank" rel="noopener" href="https://appleid.apple.com/account/manage">https://appleid.apple.com/account/manage</a>'), 'fluent-booking-pro'),
                'username_label' => __('Apple ID (Email)', 'fluent-booking-pro'),
                'password_label' => __('App Specific Password', 'fluent-booking-pro'),
                'button_text'    => __('Connect with Apple Calendar', 'fluent-booking-pro')
            ],
            'title'                => $this->calendarTitle,
            'subtitle'             => __(sprintf('Configure %s to sync your events', $this->calendarTitle), 'fluent-booking-pro'),
            'btn_text'             => __(sprintf('Connect with %s', $this->calendarTitle), 'fluent-booking-pro'),
            'auth_url'             => $this->getAuthUrl($userId),
            'is_global_configured' => $this->isConfigured(),
            'global_config_url'    => admin_url('admin.php?page=fluent-booking#/settings/configure-integrations/' . $this->calendarKey),
        ];

        return $providers;
    }

    public function saveClientSettings($settings)
    {
        AppleHelper::updateConfig($settings);
    }

    public function pushFeeds($feeds, $userId)
    {
        if (!$this->isConfigured()) {
            return $feeds;
        }

        $items = Meta::where('object_type', '_apple_cal_user_token')
            ->where('object_id', $userId)
            ->get();

        foreach ($items as $item) {

            CalendarCache::deleteAllParentCache($item->id);

            $errors = '';

            $remoteCalendars = $this->getRemoteCalendarsList($item, true);

            if (is_wp_error($remoteCalendars)) {
                $errors = $remoteCalendars->get_error_message() . ' ' . __('Please remove the connection and reconnect again.', 'fluent-booking-pro');
                $remoteCalendars = [];
            }

            $feeds[] = [
                'driver'             => $this->calendarKey,
                'db_id'              => $item->id,
                'identifier'         => $item->key,
                'remote_calendars'   => $remoteCalendars,
                'errors'             => $errors,
                'conflict_check_ids' => Arr::get($item->value, 'conflict_check_ids', [])
            ];
        }

        return $feeds;
    }

    public function authDisconnect($meta)
    {
        // Let's remove the cache first
        CalendarCache::deleteAllParentCache($meta->id);
        $meta->delete();
    }

    public function getBookedSlots($books, $calendarSlot, $toTimeZone, $dateRange, $isDoingBooking)
    {
        return $books;
    }

    public function createEvent($config, Booking $booking)
    {
        if (!$this->isConfigured() || $booking->status != 'scheduled') {
            return false;
        }

        if ($booking->getMeta('__apple_calendar_event')) {
            return false; // Already created
        }

        $meta = Meta::where('object_type', '_apple_cal_user_token')
            ->where('object_id', $booking->host_user_id)
            ->where('id', $config['db_id'])
            ->first();

        if (!$meta) {
            return false; //  Meta could not be found
        }

        $settings = $meta->value;

        $isValid = false;

        $calendarLists = Arr::get($settings, 'calendar_lists', []);

        foreach ($calendarLists as $item) {
            if ($item['can_write'] != 'yes') {
                continue;
            }

            if ($item['id'] == $config['remote_calendar_id']) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            return false; // invalid id of the remote calendar
        }


    }

    public function cancelEvent($config, Booking $booking)
    {
        return $this->patchEvent($config, $booking, [
            'status' => 'cancelled'
        ], false);
    }

    public function patchEvent($config, Booking $booking, $updateData, $isRescheduling)
    {
        if (!$this->isConfigured()) {
            return false;
        }

        return;
    }

    public function maybeAddOrRemoveGroupMembers($config, Booking $booking, $allGroupBookings, $isRescheduling)
    {
        return;
    }

    public function getAuthUrl($userId = null)
    {
        return '';
    }

    public function isConfigured()
    {
        $config = AppleHelper::getApiConfig();
        return $config['is_enabled'] == 'yes';
    }


    private function getRemoteCalendarsList($item, $fromApi = false)
    {
        return [];
    }

    private function addFeedIntegration($userId, $tokenData)
    {
        $exist = Meta::where('object_type', '_apple_cal_user_token')
            ->where('object_id', $userId)
            ->where('key', $tokenData['remote_email'])
            ->first();

        if ($exist) {
            $exist->value = $tokenData;
            $exist->save();
            return $exist;
        }

        return Meta::create([
            'object_type' => '_apple_cal_user_token',
            'object_id'   => $userId,
            'key'         => $tokenData['remote_email'],
            'value'       => $tokenData
        ]);
    }
}
