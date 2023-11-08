<?php

namespace FluentBooking\App\Services\Integrations\Calendars\Outlook;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\Calendars\CalendarCache;
use FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarHelper;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\Framework\Support\Arr;

class Bootstrap
{
    public function register()
    {

        /*
         * Global Settings Handlers
         */
        add_filter('fluent_booking/settings_menu_items', function ($menuItems) {
            $app = App::getInstance();
            $menuItems['outlook_calendar'] = [
                'title'          => __('Outlook Calendar', 'fluent-booking-pro'),
                'icon_url'       => $app['url.assets'] . 'images/outlook-color.svg',
                'component_type' => 'GlobalSettingsComponent',
                'route'          => [
                    'name'   => 'configure-integrations',
                    'params' => [
                        'settings_key' => 'outlook_calendar'
                    ]
                ]
            ];
            return $menuItems;
        }, 10, 1);

        add_filter('fluent_booking/get_client_settings_outlook_calendar', function ($settings) {
            $config = OutlookHelper::getApiConfig();
            $config['redirect_url'] = OutlookHelper::getAppRedirectUrl();

            if (!empty($config['constant_defined'])) {
                $config['client_secret'] = '**********';
                $config['client_id'] = '**********';
            } else if (!empty($config['client_secret'])) {
                $config['client_secret'] = '********************';
            }

            return $config;
        });
        add_filter('fluent_booking/get_client_field_settings_outlook_calendar', function ($items) {

            $app = App::getInstance();

            $fields = [
                'client_id'     => [
                    'type'        => 'text',
                    'label'       => __('Client ID', 'fluent-booking-pro'),
                    'placeholder' => __('Enter Your Client ID', 'fluent-booking-pro'),
                ],
                'client_secret' => [
                    'type'        => 'text',
                    'label'       => __('Secret Key', 'fluent-booking-pro'),
                    'placeholder' => __('Enter Your Secret Key', 'fluent-booking-pro'),
                ],
                'redirect_url'  => [
                    'type'        => 'text',
                    'label'       => __('Redirect URI', 'fluent-booking-pro'),
                    'placeholder' => __('Enter Your Redirect URI', 'fluent-booking-pro'),
                    'readonly'    => true,
                    'copy_btn'    => true,
                ],
                'caching_time'  => [
                    'type'        => 'select',
                    'options'     => [
                        '1'  => __('1 minute', 'fluent-booking-pro'),
                        '5'  => __('5 minutes', 'fluent-booking-pro'),
                        '10' => __('10 minutes', 'fluent-booking-pro'),
                        '15' => __('15 minutes', 'fluent-booking-pro'),
                    ],
                    'label'       => __('Caching Time', 'fluent-booking-pro'),
                    'inline_help' => __('Select for how many minutes the Google Calendar event API call will be cached. Recommended 5/10 minutes. If you add lots of manual events in google then you may lower the value', 'fluent-booking-pro')
                ],
            ];

            $config = OutlookHelper::getApiConfig();

            $description = '<p>' . __('Login to your Outlook account, go to Azure Cloud Console, create a project, complete OAuth Consent screen process, click on Create Credentials, and you will get your client id and secret key. If you get the ID and Keys for Outlook Calendar. For full details read the', 'fluent-booking-pro') . ' <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/outlook-calendar-integration-with-fluent-booking/">' . __('documentation', 'fluent-booking-pro') . '</a></p>';

            if (!empty($config['constant_defined'])) {
                $fields = null;
                $description = '<p>' . __('Outlook Calendar integration is configured by wp-config.php constants. No action required here', 'fluent-booking-pro') . '</p>';
            }

            return [
                'logo'          => $app['url.assets'] . 'images/outlook-color.svg',
                'title'         => __('Outlook Calendar / Meet', 'fluent-booking-pro'),
                'subtitle'      => __('Configure Outlook Calendar to sync your events', 'fluent-booking-pro'),
                'description'   => $description,
                'save_btn_text' => __('Save Outlook API Configuration', 'fluent-booking-pro'),
                'fields'        => $fields,
                'will_encrypt'  => true
            ];
        });

        add_filter('fluent_booking/get_location_fields', function ($fields, $calendar) {
            $meetExist = Meta::where('object_type', '_outlook_user_token')
                ->where('object_id', $calendar->user_id)
                ->first();

            $message = !$meetExist ? ' ' . __('(Connect Outlook Calendar First)', 'fluent-booking-pro') : '';

            if (!$message) {
                // now check if the user calendar event create enabled
                $calConfig = RemoteCalendarHelper::getUserRemoteCreatableCalendarSettings($calendar->user_id);
                if (!$calConfig || Arr::get($calConfig, 'driver') != 'outlook') {
                    $message = __('(Set Outlook Event Creat First)', 'fluent-booking-pro');
                    $meetExist = false;
                }
            }

            $fields['conferencing']['options']['outlook'] = [
                'title'         => __('Outlook', 'fluent-booking-pro') . $message,
                'disabled'      => !$meetExist,
                'location_type' => 'conferencing'
            ];
            return $fields;
        }, 10, 2);

        add_action('fluent_booking/save_client_settings_outlook_calendar', function ($settings) {
            OutlookHelper::updateApiConfig($settings);
        });
        add_action('wp_ajax_fluent_booking_outlook_auth', [$this, 'handleAuthCallback']);

        /*
         * oAuth From Handlers from Calendar
         */
        add_filter('fluent_booking/remote_calendar_providers', function ($calendars, $userId = null) {
            $app = App::getInstance();
            $calendars['outlook'] = [
                'key'                  => 'outlook',
                'icon'                 => $app['url.assets'] . 'images/outlook-color.svg',
                'title'                => __('Outlook Calendar', 'fluent-booking-pro'),
                'subtitle'             => __('Configure Outlook Calendar to sync your events', 'fluent-booking-pro'),
                'btn_text'             => __('Connect with Outlook Calendar', 'fluent-booking-pro'),
                'auth_url'             => $this->getAuthUrl($userId),
                'is_global_configured' => OutlookHelper::isConfigured(),
                'global_config_url'    => admin_url('admin.php?page=fluent-booking#/settings/configure-integrations/outlook_calendar'),
            ];
            return $calendars;
        }, 10, 2);
        add_filter('fluent_booking/remote_calendar_connection_feeds', [$this, 'pushOutlookFeeds'], 10, 2);
        add_action('fluent_calendar/patch_calendar_config_settings__outlook_user_token', function ($conflictIds, $meta) {
            $meta = Meta::where('object_type', '_outlook_user_token')
                ->where('id', $meta->id)
                ->first();
            $settings = $meta->value;
            $settings['conflict_check_ids'] = $conflictIds;
            $meta->value = $settings;
            $meta->save();
        }, 10, 2);
        add_action('fluent_calendar/disconnect_remote_calendar__outlook_user_token', function ($meta) {
            // Let's remove the cache first
            CalendarCache::deleteAllParentCache($meta->id);
            (new OutlookCalendar($meta))->revoke();
            $meta->delete();
        });

        /*
         * Booking Handlers
         */
        add_filter('fluent_booking/booked_events', [$this, 'pushBookedSlots'], 10, 5);
        add_action('fluent_booking/create_remote_calendar_event_outlook', [$this, 'createRemoteCalendarEvent'], 10, 3);
        add_action('fluent_booking/update_remote_calendar_event_outlook', [$this, 'updateRemoteCalendarEvent'], 10, 4);
        add_action('fluent_booking/update_attendees_remote_calendar_event_outlook', [$this, 'updateAttendeesRemoteCalendarEvent'], 10, 3);

        add_action('fluent_booking/existing_event_attendees_async_add', [$this, 'addNewAttendee'], 10, 3);
        add_action('fluent_booking/existing_event_attendees_async_remove', [$this, 'removeExistingAttendee'], 10, 3);


        add_action('fluent_booking/before_get_all_calendars', function () {
            if (!OutlookHelper::isConfigured()) {
                return;
            }
            // Show the Google last error
            add_action('fluent_booking/calendar', function (&$calendar, $type) {
                if ($type != 'lists') {
                    return $calendar;
                }

                $meta = Meta::where('object_type', '_outlook_user_token')
                    ->where('object_id', $calendar->user_id)
                    ->first();

                if (!$meta || empty(Arr::get($meta->value, 'last_error'))) {
                    return $calendar;
                }
                $error = Arr::get($meta->value, 'last_error');
                $calendar->generic_error = '<p style="color: red; margin:0;">' . __('Outlook Calendar API Error:', 'fluent-booking-pro') . ' ' . $error . '. <a href="' . Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars') . '">' . __('Click Here to Review', 'fluent-booking-pro') . '</a></p>';
            }, 10, 2);
        });

    }

    public function pushOutlookFeeds($feeds, $userId)
    {
        $items = Meta::where('object_type', '_outlook_user_token')
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
                'driver'             => 'outlook',
                'db_id'              => $item->id,
                'identifier'         => $item->key,
                'remote_calendars'   => $remoteCalendars,
                'errors'             => $errors,
                'conflict_check_ids' => Arr::get($item->value, 'conflict_check_ids', [])
            ];
        }

        return $feeds;
    }

    public function handleAuthCallback()
    {
        if (!isset($_GET['code'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $code = sanitize_text_field($_GET['code']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $userId = sanitize_text_field($_GET['state']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $calendar = Calendar::where('user_id', $userId)->first();

        if (!$calendar || !PermissionManager::hasCalendarAccess($calendar)) {
            return;
        }

        $client = OutlookHelper::getApiClient();

        $response = $client->generateAuthCode($code);

        if (is_wp_error($response)) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Failed to connect Calendar API', 'fluent-booking-pro'),
                'body'     => __('Outlook API Response Error:', 'fluent-booking-pro') . ' ' . $response->get_error_message(),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),
                'btn_text' => __('Back to Calendars Configuration', 'fluent-booking-pro')
            ]);
            return;
        }

        $requiredScopes = [];
        // Verify the scopes
        $returnedScope = $response['scope'];
        if (!strpos($returnedScope, 'ndars.ReadWrite')) {
            $requiredScopes[] = 'Calendars.ReadWrite';
        }

        if ($requiredScopes) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Required scopes missing', 'fluent-booking-pro'),
                'body'     => __('Looks like you did not allow the required scopes. Please try again with the following scopes:', 'fluent-booking-pro') . ' ' . implode(', ', $requiredScopes),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),
                'btn_text' => __('Back to Calendars Configuration', 'fluent-booking-pro')
            ]);
        }


        $userEmail = OutlookHelper::getEmailByIdToken($response['id_token']);

        $response['expires_in'] += time();
        $response['access_token'] = Helper::encryptKey($response['access_token']);
        $response['refresh_token'] = Helper::encryptKey($response['refresh_token']);

        $data = Arr::only($response, ['access_token', 'expires_in', 'refresh_token', 'token_type']);
        $data['remote_email'] = $userEmail;
        $this->addFeedIntegration($userId, $data);

        wp_redirect(Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),);
        exit;
    }

    public function pushBookedSlots($books, $calendarSlot, $toTimeZone, $bookingRequest, $dateRange)
    {
        $config = OutlookHelper::getApiConfig();

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return $books;
        }

        $cacheTime = Arr::get($config, 'caching_time', 5);

        $items = OutlookHelper::getConflictCheckCalendars($calendarSlot->user_id);

        if (!$items) {
            return $books;
        }

        $start = date('Y-m-d 00:00:00', strtotime($dateRange[0]) - 86400); // just the previous day
        $fromDate = new \DateTime($start, new \DateTimeZone('UTC'));

        $toDate = new \DateTime($dateRange[1], new \DateTimeZone('UTC'));
        $toDate->modify('first day of next month');
        $toDate->setTime(23, 59, 59);

        $startDate = $fromDate->format('Y-m-d\TH:i:s\Z');
        $endDate = $toDate->format('Y-m-d\TH:i:s\Z');
        $cacheKeyPrefix = $toDate->format('YmdHis');

        $allRemoteBookedSlots = [];


        foreach ($items as $item) {
            $meta = $item['item'];
            $calendarApi = new OutlookCalendar($meta);

            if ($calendarApi->lastError) {
                continue;
            }

            foreach ($item['check_ids'] as $remoteId) {
                $cacheKey = md5($cacheKeyPrefix . '_' . $remoteId);
                $remoteSlots = CalendarCache::getCache($meta->id, $cacheKey, function () use ($calendarApi, $startDate, $endDate, $remoteId) {
                    $events = $calendarApi->getCalendarEvents($remoteId, [
                        'startDateTime' => $startDate,
                        'endDateTime' => $endDate
                    ]);

                    if (is_wp_error($events)) {
                        if ($events->get_error_code() == 'api_error') {
                            return []; // it's an api error so let's not call again and again
                        }

                        return $events; //  it's an wp error so we will call again
                    }

                    // We have to format it appropriately
                    return $events;
                }, $cacheTime * 60);

                if ($remoteSlots && !is_wp_error($remoteSlots)) {
                    $allRemoteBookedSlots = array_merge($allRemoteBookedSlots, $remoteSlots);
                }
            }
        }

        foreach ($allRemoteBookedSlots as $slot) {
            $start = RemoteCalendarHelper::convertToTimeZoneOffset($slot['start'], $toTimeZone, Arr::get($slot, 'rec_start'));
            $end = RemoteCalendarHelper::convertToTimeZoneOffset($slot['end'], $toTimeZone, Arr::get($slot, 'rec_start'));
            $date = date('Y-m-d', strtotime($start));

            if (!isset($books[$date])) {
                $books[$date] = [];
            }

            $books[$date][] = [
                'type'     => 'remote',
                'start'    => $start,
                'end'      => $end,
                'source'   => 'outlook',
                'event_id' => null
            ];
        }

        return $books;
    }

    public function createRemoteCalendarEvent($config, Booking $booking, CalendarSlot $slot)
    {
        $calendar = $booking->calendar;
        if (!$calendar) {
            return false;
        }

        if ($booking->getMeta('__outlook_calendar_event')) {
            return false; // Already created
        }

        $meta = Meta::where('object_type', '_outlook_user_token')
            ->where('object_id', $calendar->user_id)
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

        $booking = Booking::find($booking->id);

        $api = new OutlookCalendar($meta);

        if ($api->lastError) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to connect with Outlook calendar API. API Response: %s', $api->lastError->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        $guestAttendee = array_filter([
            'display_name' => trim($booking->first_name . ' ' . $booking->last_name),
            'email'        => $booking->email,
            'comment'      => $booking->message
        ]);

        $author = $slot->getAuthorProfile(false);

        $data = [
            'start'              => [
                'dateTime' => date('Y-m-d\TH:i:s\Z', strtotime($booking->start_time))
            ],
            'end'                => [
                'dateTime' => date('Y-m-d\TH:i:s\Z', strtotime($booking->end_time))
            ],
            'attendees'          => [
                $guestAttendee,
                [
                    'display_name' => $author['name'],
                    'email'        => $author['email']
                ]
            ],
            'source'             => [
                'title' => $slot->title,
                'url'   => $booking->source_url
            ],
            'location'           => $booking->getLocationAsText(),
            'summary'            => __(sprintf('%d Min Meeting between %1s and %2s', $booking->slot_minutes, $author['name'], trim($booking->first_name . ' ' . $booking->last_name)), 'fluent-booking-pro'),
            'extendedProperties' => [
                'shared' => [
                    'created_by' => 'fluent-booking-pro',
                    'site_uid'   => OutlookHelper::getUniqueSiteIdHash(),
                    'event_id'   => $slot->id,
                    'booking_id' => $booking->id
                ],
            ],
        ];

        if ($booking->message && $booking->event_type == 'single') {
            $data['description'] = __('Note: ', 'fluent-booking-pro') . $booking->message;
        }

        $isGoogleMeet = false;

        if (Arr::get($booking->location_details, 'type') == 'google_meet') {
            $data['conferenceData'] = [
                'createRequest' => [
                    'requestId'             => $booking->hash,
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet'
                    ]
                ]
            ];

            $isGoogleMeet = true;
        }

        $data = apply_filters('fluent_booking/outlook_event_data', $data, $booking, $slot);

        $response = $api->createEvent($config['remote_calendar_id'], $data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to create event in Outlook calendar. API Response: %s', $response->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        $responseData = [
            'id'                 => $response['id'],
            'remote_link'        => $response['htmlLink'],
            'remote_calendar_id' => $config['remote_calendar_id'],
            'access_db_id'       => $meta->id,
        ];

        if ($isGoogleMeet && !empty($response['hangoutLink'])) {
            $responseData['google_meet_link'] = $response['hangoutLink'];
            $location = $booking->location_details;
            $location['online_platform_link'] = $responseData['google_meet_link'];
            $booking->location_details = $location;
            $booking->save();
        }

        $booking->updateMeta('__outlook_calendar_event', $responseData);

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Outlook Calendar event created', 'fluent-booking-pro'),
            'description' => __(sprintf('Outlook calendar event has been created. %s', '<a target="_blank" href="' . $response['htmlLink'] . '">' . __('View on Google Calendar', 'fluent-booking-pro') . '</a>'), 'fluent-booking-pro')
        ]);

        return true;
    }

    public function updateRemoteCalendarEvent($config, $calendar, $booking, $data)
    {
        if (!$calendar) {
            return false;
        }

        $bookingMeta = $booking->getMeta('__outlook_calendar_event');

        if (!$bookingMeta) {
            return false; // Nothing to update as there is no previous response of this booking
        }

        $meta = Meta::where('object_type', '_goutlook_user_token')
            ->where('object_id', $calendar->user_id)
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

        $api = new OutlookCalendar($meta);

        if ($api->lastError) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to connect with Outlook calendar API. API Response: %s', $api->lastError->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        $googleEventId = Arr::get($bookingMeta, 'id');

        $data = apply_filters('fluent_booking/google_event_data', $data, $booking, $calendar);

        $response = $api->patchEvent($config['remote_calendar_id'], $googleEventId, $data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to update event in Outlook calendar. API Response: %s', $api->lastError->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        $responseData = [
            'id'                 => $response['id'],
            'remote_link'        => $response['htmlLink'],
            'remote_calendar_id' => $config['remote_calendar_id'],
            'access_db_id'       => $meta->id,
        ];

        $booking->updateMeta('__google_calendar_event', $responseData);

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Outlook Calendar event updated', 'fluent-booking-pro'),
            'description' => __(sprintf('Outlook calendar event has been updated. %s', '<a target="_blank" href="' . $response['htmlLink'] . '">' . __('View on Google Calendar', 'fluent-booking-pro') . '</a>'), 'fluent-booking-pro')
        ]);

        return true;
    }

    public function updateAttendeesRemoteCalendarEvent($config, Booking $booking, $action)
    {
        $calendar = $booking->calendar;
        if (!$calendar) {
            return false;
        }

        if ($booking->getMeta('__outlook_calendar_event')) {
            return false; // Already created
        }

        $meta = Meta::where('object_type', '_outlook_user_token')
            ->where('object_id', $calendar->user_id)
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

        as_enqueue_async_action('fluent_booking/existing_event_attendees_async_' . $action, [
            $config['remote_calendar_id'],
            $config['db_id'],
            $booking->id
        ], 'fluent-booking');
    }

    public function addNewAttendee($calendarId, $dbId, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        $calendar = $booking->calendar;
        if (!$calendar) {
            return false;
        }

        $existingBooking = Booking::where('group_id', $booking->group_id)->first();

        $bookingMeta = $existingBooking->getMeta('__outlook_calendar_event');

        if (!$bookingMeta) {
            return false; // Nothing to add as there is no previous response
        }

        $googleEventId = Arr::get($bookingMeta, 'id');

        $meta = Meta::where('object_type', '_outlook_user_token')
            ->where('object_id', $calendar->user_id)
            ->where('id', $dbId)
            ->first();

        if (!$meta) {
            return false; //  Meta could not be found
        }

        $googleEventId = Arr::get($bookingMeta, 'id');
        $googleMeetLink = Arr::get($bookingMeta, 'ms_team_link');

        if ($googleMeetLink) {
            $location = $booking->location_details;
            $location['online_platform_link'] = $googleMeetLink;
            $booking->location_details = $location;
            $booking->save();
        }

        $api = new OutlookCalendar($meta);

        $updatedEvent = $api->getEvent($calendarId, $googleEventId);

        if (is_wp_error($updatedEvent)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to add attendee in Outlook calendar. API Response: %s', $updatedEvent->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        $attendee = array_filter([
            'display_name' => trim($booking->first_name . ' ' . $booking->last_name),
            'email'        => $booking->email,
            'comment'      => $booking->message
        ]);

        $attendees = $updatedEvent['attendees'] ?? [];

        $attendees[] = $attendee;

        $data['attendees'] = $attendees;

        $response = $api->patchEvent($calendarId, $googleEventId, $data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to add attendee in Outlook calendar. API Response: %s', $response->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Attendee Added in Outlook Calendar Event', 'fluent-booking-pro'),
            'description' => __(sprintf('Attendee has been added in Outlook calendar successfully. %s', '<a target="_blank" href="' . $response['htmlLink'] . '">' . __('View on Google Calendar', 'fluent-booking-pro') . '</a>'), 'fluent-booking-pro')
        ]);
    }

    public function removeExistingAttendee($calendarId, $dbId, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        $calendar = $booking->calendar;
        if (!$calendar) {
            return false;
        }

        $existingBooking = Booking::where('group_id', $booking->group_id)->first();

        $bookingMeta = $existingBooking->getMeta('__outlook_calendar_event');

        if (!$bookingMeta) {
            return false; // Nothing to add as there is no previous response
        }

        $googleEventId = Arr::get($bookingMeta, 'id');

        $meta = Meta::where('object_type', '_outlook_user_token')
            ->where('object_id', $calendar->user_id)
            ->where('id', $dbId)
            ->first();

        if (!$meta) {
            return false; //  Meta could not be found
        }

        $api = new OutlookCalendar($meta);


        $updatedEvent = $api->getEvent($calendarId, $googleEventId);

        if (is_wp_error($updatedEvent)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to add attendee in Outlook calendar. API Response: %s', $updatedEvent->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        $attendees = $updatedEvent['attendees'] ?? [];

        $attendeeIndex = array_search($booking->email, array_column($attendees, 'email'));

        if ($attendeeIndex !== false) {
            unset($attendees[$attendeeIndex]);
        }

        $data = [
            'attendees' => array_values($attendees) // Reindex the array after removing the attendee
        ];

        $response = $api->patchEvent($calendarId, $googleEventId, $data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to remove attendee from Outlook calendar. API Response: %s', $response->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Attendee Added in Outlook Calendar Event', 'fluent-booking-pro'),
            'description' => __(sprintf('Attendee has been removed from Outlook calendar successfully. %s', '<a target="_blank" href="' . $response['htmlLink'] . '">' . __('View on Google Calendar', 'fluent-booking-pro') . '</a>'), 'fluent-booking-pro')
        ]);
    }

    private function addFeedIntegration($userId, $tokenData)
    {
        $exist = Meta::where('object_type', '_outlook_user_token')
            ->where('object_id', $userId)
            ->where('key', $tokenData['remote_email'])
            ->first();

        if ($exist) {
            $exist->value = $tokenData;
            $exist->save();
            return $exist;
        }

        return Meta::create([
            'object_type' => '_outlook_user_token',
            'object_id'   => $userId,
            'key'         => $tokenData['remote_email'],
            'value'       => $tokenData
        ]);
    }

    private function getRemoteCalendarsList($item, $fromApi = false)
    {
        $settings = $item->value;
        if (!empty($settings['calendar_lists']) && !$fromApi) {
            $lastChecked = Arr::get($settings, 'last_calendar_lists_fetched');
            if ($lastChecked && ($lastChecked + 86400) > time()) {
                return $settings['calendar_lists'];
            }
        }

        $calendarClient = new OutlookCalendar($item);

        if ($calendarClient->lastError) {
            return $calendarClient->lastError;
        }

        $remoteCalendars = $calendarClient->getCalendarLists();
        if (is_wp_error($remoteCalendars)) {
            return $remoteCalendars;
        }

        $calendarClient->updateSettinsValueByKey('calendar_lists', $remoteCalendars);
        $calendarClient->updateSettinsValueByKey('last_calendar_lists_fetched', time());

        return $remoteCalendars;
    }

    protected function getAuthUrl($userId)
    {
        if (!$userId) {
            return '';
        }

        return (OutlookHelper::getApiClient())->getAuthUrl($userId);
    }
}
