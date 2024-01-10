<?php

namespace FluentBooking\App\Models;

use FluentBooking\App\Models\Model;
use FluentBooking\App\Services\SanitizeService;
use FluentBooking\App\Services\AvailabilityService;
use FluentBooking\App\Services\BookingFieldService;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\BookingService;
use FluentBooking\App\Services\EditorShortCodeParser;
use FluentBooking\App\Services\LandingPage\LandingPageHandler;
use FluentBooking\App\Services\LandingPage\LandingPageHelper;
use FluentBooking\App\Services\Integrations\Twilio\TwilioHelper;
use FluentBooking\App\Services\LocationService;
use FluentBooking\Framework\Support\Arr;

class CalendarSlot extends Model
{
    protected $table = 'fcal_calendar_events';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'hash',
        'calendar_id',
        'duration',
        'title',
        'slug',
        'media_id',
        'description',
        'settings',
        'availability_type',
        'availability_id',
        'status',
        'type',
        'color_schema',
        'location_type',
        'location_heading',
        'location_settings',
        'event_type',
        'is_display_spots',
        'max_book_per_slot',
        'created_at',
        'updated_at',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->user_id)) {
                $model->user_id = get_current_user_id();
            }
            $model->hash = md5(wp_generate_uuid4() . time());
        });
    }

    public function setSettingsAttribute($settings)
    {
        $originalSettings = $this->getOriginal('settings');

        $originalSettings = \maybe_unserialize($originalSettings);

        foreach ($settings as $key => $value) {
            $originalSettings[$key] = $value;
        }

        $this->attributes['settings'] = \maybe_serialize($originalSettings);
    }

    public function getSettingsAttribute($settings)
    {
        return \maybe_unserialize($settings);
    }

    public function setLocationSettingsAttribute($locationSettings)
    {
        $this->attributes['location_settings'] = \maybe_serialize($locationSettings);
    }

    public function getLocationSettingsAttribute($locationSettings)
    {
        return \maybe_unserialize($locationSettings);
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class, 'calendar_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isTeamEvent() {
        if ($this->calendar) {
            return $this->calendar->type == 'team';
        }
        
        return $this->event_type == 'round_robin' || $this->event_type == 'collective';
    }

    public function getAuthorProfile($public = true, $userID = null)
    {
        $userID = $userID ?: $this->user_id;

        $user = get_user_by('id', $userID);
        if (!$user) {
            return false;
        }

        $name = trim($user->first_name . ' ' . $user->last_name);

        if (!$name) {
            $name = $user->display_name;
        }

        $data = [
            'ID'     => $user->ID,
            'name'   => $name,
            'avatar' => apply_filters('fluent_booking/author_photo', get_avatar_url($user->ID), $user)
        ];

        if (!$public) {
            $data['email'] = $user->user_email;
        }

        return $data;
    }

    public function getAuthorProfiles($public = true)
    {
        $teamMembers   = [];
        $teamMemberIds = $this->getHostIds();

        foreach ($teamMemberIds as $teamMemberId) {
            $teamMembers[] = $this->getAuthorProfile($public, $teamMemberId);
        }

        return $teamMembers;
    }

    public function isLocationFieldRequired()
    {
        $locationSettings = Arr::get($this, 'location_settings');

        if (count($locationSettings) > 1) {
            return true;
        }

        return false;
    }

    public function isPhoneRequired()
    {
        if (count($this->location_settings) == 1) {
            return Arr::get($this->location_settings, '0.type') == 'phone_guest';
        }

        return false;
    }

    public function isAddressRequired()
    {
        if (count($this->location_settings) == 1) {
            return Arr::get($this->location_settings, '0.type') == 'in_person_guest';
        }

        return false;
    }

    public function getSlotSettingsSchema($calendar)
    {
        return [
            'schedule_type'       => 'weekly_schedules',
            'weekly_schedules'    => Helper::getWeeklyScheduleSchema(),
            'date_overrides'      => [],
            'range_type'          => 'range_days',
            'range_days'          => 60,
            'range_date_between'  => ['', ''],
            'schedule_conditions' => [
                'value' => 4,
                'unit'  => 'hours'
            ],
            'location_fields'     => $calendar->getLocationFields()
        ];
    }

    public function getNotifications($isEdit = false)
    {
        $statuses = $this->getMeta('email_notifications');

        if ($statuses) {

            $defaults = Helper::getDefaultEmailNotificationSettings();

            if ($isEdit) {
                foreach ($defaults as $key => $default) {
                    if (isset($statuses[$key])) {
                        $statuses[$key]['title'] = $default['title'];
                    }
                }
            }

            if (!Arr::get($statuses, 'rescheduled_by_host')) {
                $statuses['rescheduled_by_host'] = $defaults['rescheduled_by_host'];
            }

            if (!Arr::get($statuses, 'rescheduled_by_attendee')) {
                $statuses['rescheduled_by_attendee'] = $defaults['rescheduled_by_attendee'];
            }

            return $statuses;
        }

        return Helper::getDefaultEmailNotificationSettings();
    }

    public function setNotifications($notifications)
    {
        $this->updateMeta('email_notifications', $notifications);
    }

    public function getSmsNotifications($isEdit = false)
    {
        $statuses = $this->getMeta('sms_notifications');

        if ($statuses) {

            if ($isEdit) {
                $defaults = TwilioHelper::getDefaultSmsNotificationSettings();

                foreach ($defaults as $key => $default) {
                    if (isset($statuses[$key])) {
                        $statuses[$key]['title'] = $default['title'];
                    }
                }

            }

            return $statuses;
        }

        return TwilioHelper::getDefaultSmsNotificationSettings();
    }

    public function setSmsNotifications($notifications)
    {
        $this->updateMeta('sms_notifications', $notifications);
    }

    public function getBookingFields()
    {
        return BookingFieldService::getBookingFields($this);
    }

    public function setBookingFields($bookingFields)
    {
        return $this->updateMeta('booking_fields', $bookingFields);
    }

    public function getDuration($duration = null)
    {
        if (Arr::isTrue($this->settings, 'multi_duration.enabled')) {
            if (in_array($duration, Arr::get($this->settings, 'multi_duration.available_durations', []))) {
                return $duration;
            } else {
                return Arr::get($this->settings, 'multi_duration.default_duration', '');
            }
        }

        return $this->duration;
    }

    public function getDefaultDuration()
    {
        if (Arr::isTrue($this->settings, 'multi_duration.enabled')) {
            return Arr::get($this->settings, 'multi_duration.default_duration', '');
        }
        
        return $this->duration;
    }

    public function getSlotInterval($duration = null)
    {
        $duration = $duration ?: $this->duration;

        $interval = Arr::get($this->settings, 'slot_interval', '');

        $slotInterval = empty($interval) ? $duration : intval($interval);

        return $slotInterval;
    }

    public function getTotalBufferTime()
    {
        $bufferTimeBefore = Arr::get($this->settings, 'buffer_time_before', 0);
        $bufferTimeAfter = Arr::get($this->settings, 'buffer_time_after', 0);

        return $bufferTimeBefore + $bufferTimeAfter;
    }

    public function getMaxBookableDateTime($startDate)
    {
        $rangeType = Arr::get($this->settings, 'range_type', 'range_days');

        if ($rangeType == 'range_indefinite') {
            return gmdate('Y-m-t 23:59:59', strtotime($startDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        }

        $maxDate = gmdate('Y-m-t 23:59:59', strtotime($startDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        if ($rangeType == 'range_date_between') {
            $range = Arr::get($this->settings, 'range_date_between', []);
            if (is_array($range) && count(array_filter($range)) == 2) {
                if (strtotime($maxDate) > strtotime($range[1])) {
                    $maxDate = gmdate('Y-m-d 23:59:59', strtotime($range[1])); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                }
            }
        } else {
            $rangeDays = Arr::get($this->settings, 'range_days', 60);
            if (!$rangeDays) {
                $rangeDays = 60;
            }
            $maxDate = gmdate('Y-m-d 23:59:59', time() + $rangeDays * DAY_IN_SECONDS); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        }

        if (strtotime($maxDate) > strtotime(gmdate('Y-m-t 23:59:59', strtotime($startDate)))) { // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            return gmdate('Y-m-t 23:59:59', strtotime($startDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        }

        return $maxDate;
    }

    public function getMinBookableDateTime($startDate)
    {
        $rangeType = Arr::get($this->settings, 'range_type', 'range_days');

        if ($rangeType == 'range_date_between') {
            $range = Arr::get($this->settings, 'range_date_between', []);
            if (is_array($range) && count(array_filter($range)) == 2) {
                if (strtotime($range[0]) >= strtotime($startDate)) {
                    $startDate = gmdate('Y-m-d H:i:s', strtotime($range[0])); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                }
            }
        }

        $cutOutSeconds = $this->getCutoutSeconds();
        $currentAuthorTimezoneDateTime = DateTimeHelper::convertToTimeZone(gmdate('Y-m-d H:i:s'), 'UTC', $this->calendar->author_timezone); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        $totalCutStamp = strtotime($currentAuthorTimezoneDateTime) + $cutOutSeconds;

        if (strtotime($startDate) < $totalCutStamp) {
            $startDate = gmdate('Y-m-d H:i:s', $totalCutStamp); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        }

        return $startDate;
    }

    public function getMaxLookUpDate()
    {
        $rangeType = Arr::get($this->settings, 'range_type', 'range_days');

        if ($rangeType == 'range_indefinite') {
            return false;
        }

        if ($rangeType == 'range_date_between') {
            $range = Arr::get($this->settings, 'range_date_between', []);
            if (is_array($range) && count(array_filter($range)) == 2) {
                return gmdate('Y-m-d 23:59:59', strtotime($range[1])); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            }
        }

        $rangeDays = Arr::get($this->settings, 'range_days', 60);
        if (!$rangeDays) {
            $rangeDays = 60;
        }

        return gmdate('Y-m-d 23:59:59', time() + $rangeDays * DAY_IN_SECONDS); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
    }

    public function getMinLookUpDate()
    {
        $rangeType = Arr::get($this->settings, 'range_type', 'range_days');

        if ($rangeType == 'range_date_between') {
            $range = Arr::get($this->settings, 'range_date_between', []);
            if (is_array($range) && count(array_filter($range)) == 2) {
                return gmdate('Y-m-d H:i:s', strtotime($range[0])); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            }
        }

        return gmdate('Y-m-d H:i:s'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
    }

    public function getCutoutSeconds()
    {
        $conditions = Arr::get($this->settings, 'schedule_conditions', []);

        if (!$conditions || empty($conditions['unit'])) {
            return 0;
        }

        return strtotime('+' . $conditions['value'] . ' ' . $conditions['unit'], 0) - strtotime('+0 seconds', 0);
    }

    public function getHostIds($hostId = null)
    {
        if ($hostId) {
            return [$hostId];
        }

        if ($this->isTeamEvent()) {
            return Arr::get($this->settings, 'team_members', []);
        }

        return [$this->user_id];
    }

    public function getMaxBookingPerSlot()
    {
        return $this->max_book_per_slot;
    }

    public function getPublicUrl()
    {
        $calendar = $this->calendar;
        if (!$calendar) {
            return false;
        }

        $baseUr = $calendar->getLandingPageUrl();

        if (!$baseUr) {
            return '';
        }

        if (defined('FLUENT_BOOKING_LANDING_SLUG')) {
            return $baseUr . '/' . $this->slug;
        }

        return $baseUr . '&event=' . $this->slug;
    }

    public function getMeta($key, $default = null)
    {
        $meta = Meta::where('object_type', 'calendar_event')
            ->where('object_id', $this->id)
            ->where('key', $key)
            ->first();

        if (!$meta) {
            return $default;
        }

        return $meta->value;
    }

    public function updateMeta($key, $value)
    {
        $exist = Meta::where('object_type', 'calendar_event')
            ->where('object_id', $this->id)
            ->where('key', $key)
            ->first();

        if ($exist) {
            $exist->value = $value;
            $exist->save();
        } else {
            $exist = Meta::create([
                'object_type' => 'calendar_event',
                'object_id'   => $this->id,
                'key'         => $key,
                'value'       => $value
            ]);
        }

        return $exist;
    }

    public function defaultPaymentIcon($currency, $amount)
    {
        $html = '<div class="fcal_slot_payment_item"><svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-ea893728=""><path fill="currentColor" d="M256 640v192h640V384H768v-64h150.976c14.272 0 19.456 1.472 24.64 4.288a29.056 29.056 0 0 1 12.16 12.096c2.752 5.184 4.224 10.368 4.224 24.64v493.952c0 14.272-1.472 19.456-4.288 24.64a29.056 29.056 0 0 1-12.096 12.16c-5.184 2.752-10.368 4.224-24.64 4.224H233.024c-14.272 0-19.456-1.472-24.64-4.288a29.056 29.056 0 0 1-12.16-12.096c-2.688-5.184-4.224-10.368-4.224-24.576V640h64z"></path><path fill="currentColor" d="M768 192H128v448h640V192zm64-22.976v493.952c0 14.272-1.472 19.456-4.288 24.64a29.056 29.056 0 0 1-12.096 12.16c-5.184 2.752-10.368 4.224-24.64 4.224H105.024c-14.272 0-19.456-1.472-24.64-4.288a29.056 29.056 0 0 1-12.16-12.096C65.536 682.432 64 677.248 64 663.04V169.024c0-14.272 1.472-19.456 4.288-24.64a29.056 29.056 0 0 1 12.096-12.16C85.568 129.536 90.752 128 104.96 128h685.952c14.272 0 19.456 1.472 24.64 4.288a29.056 29.056 0 0 1 12.16 12.096c2.752 5.184 4.224 10.368 4.224 24.64z"></path><path fill="currentColor" d="M448 576a160 160 0 1 1 0-320 160 160 0 0 1 0 320zm0-64a96 96 0 1 0 0-192 96 96 0 0 0 0 192z"></path></svg>' . $currency . $amount . '</div>';
        return $html;
    }

    public function defaultLocationHtml()
    {
        if (empty($this->location_settings)) {
            return '';
        }

        $default = Arr::get($this, 'location_settings');
        if (!$default) {
            return '';
        }

        return LocationService::getLocationIconHeadingHtml($default, $this);
    }

    public function getPricingTotal()
    {
        if ($this->type != 'paid') {
            return 0;
        }

        if (!Helper::isPaymentEnabled()) {
            return 0;
        }

        $paymentSettings = $this->getMeta('payment_settings', []);

        if (!$paymentSettings || Arr::get($paymentSettings, 'enabled') != 'yes') {
            return 0;
        }

        $items = Arr::get($paymentSettings, 'items', []);
        $total = 0;

        foreach ($items as $item) {
            $total += (int)$item['value'];
        }

        return $total;
    }

    public function getRedirectUrlWithQuery($booking)
    {
        $isEnabled     = Arr::isTrue($this->settings, 'custom_redirect.enabled');
        $redirectUrl   = Arr::get($this->settings, 'custom_redirect.redirect_url', '');
        $queryString   = Arr::get($this->settings, 'custom_redirect.query_string', '');
        $isQueryString = Arr::get($this->settings, 'custom_redirect.is_query_string', 'no') == 'yes';

        if ($isQueryString && $queryString) {
            if (strpos($redirectUrl, '?')) {
                $redirectUrl .= '&' . $queryString;
            } else {
                $redirectUrl .= '?' . $queryString;
            }
        }

        if (!$isEnabled || empty($redirectUrl)) {
            return '';
        }
            
        $redirectUrl = EditorShortCodeParser::parse($redirectUrl, $booking);

        $isUrlParser = apply_filters('fluent_booking/will_parse_redirect_url_value', true, $this);

        if ($isUrlParser) {
            if (strpos($redirectUrl, '=&') || '=' == substr($redirectUrl, -1)) {
                $urlArray    = explode('?', $redirectUrl);
                $baseUrl     = array_shift($urlArray);
                $query       = wp_parse_url($redirectUrl)['query'];
                $queryParams = explode('&', $query);

                $params = [];
                foreach ($queryParams as $queryParam) {
                    $paramArray = explode('=', $queryParam);
                    if (!empty($paramArray[1])) {
                        $params[$paramArray[0]] = $paramArray[1];
                    }
                }
                $redirectUrl = add_query_arg($params, $baseUrl);
            }
        }

        return $redirectUrl;
    }

    public function isCommonSchedule()
    {
        if ($this->isTeamEvent()) {
            if (!Arr::isTrue($this->settings, 'common_schedule', false)) {
                return true;
            }
        }
        return false;
    }

    private function getProcessedWeeklySlots($schedule)
    {
        $scheduleData = Arr::get($schedule, 'value.weekly_schedules', []);
        $scheduleTimezone = Arr::get($schedule, 'value.timezone', 'UTC');
        $schedule = SanitizeService::weeklySchedules($scheduleData, 'UTC', $scheduleTimezone);
        return AvailabilityService::getUtcWeeklySchedules($schedule, $scheduleTimezone);
    }

    private function getProcessedDateOverrides($schedule)
    {
        $scheduleData = Arr::get($schedule, 'value.date_overrides', []);
        $scheduleTimezone = Arr::get($schedule, 'value.timezone', 'UTC');
        $schedule = SanitizeService::slotDateOverrides($scheduleData, 'UTC', $scheduleTimezone);
        return AvailabilityService::getUtcDateOverrides($schedule, $scheduleTimezone);
    }

    protected function getTeamScheduleData($dataKey = 'weekly_schedules')
    {
        $teamSchedules = [];
        $teamMemberIds = $this->getHostIds();

        foreach ($teamMemberIds as $teamMemberId) {
            $schedule = AvailabilityService::getDefaultSchedule($teamMemberId);
            if ($schedule) {
                if ($dataKey == 'date_overrides') {
                    $teamSchedules[] = $this->getProcessedDateOverrides($schedule);
                    continue;
                }
                $teamSchedules[] = $this->getProcessedWeeklySlots($schedule);
            }
        }
        return $teamSchedules;
    }

    protected function mergeTeamOverrides()
    {
        $teamOverrides = $this->getTeamScheduleData('date_overrides');

        $teamOverride = [];
        foreach ($teamOverrides as $dateOverrides) {
            foreach ($dateOverrides as $date => $slots) {
                if (!isset($teamOverride[$date])) {
                    $teamOverride[$date] = $slots;
                } else {
                    $combinedSlots = array_merge($teamOverride[$date], $slots);
                    $uniqueCombinedSlots = array_unique($combinedSlots, SORT_REGULAR);
                    $teamOverride[$date] = array_values($uniqueCombinedSlots);
                }
            }
        }

        return $teamOverride;
    }

    protected function mergeTeamSchedules()
    {
        $teamSchedules = $this->getTeamScheduleData('weekly_schedules');

        $teamSchedule = [];
        foreach ($teamSchedules as $schedule) {
            foreach ($schedule as $day => $dayData) {
                if (!isset($teamSchedule[$day])) {
                    $teamSchedule[$day] = $dayData;
                } else {
                    $combinedSlots = array_merge($teamSchedule[$day]['slots'], $dayData['slots']);
                    $uniqueCombinedSlots = array_unique($combinedSlots, SORT_REGULAR);
                    $teamSchedule[$day]['slots'] = array_values($uniqueCombinedSlots);
                    $teamSchedule[$day]['enabled'] = $teamSchedule[$day]['enabled'] || $dayData['enabled'];
                }
            }
        }

        return $teamSchedule;
    }

    public function getWeeklySlots($hostId = null)
    {
        if ($hostId) {
            $schedule = AvailabilityService::getDefaultSchedule($hostId);
            return $this->getProcessedWeeklySlots($schedule);
        }

        if ($this->isCommonSchedule()) {
            return $this->mergeTeamSchedules();
        }

        if ($this->availability_type === 'existing_schedule') {
            $schedule = Availability::findOrFail($this->availability_id);
            return $this->getProcessedWeeklySlots($schedule);
        }

        $schedule = SanitizeService::weeklySchedules(Arr::get($this->settings,'weekly_schedules',[]), 'UTC', $this->calendar->author_timezone);
        return AvailabilityService::getUtcWeeklySchedules($schedule, $scheduleTimezone);
    }

    public function getDateOverrides($hostId = null)
    {
        if ($hostId) {
            $schedule = AvailabilityService::getDefaultSchedule($hostId);
            return $this->getProcessedDateOverrides($schedule);
        }

        if ($this->isCommonSchedule()) {
            return $this->mergeTeamOverrides();
        }

        if ($this->availability_type === 'existing_schedule') {
            $schedule = Availability::findOrFail($this->availability_id);
            return $this->getProcessedDateOverrides($schedule);
        }

        $scheduleData = Arr::get($this->settings,'date_overrides',[]);
        $schedule = SanitizeService::slotDateOverrides($scheduleData, 'UTC', $this->calendar->author_timezone);
        return AvailabilityService::getUtcDateOverrides($schedule, $scheduleTimezone);
    }

    public function getHostIdsSortedByBookings($startDate)
    {
        $hostIds = $this->getHostIds();

        $hostBookings = [];
        foreach ($hostIds as $hostId) {
            $hostBookings[$hostId] = Booking::getHostTotalBooking(
                $this->id,
                [$hostId],
                [gmdate('Y-m-d 00:00:00',strtotime($startDate)), gmdate('Y-m-d 23:59:59',strtotime($startDate))]
            );
        }
        usort($hostIds, function ($a, $b) use ($hostBookings) {
            return $hostBookings[$a] - $hostBookings[$b];
        });

        return $hostIds;
    }

    public function getPaymentSettings()
    {
        $settings = $this->getMeta('payment_settings', []);

        $defaults = [
            'enabled'        => 'no',
            'driver'         => 'native',
            'items'          => [
                [
                    'title' => __('Booking Fee', 'fluent-booking-pro'),
                    'value' => 100,
                ]
            ],
            'woo_product_id' => ''
        ];

        if (!$settings) {
            $settings = $defaults;
        }

        return wp_parse_args($settings, $defaults);
    }

    public function getCalendarEventsMeta()
    {
        $eventsMeta = Meta::where('object_id', $this->id)
            ->where('object_type', 'calendar_event')
            ->get();
        
        return $eventsMeta;
    }

    public function getIntegrationsMeta()
    {
        $integrationsMeta = Meta::where('object_id', $this->id)
            ->where('object_type', 'integration')
            ->get();
        
        return $integrationsMeta;
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!in_array($key, $this->fillable)) {
                continue;
            }

            if (!array_key_exists($key, $this->original)) {
                $dirty[$key] = $value;
            } elseif ($value !== $this->original[$key] &&
                !$this->originalIsNumericallyEquivalent($key)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }
}
