<?php

namespace FluentCalendar\App\Models;

use FluentCalendar\App\Models\Model;

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

    public function calendar()
    {
        return $this->belongsTo(Calendar::class, 'calendar_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'slot_id');
    }

    public function getAuthorProfile($public = true)
    {
        $author = get_user_by('id', $this->user_id);
        if (!$author) {
            return false;
        }

        $name = trim($author->first_name . ' ' . $author->last_name);

        if (!$name) {
            $name = $author->display_name;
        }

        return [
            'name'   => $name,
            'avatar' => get_avatar_url($author->ID)
        ];
    }

    public function getSlotSettingsSchema()
    {
        return [
            'schedule_type'    => 'weekly_schedules',
            'weekly_schedules' => [
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
            ]
        ];
    }

}
