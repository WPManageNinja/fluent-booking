<?php

namespace FluentBooking\App\Models;

use FluentBooking\App\Models\Model;
use FluentBooking\App\Services\BookingFieldService;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\BookingService;
use FluentBooking\App\Services\LandingPage\LandingPageHandler;
use FluentBooking\App\Services\LandingPage\LandingPageHelper;
use FluentBooking\App\Services\LocationService;
use FluentBooking\Framework\Support\Arr;

class CalendarSlot extends Model
{
    protected $table = 'fcal_calendar_events';

    protected $guarded = ['id'];

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
        $this->attributes['settings'] = \maybe_serialize($settings);
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

    public function getAuthorProfile($public = true)
    {
        $user = get_user_by('id', $this->user_id);
        if (!$user) {
            return false;
        }

        $name = trim($user->first_name . ' ' . $user->last_name);

        if (!$name) {
            $name = $user->display_name;
        }

        $data = [
            'name'   => $name,
            'avatar' => apply_filters('fluent_booking/author_photo', get_avatar_url($user->ID), $user)
        ];

        if (!$public) {
            $data['email'] = $user->user_email;
        }

        $data['ID'] = $user->ID;

        return $data;
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

    public function getBookingFields()
    {
        return BookingFieldService::getBookingFields($this);
    }

    public function setBookingFields($bookingFields)
    {
        return $this->updateMeta('booking_fields', $bookingFields);
    }

    public function getTotalBufferTime()
    {
        $bufferTimeBefore = Arr::get($this->settings, 'buffer_time_before', 0);
        $bufferTimeAfter  = Arr::get($this->settings, 'buffer_time_after', 0);

        return $bufferTimeBefore + $bufferTimeAfter;
    }

    public function getMaxBookableDateTime($startDate)
    {
        $rangeType = Arr::get($this->settings, 'range_type', 'range_days');

        if ($rangeType == 'range_indefinite') {
            return date('Y-m-t 23:59:59', strtotime($startDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        }

        $maxDate = date('Y-m-t 23:59:59', strtotime($startDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        if ($rangeType == 'range_date_between') {
            $range = Arr::get($this->settings, 'range_date_between', []);
            if (is_array($range) && count(array_filter($range)) == 2) {
                if (strtotime($maxDate) > strtotime($range[1])) {
                    $maxDate = date('Y-m-d 23:59:59', strtotime($range[1])); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                }
            }
        } else {
            $rangeDays = Arr::get($this->settings, 'range_days', 60);
            if (!$rangeDays) {
                $rangeDays = 60;
            }
            $maxDate = date('Y-m-d 23:59:59', time() + $rangeDays * DAY_IN_SECONDS); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        }

        if (strtotime($maxDate) > strtotime(date('Y-m-t 23:59:59', strtotime($startDate)))) { // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            return date('Y-m-t 23:59:59', strtotime($startDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
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
                    $startDate = date('Y-m-d H:i:s', strtotime($range[0])); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                }
            }
        }

        $cutOutSeconds = $this->getCutoutSeconds();
        $currentAuthorTimezoneDateTime = DateTimeHelper::convertToTimeZone(date('Y-m-d H:i:s'), 'UTC', $this->calendar->author_timezone); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        $totalCutStamp = strtotime($currentAuthorTimezoneDateTime) + $cutOutSeconds;

        if(strtotime($startDate) < $totalCutStamp) {
            $startDate = date('Y-m-d H:i:s', $totalCutStamp); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
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
                return date('Y-m-d 23:59:59', strtotime($range[1])); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            }
        }

        $rangeDays = Arr::get($this->settings, 'range_days', 60);
        if (!$rangeDays) {
            $rangeDays = 60;
        }

        return date('Y-m-d 23:59:59', time() + $rangeDays * DAY_IN_SECONDS); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
    }

    public function getMinLookUpDate()
    {
        $rangeType = Arr::get($this->settings, 'range_type', 'range_days');

        if ($rangeType == 'range_date_between') {
            $range = Arr::get($this->settings, 'range_date_between', []);
            if (is_array($range) && count(array_filter($range)) == 2) {
                return date('Y-m-d H:i:s', strtotime($range[0])); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            }
        }

        return date('Y-m-d H:i:s'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
    }

    public function getCutoutSeconds()
    {
        $conditions = Arr::get($this->settings, 'schedule_conditions', []);

        if (!$conditions || empty($conditions['unit'])) {
            return 0;
        }

        return strtotime('+' . $conditions['value'] . ' ' . $conditions['unit'], 0) - strtotime('+0 seconds', 0);
    }

    public function getHostIds()
    {
        return [
            $this->user_id
        ];
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
        $html = '<div class="fcal_slot_payment_item">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
<path d="M6.50391 10.7474C6.50391 11.7149 7.24641 12.4949 8.16891 12.4949H10.0514C10.8539 12.4949 11.5064 11.8124 11.5064 10.9724C11.5064 10.0574 11.1089 9.73488 10.5164 9.52488L7.49391 8.47488C6.90141 8.26488 6.50391 7.94238 6.50391 7.02738C6.50391 6.18738 7.15641 5.50488 7.95891 5.50488H9.84141C10.7639 5.50488 11.5064 6.28488 11.5064 7.25238" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9 4.5V13.5" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9 16.5C13.1421 16.5 16.5 13.1421 16.5 9C16.5 4.85786 13.1421 1.5 9 1.5C4.85786 1.5 1.5 4.85786 1.5 9C1.5 13.1421 4.85786 16.5 9 16.5Z" stroke="#445164" stroke-linecap="round" stroke-linejoin="round"/>
</svg>' . $currency . $amount . '</div>';
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
            $total += $item['value'];
        }

        return $total;
    }

}
