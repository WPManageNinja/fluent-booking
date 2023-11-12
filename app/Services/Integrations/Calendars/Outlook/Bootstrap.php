<?php

namespace FluentBooking\App\Services\Integrations\Calendars\Outlook;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\Calendars\BaseCalendar;
use FluentBooking\App\Services\Integrations\Calendars\CalendarCache;
use FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarHelper;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\Framework\Support\Arr;

class Bootstrap extends BaseCalendar
{
    public function __construct()
    {
        $this->calendarKey = 'outlook';
        $app = App::getInstance();
        $this->logo = $app['url.assets'] . 'images/outlook-color.svg';
        $this->calendarTitle = __('Outlook Calendar', 'fluent-booking-pro');
    }

    public function register()
    {
        $this->boot();
        add_action('wp_ajax_fluent_booking_outlook_auth', [$this, 'handleAuthCallback']);
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

    public function getClientSettingsForView($settings)
    {
        $config = OutlookHelper::getApiConfig();
        $config['redirect_url'] = OutlookHelper::getAppRedirectUrl();

        if (!empty($config['constant_defined'])) {
            $config['client_secret'] = '**********';
            $config['client_id'] = '**********';
        } else if (!empty($config['client_secret'])) {
            $config['client_secret'] = '********************';
        }

        return $config;
    }

    public function getClientFieldSettings($settings)
    {
        $fields = $this->getStanadrdFields();

        $config = OutlookHelper::getApiConfig();

        $description = '<p>' . __('Login to your Outlook account, go to Azure Cloud Console, create a project, complete OAuth Consent screen process, click on Create Credentials, and you will get your client id and secret key. If you get the ID and Keys for Outlook Calendar. For full details read the', 'fluent-booking-pro') . ' <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/outlook-calendar-integration-with-fluent-booking/">' . __('documentation', 'fluent-booking-pro') . '</a></p>';

        if (!empty($config['constant_defined'])) {
            $fields = null;
            $description = '<p>' . __('Outlook Calendar integration is configured by wp-config.php constants. No action required here', 'fluent-booking-pro') . '</p>';
        }

        return [
            'logo'          => $this->logo,
            'title'         => $this->calendarTitle,
            'subtitle'      => __('Configure Outlook Calendar to sync your events', 'fluent-booking-pro'),
            'description'   => $description,
            'save_btn_text' => __('Save Outlook API Configuration', 'fluent-booking-pro'),
            'fields'        => $fields,
            'will_encrypt'  => true
        ];
    }

    public function saveClientSettings($settings)
    {
        OutlookHelper::updateApiConfig($settings);
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

    public function pushFeeds($feeds, $userId)
    {
        if (!$this->isConfigured()) {
            return $feeds;
        }

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

    public function getBookedSlots($books, $calendarSlot, $toTimeZone, $dateRange, $isDoingBooking)
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
                        'endDateTime'   => $endDate
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

    public function createEvent($config, Booking $booking)
    {
        if ($booking->status != 'scheduled' || $booking->getMeta('__outlook_calendar_event')) {
            return; // already created
        }

        $calendarApi = OutlookHelper::getApiClientByUserId($booking->host_user_id);
        if (!$calendarApi) {
            return;
        }

        $isValid = false;

        $calendarLists = Arr::get($calendarApi->settings, 'calendar_lists', []);
        $remoteCreateId = Arr::get($config, 'remote_calendar_id');

        foreach ($calendarLists as $item) {
            if ($isValid || Arr::get($item, 'can_write') != 'yes') {
                continue;
            }
            if (Arr::get($item, 'id') == $remoteCreateId) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            return false; // invalid id of the remote calendar
        }


        if ($calendarApi->lastError) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to connect with Outlook calendar API. API Response: %s', $calendarApi->lastError->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        $guestAttendee = [
            'emailAddress' => array_filter([
                'name'    => trim($booking->first_name . ' ' . $booking->last_name),
                'address' => $booking->email
            ]),
            'type'         => 'required'
        ];

        $author = $booking->getHostDetails(false);

        $data = [
            'start'                 => [
                'dateTime' => date('Y-m-d\TH:i:s', strtotime($booking->start_time)),
                'timeZone' => 'UTC'
            ],
            'end'                   => [
                'dateTime' => date('Y-m-d\TH:i:s', strtotime($booking->end_time)),
                'timeZone' => 'UTC'
            ],
            'attendees'             => [
                $guestAttendee,
            ],
            'organizer'             => [
                'emailAddress' => [
                    'name'    => $author['name'],
                    'address' => $author['email']
                ]
            ],
            'allowNewTimeProposals' => false,
            'location'              => [
                'displayName' => $booking->getLocationAsText(),
            ],
            'subject'               => $booking->getMeetingTitle(),
            'transactionId'         => $booking->id,
        ];

        if ($booking->message && $booking->event_type == 'single') {
            $data['body'] = [
                'contentType' => 'html',
                'content'     => sprintf(__('Note: %s', 'fluent-booking-pro'), $booking->message)
            ];
        }

        $data = apply_filters('fluent_booking/outlook_event_data', $data, $booking);
        $response = $calendarApi->createEvent($config['remote_calendar_id'], $data);

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
            'remote_link'        => $response['webLink'],
            'remote_calendar_id' => $config['remote_calendar_id'],
            'access_db_id'       => $calendarApi->getMetaModel()->id,
        ];

        if (!empty($response['onlineMeeting']['joinUrl'])) {
            $responseData['ms_team_link'] = $response['onlineMeeting']['joinUrl'];
            $location = $booking->location_details;
            $location['online_platform_link'] = $response['onlineMeeting']['joinUrl'];
            $booking->location_details = $location;
            $booking->save();
        }

        $booking->updateMeta('__outlook_calendar_event', $responseData);

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Outlook Calendar event created', 'fluent-booking-pro'),
            'description' => __(sprintf('Outlook calendar event has been created. %s', '<a target="_blank" href="' . $response['webLink'] . '">' . __('View on Outlook Calendar', 'fluent-booking-pro') . '</a>'), 'fluent-booking-pro')
        ]);

        return true;
    }

    public function maybeAddOrRemoveGroupMembers($config, $booking, $allGroupBookings, $isRescheduling)
    {
        $parentMeta = null;

        $missingEventBookings = [];

        foreach ($allGroupBookings as $parentBooking) {
            $meta = $parentBooking->getMeta('__outlook_calendar_event', []);
            if (!$meta) {
                $missingEventBookings[] = $parentBooking;
            } else if (!$parentMeta) {
                $parentMeta = $meta;
            }
        }

        if (!$parentMeta || empty($parentMeta['id'])) {
            return $this->createEvent($config, $booking);
        }

        $parentEventId = $parentMeta['id'];
        $attendees = [];

        foreach ($allGroupBookings as $groupBooking) {
            if ($groupBooking->status != 'scheduled') {
                continue;
            }
            $attendees[] = [
                'emailAddress' => [
                    'name'    => trim($groupBooking->first_name . ' ' . $groupBooking->last_name),
                    'address' => $groupBooking->email
                ],
                'type'         => 'required'
            ];
        }

        if (!$attendees) {
            return;
        }

        $calendarApi = OutlookHelper::getApiClientByUserId($booking->host_user_id);
        if (!$calendarApi) {
            return false;
        }

        if ($calendarApi->lastError) {
            if (!$isRescheduling) {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'error',
                    'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                    'description' => __(sprintf('Failed to connect with Outlook calendar API. API Response: %s', $calendarApi->lastError->get_error_message()), 'fluent-booking-pro')
                ]);
            }
            return false;
        }

        $response = $calendarApi->patchEvent($parentEventId, [
            'attendees'     => $attendees,
            'hideAttendees' => true
        ]);

        if (is_wp_error($response)) {
            if (!$isRescheduling) {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'error',
                    'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                    'description' => __(sprintf('Failed to connect with Outlook calendar API. API Response: %s', $response->get_error_message()), 'fluent-booking-pro')
                ]);
            }
            return false;
        }

        if (!$isRescheduling) {
            if ($booking->status == 'scheduled') {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'info',
                    'title'       => __('Added to Outlook calendar', 'fluent-booking-pro'),
                    'description' => __('Guest has been added to outlook calendar event', 'fluent-booking-pro')
                ]);
            } else if ($booking->status == 'cancelled') {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'info',
                    'title'       => __('Removed from Outlook calendar', 'fluent-booking-pro'),
                    'description' => __('Guest has been removed from outlook calendar event', 'fluent-booking-pro')
                ]);
            }
        }

        foreach ($missingEventBookings as $missingBooking) {
            if (!empty($parentMeta['onlineMeeting']['joinUrl'])) {
                $location = $missingBooking->location_details;
                if (empty($location['online_platform_link'])) {
                    $location['online_platform_link'] = $parentMeta['onlineMeeting']['joinUrl'];
                    $missingBooking->location_details = $location;
                    $booking->save();
                }
            }

            if ($missingBooking->status != 'cancelled') {
                $missingBooking->updateMeta('__outlook_calendar_event', $parentMeta);
            }

            return true;
        }
    }

    public function patchEvent($config, Booking $booking, $updateData, $isRescheduling)
    {
        $bookingMeta = $booking->getMeta('__outlook_calendar_event');

        if (!$bookingMeta || empty($bookingMeta['id'])) {
            return $this->createEvent($config, $booking);
        }

        $data = [
            'start' => [
                'dateTime' => date('Y-m-d\TH:i:s', strtotime($booking->start_time)),
                'timeZone' => 'UTC'
            ],
            'end'   => [
                'dateTime' => date('Y-m-d\TH:i:s', strtotime($booking->end_time)),
                'timeZone' => 'UTC'
            ],
        ];

        $calendarApi = OutlookHelper::getApiClientByUserId($booking->host_user_id);
        if (!$calendarApi) {
            return false;
        }

        if ($calendarApi->lastError) {
            if (!$isRescheduling) {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'error',
                    'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                    'description' => __(sprintf('Failed to connect with Outlook calendar API. API Response: %s', $calendarApi->lastError->get_error_message()), 'fluent-booking-pro')
                ]);
            }
            return false;
        }

        $response = $calendarApi->patchEvent($bookingMeta['id'], $data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to connect with Outlook calendar API. API Response: %s', $response->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'error',
            'title'       => __('Outlook Event Updated', 'fluent-booking-pro'),
            'description' => __('Event in outlook has been updated with new dates', 'fluent-booking-pro')
        ]);
    }

    public function cancelEvent($config, Booking $booking)
    {
        $bookingMeta = $booking->getMeta('__outlook_calendar_event');

        if (!$bookingMeta) {
            return false; // Nothing to update as there is no previous response of this booking
        }

        $calendarApi = OutlookHelper::getApiClientByUserId($booking->host_user_id);
        if (!$calendarApi) {
            return;
        }


        if ($calendarApi->lastError) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to connect with Outlook calendar API. API Response: %s', $calendarApi->lastError->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        $outlookEventId = Arr::get($bookingMeta, 'id');

        if (!$outlookEventId) {
            return false;
        }

        // Let's cancel the event
        $response = $calendarApi->deleteEvent($outlookEventId);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                'description' => __(sprintf('Failed to delete event in Outlook calendar. API Response: %s', $response->get_error_message()), 'fluent-booking-pro')
            ]);
            return false;
        }

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Outlook event has been deleted', 'fluent-booking-pro'),
            'description' => __('Outlook calendar event has been deleted', 'fluent-booking-pro')
        ]);

        return true;
    }

    public function authDisconnect($meta)
    {
        // Let's remove the cache first
        CalendarCache::deleteAllParentCache($meta->id);
        (new OutlookCalendar($meta))->revoke();
        $meta->delete();
    }

    public function getAuthUrl($userId = null)
    {
        if (!$userId) {
            return '';
        }

        return (OutlookHelper::getApiClient())->getAuthUrl($userId);
    }

    public function isConfigured()
    {
        return OutlookHelper::isConfigured();
    }

    /*
     * Internals
     */
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
}
