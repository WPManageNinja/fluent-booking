<?php

namespace FluentCalendar\App\Models;

use FluentCalendar\App\Models\Model;

class Calendar extends Model
{   
    protected $table = 'fcal_calendars';

    protected $guarded = ['id'];

    public static function boot()
    {
        static::creating(function ($model) {
            $model->user_id = get_current_user_id();
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

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'calendar_id');
    }

    public function getAuthorProfile($public = true)
    {
        $author = get_user_by('id', $this->user_id);
        if(!$author) {
            return [
                'avatar' => '',
                'name' => 'Unknown'
            ];
        }

        $name = trim($author->first_name.' '.$author->last_name);

        if(!$name) {
            $name = $author->display_name;
        }

        return [
            'name' => $name,
            'avatar' => get_avatar_url($author->ID)
        ];
    }
    
}
