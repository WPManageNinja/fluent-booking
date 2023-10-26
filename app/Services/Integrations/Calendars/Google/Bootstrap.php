<?php

namespace FluentBooking\App\Services\Integrations\Calendars\Google;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\DateTimeHelper;
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
            $menuItems['google_calendar'] = [
                'title'          => __('Google Calendar / Meet', 'fluent-booking-pro'),
                'icon_url'       => $app['url.assets'] . 'images/google-calendar.svg',
                'component_type' => 'GlobalSettingsComponent',
                'route'          => [
                    'name'   => 'configure-integrations',
                    'params' => [
                        'settings_key' => 'google_calendar'
                    ]
                ]
            ];
            return $menuItems;
        }, 10, 1);

        add_filter('fluent_booking/get_client_settings_google_calendar', function ($settings) {
            $config = GoogleHelper::getApiConfig();
            $config['redirect_url'] = GoogleHelper::getAppRedirectUrl();

            if (!empty($config['constant_defined'])) {
                $config['client_secret'] = '**********';
                $config['client_id'] = '**********';
            } else if (!empty($config['client_secret'])) {
                $config['client_secret'] = '********************';
            }

            return $config;
        });
        add_filter('fluent_booking/get_client_field_settings_google_calendar', function ($items) {

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
                        '1'  => '1 minute',
                        '5'  => '5 minutes',
                        '10' => '10 minutes',
                        '15' => '15 minutes',
                    ],
                    'label'       => __('Caching Time', 'fluent-booking-pro'),
                    'inline_help' => __('Select for how many minutes the Google Calendar event API call will be cached. Recommended 5/10 minutes. If you add lots of manual events in google then you may lower the value', 'fluent-booking-pro')
                ],
            ];

            $config = GoogleHelper::getApiConfig();

            $description = '<p>Login to your Google account, go to Google Cloud Console, create a project, complete OAuth Consent screen process, click on Create Credentials, and you will get your client id and secret key. If you get the ID and Keys for Google Calendar, Google Meet will be integrated automatically. For full details read the <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/google-calendar-meet-integration-with-fluent-booking/">documentation</a></p>';

            if (!empty($config['constant_defined'])) {
                $fields = null;
                $description = '<p>Google Calendar/Meet integration is configured by wp-config.php constants. No action required here</p>';
            }

            return [
                'logo'          => $app['url.assets'] . 'images/google-calendar.svg',
                'title'         => __('Google Calendar / Meet', 'fluent-booking-pro'),
                'subtitle'      => __('Configure Google Calendar/Meet to sync your events', 'fluent-booking-pro'),
                'description'   => $description,
                'save_btn_text' => __('Save Google API Configuration', 'fluent-booking-pro'),
                'fields'        => $fields,
                'will_encrypt'  => true
            ];
        });

        add_filter('fluent_booking/get_location_fields', function ($fields, $calendar) {
            $meetExist = Meta::where('object_type', '_google_user_token')
                ->where('object_id', $calendar->user_id)
                ->first();

            $message = !$meetExist ? ' '.__('(Connect Google Meet First)', 'fluent-booking-pro') : '';

            if (!$message) {
                // now check if the user calendar event create enabled
                $calConfig = RemoteCalendarHelper::getUserRemoteCreatableCalendarSettings($calendar->user_id);
                if (!$calConfig || Arr::get($calConfig, 'driver') != 'google') {
                    $message = ' (Set Google Event Creat First)';
                    $meetExist = false;
                }
            }

            $fields['conferencing']['options']['google_meet'] = [
                'title'         => 'Google Meet' . $message,
                'disabled'      => !$meetExist,
                'location_type' => 'conferencing'
            ];
            return $fields;
        }, 10, 2);

        add_action('fluent_booking/save_client_settings_google_calendar', function ($settings) {
            GoogleHelper::updateApiConfig($settings);
        });
        add_action('wp_ajax_fluent_booking_g_auth', [$this, 'handleAuthCallback']);

        /*
         * oAuth From Handlers from Calendar
         */
        add_filter('fluent_booking/remote_calendar_providers', function ($calendars, $userId = null) {
            $app = App::getInstance();
            $calendars['google'] = [
                'key'                  => 'google',
                'icon'                 => $app['url.assets'] . 'images/google-calendar.svg',
                'title'                => __('Google Calendar', 'fluent-booking-pro'),
                'subtitle'             => __('Configure Google Calendar/Meet to sync your events', 'fluent-booking-pro'),
                'btn_text'             => __('Connect with Google Calendar', 'fluent-booking-pro'),
                'auth_url'             => $this->getAuthUrl($userId),
                'is_global_configured' => GoogleHelper::isConfigured(),
                'global_config_url'    => admin_url('admin.php?page=fluent-booking#/settings/configure-integrations/google_calendar'),
            ];
            return $calendars;
        }, 10, 2);
        add_filter('fluent_booking/remote_calendar_connection_feeds', [$this, 'pushGoogleFeeds'], 10, 2);
        add_action('fluent_calendar/patch_calendar_config_settings__google_user_token', function ($conflictIds, $meta) {
            $meta = Meta::where('object_type', '_google_user_token')
                ->where('id', $meta->id)
                ->first();
            $settings = $meta->value;
            $settings['conflict_check_ids'] = $conflictIds;
            $meta->value = $settings;
            $meta->save();
        }, 10, 2);
        add_action('fluent_calendar/disconnect_remote_calendar__google_user_token', function ($meta) {
            // Let's remove the cache first
            CalendarCache::deleteAllParentCache($meta->id);
            (new GoogleCalendar($meta))->revoke();
            $meta->delete();
        });

        /*
         * Booking Handlers
         */
        add_filter('fluent_booking/booked_events', [$this, 'pushBookedSlots'], 10, 5);
        add_action('fluent_booking/create_remote_calendar_event_google', [$this, 'createRemoteCalendarEvent'], 10, 3);
        add_action('fluent_booking/update_remote_calendar_event_google', [$this, 'updateRemoteCalendarEvent'], 10, 4);
        add_action('fluent_booking/update_attendees_remote_calendar_event_google', [$this, 'updateAttendeesRemoteCalendarEvent'], 10, 3);

        add_action('fluent_booking/existing_event_attendees_async_add', [$this, 'addNewAttendee'], 10, 3);
        add_action('fluent_booking/existing_event_attendees_async_remove', [$this, 'removeExistingAttendee'], 10, 3);
    }

    public function pushGoogleFeeds($feeds, $userId)
    {
        $items = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $userId)
            ->get();

        $formattedFeeds = [];
        foreach ($items as $item) {

            CalendarCache::deleteAllParentCache($item->id);

            $errors = '';

            $remoteCalendars = $this->getRemoteCalendarsList($item, true);

            if (is_wp_error($remoteCalendars)) {
                $errors = $remoteCalendars->get_error_message() . ' Please remove the connection and reconnect again.';
                $remoteCalendars = [];
            }

            $formattedFeeds[] = [
                'driver'             => 'google',
                'db_id'              => $item->id,
                'identifier'         => $item->key,
                'remote_calendars'   => $remoteCalendars,
                'errors'             => $errors,
                'conflict_check_ids' => Arr::get($item->value, 'conflict_check_ids', [])
            ];
        }

        return $formattedFeeds;
    }

    public function handleAuthCallback()
    {
        if (!isset($_GET['code'], $_GET['scope'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }


        $code = sanitize_text_field($_GET['code']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $scope = sanitize_text_field($_GET['scope']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $userId = sanitize_text_field($_GET['state']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $calendar = Calendar::where('user_id', $userId)->first();

        if (!$calendar || !PermissionManager::hasCalendarAccess($calendar)) {
            return;
        }

        $client = GoogleHelper::getApiClient();

        $response = $client->generateAuthCode($code);

        if (is_wp_error($response)) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Failed to connect Calendar API', 'fluent-booking-pro'),
                'body'     => 'Google API Response Error: ' . $response->get_error_message(),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),
                'btn_text' => 'Back to Calendars Configuration'
            ]);
            return;
        }

        $requiredScopes = [];

        // Verify the scopes
        $returnedScope = $response['scope'];
        if (!strpos($returnedScope, 'googleapis.com/auth/calendar.events') !== false) {
            $requiredScopes[] = 'https://www.googleapis.com/auth/calendar.events';
        }

        if (!strpos($returnedScope, 'googleapis.com/auth/calendar.readonly') !== false) {
            $requiredScopes[] = 'https://www.googleapis.com/auth/calendar.readonly';
        }

        if ($requiredScopes) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Required scopes missing', 'fluent-booking-pro'),
                'body'     => 'Looks like you did not allow the required scopes. Please try again with the following scopes: ' . implode(', ', $requiredScopes),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),
                'btn_text' => 'Back to Calendars Configuration'
            ]);
        }

        $calendarEmail = GoogleHelper::getEmailByIdToken($response['id_token']);

        if (is_wp_error($calendarEmail)) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Google API Error', 'fluent-booking-pro'),
                'body'     => __('We could not authenticate your account. Please try again later.', 'fluent-booking-pro'),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),
                'btn_text' => __('Back to Calendars Configuration', 'fluent-booking-pro')
            ]);
        }

        unset($response['id_token']);
        $response['remote_email'] = $calendarEmail;

        $response['expires_in'] += time();
        $response['access_token'] = Helper::encryptKey($response['access_token']);
        $response['refresh_token'] = Helper::encryptKey($response['refresh_token']);

        $this->addFeedIntegration($userId, $response);

        wp_redirect(Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),);
        exit;
    }

    public function pushBookedSlots($books, $calendarSlot, $toTimeZone, $bookingRequest, $dateRange)
    {
        $config = GoogleHelper::getApiConfig();

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return $books;
        }

        $cacheTime = Arr::get($config, 'caching_time', 5);

        $items = GoogleHelper::getConflictCheckCalendars($calendarSlot->user_id);

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
            $calendarApi = new GoogleCalendar($meta);

            if ($calendarApi->lastError) {
                continue;
            }

            foreach ($item['check_ids'] as $remoteId) {
                $cacheKey = md5($cacheKeyPrefix . '_' . $remoteId);
                $remoteSlots = CalendarCache::getCache($meta->id, $cacheKey, function () use ($calendarApi, $startDate, $endDate, $remoteId) {
                    $events = $calendarApi->getCalendarEvents($remoteId, [
                        'timeMin' => $startDate,
                        'timeMax' => $endDate
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

                if ($remoteSlots && !is_wp_error( $remoteSlots )) {
                    $allRemoteBookedSlots = array_merge($allRemoteBookedSlots, $remoteSlots);
                }
            }
        }

        foreach ($allRemoteBookedSlots as $slot) {
            $start = DateTimeHelper::convertToTimeZone($slot['start'], 'UTC', $toTimeZone);
            $end = DateTimeHelper::convertToTimeZone($slot['end'], 'UTC', $toTimeZone);
            $date = date('Y-m-d', strtotime($start));

            if (!isset($books[$date])) {
                $books[$date] = [];
            }

            $books[$date][] = [
                'type'     => 'remote',
                'start'    => $start,
                'end'      => $end,
                'source'   => 'google',
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
        
        if ($booking->getMeta('__google_calendar_event')) {
            return false; // Already created
        }

        $meta = Meta::where('object_type', '_google_user_token')
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

        $api = new GoogleCalendar($meta);

        if ($api->lastError) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Google Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to connect with google calendar API. API Response: %s', $api->lastError->get_error_message()), 'fluent-booking-pro')
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
            'location'  => $booking->getLocationAsText(),
            'summary'            => __(sprintf('%d Min Meeting between %1s and %2s', $booking->slot_minutes, $author['name'], trim($booking->first_name . ' ' . $booking->last_name)), 'fluent-booking-pro'),
            'extendedProperties' => [
                'shared' => [
                    'created_by' => 'fluent-booking-pro',
                    'site_uid'   => GoogleHelper::getUniqueSiteIdHash(),
                    'event_id'   => $slot->id,
                    'booking_id' => $booking->id
                ],
            ],
        ];

        if ($booking->message && $booking->event_type == 'single') {
            $data['description'] = 'Note: ' . $booking->message;
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

        $data = apply_filters('fluent_booking/google_event_data', $data, $booking, $slot);

        $response = $api->createEvent($config['remote_calendar_id'], $data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Google Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to create event in Google calendar. API Response: %s', $response->get_error_message()), 'fluent-booking-pro')
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

        $booking->updateMeta('__google_calendar_event', $responseData);

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Google Calendar event created', 'fluent-booking-pro'),
            'description' => __(sprintf('Google calendar event has been created. %s', '<a target="_blank" href="' . $response['htmlLink'] . '">' . __('View on Google Calendar', 'fluent-booking-pro') . '</a>'), 'fluent-booking-pro')
        ]);

        return true;
    }

    public function updateRemoteCalendarEvent($config, $calendar, $booking, $data)
    {
        if (!$calendar) {
            return false;
        }

        $bookingMeta = $booking->getMeta('__google_calendar_event');

        if (!$bookingMeta) {
            return false; // Nothing to update as there is no previous response of this booking
        }

        $meta = Meta::where('object_type', '_google_user_token')
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

        $api = new GoogleCalendar($meta);

        if ($api->lastError) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => 'Google Calendar API Error',
                'description' => __(sprintf('Failed to connect with google calendar API. API Response: %s', $api->lastError->get_error_message()), 'fluent-booking-pro')
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
                'title'       => 'Google Calendar API Error',
                'description' => __(sprintf('Failed to update event in Google calendar. API Response: %s', $api->lastError->get_error_message()), 'fluent-booking-pro')
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
            'title'       => __('Google Calendar event updated', 'fluent-booking-pro'),
            'description' => __(sprintf('Google calendar event has been updated. %s', '<a target="_blank" href="' . $response['htmlLink'] . '">' . __('View on Google Calendar', 'fluent-booking-pro') . '</a>'), 'fluent-booking-pro')
        ]);

        return true;
    }

    public function updateAttendeesRemoteCalendarEvent($config, Booking $booking, $action)
    {
        $calendar = $booking->calendar;
        if (!$calendar) {
            return false;
        }

        if ($booking->getMeta('__google_calendar_event')) {
            return false; // Already created
        }

        $meta = Meta::where('object_type', '_google_user_token')
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

        $bookingMeta = $existingBooking->getMeta('__google_calendar_event');

        if (!$bookingMeta) {
            return false; // Nothing to add as there is no previous response
        }

        $googleEventId = Arr::get($bookingMeta, 'id');

        $meta = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $calendar->user_id)
            ->where('id', $dbId)
            ->first();

        if (!$meta) {
            return false; //  Meta could not be found
        }

        $googleEventId = Arr::get($bookingMeta, 'id');
        $googleMeetLink = Arr::get($bookingMeta, 'google_meet_link');

        if ($googleMeetLink) {
            $location = $booking->location_details;
            $location['online_platform_link'] = $googleMeetLink;
            $booking->location_details = $location;
            $booking->save();
        }

        $api = new GoogleCalendar($meta);

        $updatedEvent = $api->getEvent($calendarId, $googleEventId);

        if (is_wp_error($updatedEvent)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Google Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to add attendee in Google calendar. API Response: %s', $updatedEvent->get_error_message()), 'fluent-booking-pro')
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
                'title'       => __('Google Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to add attendee in Google calendar. API Response: %s', $response->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Attendee Added in Google Calendar Event', 'fluent-booking-pro'),
            'description' => __(sprintf('Attendee has been added in Google calendar successfully. %s', '<a target="_blank" href="' . $response['htmlLink'] . '">' . __('View on Google Calendar', 'fluent-booking-pro') . '</a>'), 'fluent-booking-pro')
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

        $bookingMeta = $existingBooking->getMeta('__google_calendar_event');

        if (!$bookingMeta) {
            return false; // Nothing to add as there is no previous response
        }

        $googleEventId = Arr::get($bookingMeta, 'id');

        $meta = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $calendar->user_id)
            ->where('id', $dbId)
            ->first();

        if (!$meta) {
            return false; //  Meta could not be found
        }

        $api = new GoogleCalendar($meta);


        $updatedEvent = $api->getEvent($calendarId, $googleEventId);

        if (is_wp_error($updatedEvent)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Google Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to add attendee in Google calendar. API Response: %s', $updatedEvent->get_error_message()), 'fluent-booking-pro')
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
                'title'       => __('Google Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to remove attendee from Google calendar. API Response: %s', $response->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Attendee Added in Google Calendar Event', 'fluent-booking-pro'),
            'description' => __(sprintf('Attendee has been removed from Google calendar successfully. %s', '<a target="_blank" href="' . $response['htmlLink'] . '">' . __('View on Google Calendar', 'fluent-booking-pro') . '</a>'), 'fluent-booking-pro')
        ]);
    }

    private function addFeedIntegration($userId, $tokenData)
    {
        $exist = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $userId)
            ->where('key', $tokenData['remote_email'])
            ->first();

        if ($exist) {
            $exist->value = $tokenData;
            $exist->save();
            return $exist;
        }

        return Meta::create([
            'object_type' => '_google_user_token',
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

        $calendarClient = new GoogleCalendar($item);

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

        return (GoogleHelper::getApiClient())->getAuthUrl($userId);
    }
}
