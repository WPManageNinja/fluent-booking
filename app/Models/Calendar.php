<?php

namespace FluentCalendar\App\Models;

use FluentCalendar\App\Models\Model;

class Calendar extends Model
{   
    protected $table = 'fcal_calendars';

    protected $guarded = ['id'];

    protected $fillable = [
        'hash',
        'user_id',
        'account_id',
        'title',
        'slug',
        'media_id',
        'description',
        'settings',
        'status',
        'type',
        'event_type',
        'account_type',
        'author_timezone',
        'max_book_per_slot',
        'visibility'
    ];

    public static function boot()
    {
        static::creating(function ($model) {
            if(empty($model->user_id)) {
                $model->user_id = get_current_user_id();
            }
            $model->hash = md5(wp_generate_uuid4().time());
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

    public function slots()
    {
        return $this->hasMany(CalendarSlot::class, 'calendar_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'calendar_id');
    }

    public function getAuthorProfile($public = true)
    {
        $user = get_user_by('id', $this->user_id);
        if(!$user) {
            return [
                'avatar' => '',
                'name' => 'Unknown'
            ];
        }

        $name = trim($user->first_name.' '.$user->last_name);

        if(!$name) {
            $name = $user->display_name;
        }

        return [
            'name' => $name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'avatar' => apply_filters('fluent_calendar/author_photo', get_avatar_url($user->ID), $user)
        ];
    }
    
}
