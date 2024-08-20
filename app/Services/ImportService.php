<?php

namespace FluentBooking\App\Services;

use FluentBooking\Framework\Support\Arr;

class ImportService
{
    /*
     * Import host data from JSON
     * @param array|string $data JSON data or array
     * @param bool $userCurrentUser If true, the current user will be used as the host
     * @return \FluentBooking\App\Models\Calendar|\WP_Error
     */
    public function importHostJson($data, $userCurrentUser = true)
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (!$data || !is_array($data) || empty($data['data_type']) || $data['data_type'] !== 'host') {
            return new \WP_Error('invalid_data', 'Invalid data provided');
        }

        $hostData = Arr::only($data, [
            'user_id',
            'title',
            'slug',
            'description',
            'settings',
            'status',
            'type',
            'event_type',
            'account_type',
            'visibility',
            'author_timezone',
            'max_book_per_slot'
        ]);

        if (empty($hostData['title']) || empty($hostData['slug'])) {
            return new \WP_Error('invalid_data', 'Invalid data provided');
        }

        if ($userCurrentUser) {
            $currentUserId = get_current_user_id();
            if ($currentUserId) {
                $user = get_user_by('ID', $currentUserId);
                $hostData['user_id'] = $currentUserId;
                $hostData['author_timezone'] = get_option('timezone_string');
                $userName = $user->user_login;
                if (is_email($userName)) {
                    $userName = explode('@', $userName);
                    $userName = $userName[0] . '-' . time();
                }
                $hostData['slug'] = sanitize_title($userName, '', 'display');
            }

            if ($hostData['user_id'] != $currentUserId) {
                $user = get_user_by('ID', $hostData['user_id']);
            }

            if (!$user) {
                return new \WP_Error('invalid_user', 'Invalid user provided');
            }
        }

        if (empty($hostData['user_id'])) {
            return new \WP_Error('invalid_user', 'Invalid user provided');
        }

        $userId = (int)$hostData['user_id'];
        $user = get_user_by('ID', $userId);
        if (!$user) {
            return new \WP_Error('invalid_user', 'Invalid user provided');
        }

        // Check if any calendar exists with the same slug
        $existingHost = \FluentBooking\App\Models\Calendar::where(function ($q) use ($hostData) {
            $q->where('slug', $hostData['slug'])
                ->orWhere('user_id', $hostData['user_id']);
        })
            ->where('type', $hostData['type'])
            ->first();

        if ($existingHost) {
            return $existingHost;
        }

        if (!Helper::isCalendarSlugAvailable($data['slug'], true)) {
            $data['slug'] .= '-' . time();
        }

        $hostData = array_filter($hostData);
        $createdCalendar = \FluentBooking\App\Models\Calendar::create($hostData);

        foreach (Arr::get($data, 'metas', []) as $hostMeta) {
            $metaData = Arr::only($hostMeta, ['key', 'value']);
            if (empty($metaData['key']) || empty($metaData['value'])) {
                continue;
            }
            $createdCalendar->updateMeta($metaData['key'], $metaData['value']);
        }

        $importedAvailabilities = [];
        // Let's import the availabilities
        foreach (Arr::get($data, 'availabilities', []) as $existingId => $availabilityData) {
            $availability = Arr::only($availabilityData, [
                'key', 'value'
            ]);

            $availability['value']['timezone'] = $hostData['author_timezone'];
            $availability['object_id'] = $createdCalendar->user_id;
            $availabilityModel = \FluentBooking\App\Models\Availability::create($availability);
            $importedAvailabilities[$existingId] = $availabilityModel->id;
        }

        foreach (Arr::get($data, 'events', []) as $eventData) {
            $eventAtts = Arr::only($eventData, [
                'duration', 'title', 'slug', 'description', 'settings', 'availability_type', 'availability_id', 'status', 'type', 'color_schema', 'location_type', 'location_heading', 'location_settings', 'event_type', 'is_display_spots', 'max_book_per_slot'
            ]);

            $availablityId = (int)Arr::get($eventAtts, 'availability_id', 0);
            if ($availablityId && isset($importedAvailabilities[$availablityId])) {
                $eventAtts['availability_id'] = $importedAvailabilities[$availablityId];
            } else if ($importedAvailabilities) {
                $firstKey = array_key_first($importedAvailabilities);
                $eventAtts['availability_id'] = $importedAvailabilities[$firstKey];
            }

            $eventAtts['calendar_id'] = $createdCalendar->id;
            $eventAtts['user_id'] = $createdCalendar->user_id;
            $createdEvent = \FluentBooking\App\Models\CalendarSlot::create($eventAtts);

            foreach (Arr::get($eventData, 'events_meta', []) as $eventMeta) {
                $metaData = Arr::only($eventMeta, ['key', 'value']);
                if (empty($metaData['key']) || empty($metaData['value'])) {
                    continue;
                }

                if ($metaData['key'] == 'email_notifications') {
                    $notifications = $metaData['value'];

                    $formattedNotifications = [];

                    foreach ($notifications as $notificationKey => $notification) {
                        $emailBody = Arr::get($notification, 'email.body');
                        if ($emailBody) {
                            $newImageUrl = FLUENT_BOOKING_URL . 'assets/images/check-mark.png';
                            // Regular expression to match and replace the specific image source URL
                            $pattern = '/(https:\/\/[^"]*?' . preg_quote('assets/images/check-mark.png', '/') . ')/';
                            $emailBody = preg_replace($pattern, $newImageUrl, $emailBody);
                            $notification['email']['body'] = $emailBody;
                        }
                        $formattedNotifications[$notificationKey] = $notification;
                    }

                    $metaData['value'] = $formattedNotifications;
                }

                $createdEvent->updateMeta($metaData['key'], $metaData['value']);
            }
        }

        return $createdCalendar;
    }

    /*
     * Import host data from JSON URL
     * @param string $jsonUrl JSON URL
     * @param bool $userCurrentUser If true, the current user will be used as the host
     * @return \FluentBooking\App\Models\Calendar|\WP_Error
     */
    public function importHostByJSONUrl($jsonUrl, $userCurrentUser = true)
    {
        $response = wp_safe_remote_get($jsonUrl, [
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        // check if the status code is not 200
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new \WP_Error('invalid_response', 'Invalid response from the server');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return $this->importHostJson($body, $userCurrentUser);
    }
}
