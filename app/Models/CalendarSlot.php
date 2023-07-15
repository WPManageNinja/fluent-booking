<?php

namespace FluentCalendar\App\Models;

use FluentCalendar\App\Models\Model;
use FluentCalendar\App\Services\Helper;
use FluentCalendar\Framework\Support\Arr;

class CalendarSlot extends Model
{
    protected $table = 'fcal_calendar_slots';

    protected $guarded = ['id'];

    public static function boot()
    {
        static::creating(function ($model) {
            $model->user_id = get_current_user_id();
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
        return $this->hasMany(Booking::class, 'slot_id');
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
            'avatar' => apply_filters('fluent_calendar/author_photo', get_avatar_url($user->ID), $user)
        ];

        if (!$public) {
            $data['email'] = $user->user_email;
        }

        $data['ID'] = $user->ID;

        return $data;
    }

    public function getSlotSettingsSchema()
    {
        return [
            'schedule_type'       => 'weekly_schedules',
            'weekly_schedules'    => [
                'sun' => [
                    'enabled' => false,
                    'slots'   => []
                ],
                'mon' => [
                    'enabled' => true,
                    'slots'   => [
                        ['start' => '09:00', 'end' => '17:00']
                    ],
                ],
                'tue' => [
                    'enabled' => true,
                    'slots'   => [
                        ['start' => '09:00', 'end' => '17:00']
                    ],
                ],
                'wed' => [
                    'enabled' => true,
                    'slots'   => [
                        ['start' => '09:00', 'end' => '17:00']
                    ],
                ],
                'thu' => [
                    'enabled' => true,
                    'slots'   => [
                        ['start' => '09:00', 'end' => '17:00']
                    ],
                ],
                'fri' => [
                    'enabled' => true,
                    'slots'   => [
                        ['start' => '09:00', 'end' => '17:00']
                    ],
                ],
                'sat' => [
                    'enabled' => false,
                    'slots'   => []
                ],
            ],
            'date_overrides'      => [],
            'range_type'          => 'range_days',
            'range_days'          => 60,
            'range_date_between'  => ['', ''],
            'schedule_conditions' => [
                'value' => 4,
                'unit'  => 'hours'
            ]
        ];
    }

    public function getNotifications($isView = false)
    {
        $statuses = Helper::getMeta('calendar_slot', $this->id, 'notification_statuses');

        $defaults = [
            'booking_conf_attendee'    => [
                'enabled' => true,
                'title'   => 'Booking Confirmation to Attendee'
            ],
            'booking_conf_host'        => [
                'enabled' => true,
                'title'   => 'Booking Confirmation to Organizer (You)'
            ],
            'reminder_1_hour_attendee' => [
                'enabled' => true,
                'title'   => 'Reminder 1 Hour Before to Attendee'
            ],
            'reminder_15_min_attendee' => [
                'enabled' => false,
                'title'   => 'Reminder 15 Minutes Before to Attendee'
            ],
            'reminder_1_hour_host'     => [
                'enabled' => true,
                'title'   => 'Reminder 1 Hour Before to Organizer (You)'
            ],
            'reminder_15_min_host'     => [
                'enabled' => false,
                'title'   => 'Reminder 15 Minutes Before to Organizer (You)'
            ],
            'cancelled_by_attendee'    => [
                'enabled' => true,
                'title'   => 'Booking Cancelled by Attendee (email to Organizer)'
            ],
            'cancelled_by_host'        => [
                'enabled' => true,
                'title'   => 'Booking Cancelled by Organizer (email to Attendee)'
            ]
        ];

        if (!$statuses) {
            return $defaults;
        }

        return wp_parse_args($statuses, $defaults);
    }

    public function setNotifications($notifications)
    {
        $statuses = Helper::updateMeta('calendar_slot', $this->id, 'notification_statuses', $notifications);
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
        return 1;
    }
}
