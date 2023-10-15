<?php

namespace FluentBooking\App\Models;

use FluentBooking\App\Models\Model;
use FluentBooking\App\Services\BookingFieldService;
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

    public function isPhoneRequired()
    {
        return Arr::get($this->location_settings, '0.type') == 'phone_guest';
    }

    public function isAddressRequired()
    {
        return Arr::get($this->location_settings, '0.type') == 'in_person_guest';
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

            if ($isEdit) {
                $defaults = Helper::getDefaultEmailNotificationSettings();

                foreach ($defaults as $key => $default) {
                    if (isset($statuses[$key])) {
                        $statuses[$key]['title'] = $default['title'];
                    }
                }

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

    public function getMaxBookableDateTime($startDate)
    {
        $rangeType = Arr::get($this->settings, 'range_type', 'range_days');

        if ($rangeType == 'range_indefinite') {
            return date('Y-m-t 23:59:59', strtotime($startDate));
        }

        $maxDate = date('Y-m-t 23:59:59', strtotime($startDate));

        if ($rangeType == 'range_date_between') {
            $range = Arr::get($this->settings, 'range_date_between', []);
            if (is_array($range) && count(array_filter($range)) == 2) {
                if (strtotime($maxDate) > strtotime($range[1])) {
                    $maxDate = date('Y-m-d 23:59:59', strtotime($range[1]));
                }
            }
        } else {
            $rangeDays = Arr::get($this->settings, 'range_days', 60);
            if (!$rangeDays) {
                $rangeDays = 60;
            }
            $maxDate = date('Y-m-d 23:59:59', time() + $rangeDays * DAY_IN_SECONDS);
        }

        if (strtotime($maxDate) > strtotime(date('Y-m-t 23:59:59', strtotime($startDate)))) {
            return date('Y-m-t 23:59:59', strtotime($startDate));
        }

        return $maxDate;
    }

    public function getMinBookableDateTime($startDate)
    {
        $rangeType = Arr::get($this->settings, 'range_type', 'range_days');

        if ($rangeType == 'range_indefinite') {
            return $startDate;
        }

        if ($rangeType == 'range_date_between') {
            $range = Arr::get($this->settings, 'range_date_between', []);
            if (is_array($range) && count(array_filter($range)) == 2) {
                if (strtotime($range[0]) >= strtotime($startDate)) {
                    return date('Y-m-d H:i:s', strtotime($range[0]));
                }
            }
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
                return date('Y-m-d 23:59:59', strtotime($range[1]));
            }
        }

        $rangeDays = Arr::get($this->settings, 'range_days', 60);
        if (!$rangeDays) {
            $rangeDays = 60;
        }

        return date('Y-m-d 23:59:59', time() + $rangeDays * DAY_IN_SECONDS);
    }

    public function getMinLookUpDate()
    {
        $rangeType = Arr::get($this->settings, 'range_type', 'range_days');

        if ($rangeType == 'range_date_between') {
            $range = Arr::get($this->settings, 'range_date_between', []);
            if (is_array($range) && count(array_filter($range)) == 2) {
                return date('Y-m-d H:i:s', strtotime($range[0]));
            }
        }

        return date('Y-m-d H:i:s');
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
        $html = '<div class="fcal_slot_payment_item"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="15" viewBox="0 0 532 386" fill="none">
            <rect x="9" y="9" width="514" height="368" rx="33" stroke="black" stroke-width="18"/>
            <rect width="532" height="13" transform="matrix(1 0 0 -1 0 102)" fill="black"/>
            <rect width="532" height="12" transform="matrix(1 0 0 -1 0 153)" fill="black"/>
            <rect x="68" y="231" width="141" height="18" rx="9" fill="black"/>
            <rect x="68" y="282" width="71" height="18" rx="9" fill="black"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M364.949 297.289C359.227 301.507 352.155 304 344.5 304C325.446 304 310 288.554 310 269.5C310 250.446 325.446 235 344.5 235C352.155 235 359.227 237.493 364.949 241.711C368.167 236.563 372.252 232.014 377 228.266C368.061 221.211 356.772 217 344.5 217C315.505 217 292 240.505 292 269.5C292 298.495 315.505 322 344.5 322C356.772 322 368.061 317.789 377 310.734C372.252 306.986 368.167 302.437 364.949 297.289Z" fill="black"/>
            <circle cx="409.5" cy="269.5" r="43.5" stroke="black" stroke-width="18"/>
            </svg> ' . $currency . $amount . '</div>';
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
            return 'ssss';
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
