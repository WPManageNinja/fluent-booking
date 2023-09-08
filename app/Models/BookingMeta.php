<?php

namespace FluentCalendar\App\Models;

class BookingMeta extends Model
{
    protected $table = 'fcal_booking_meta';

    protected $guarded = ['id'];

    protected $fillable = [
        'event_id',
        'meta_key',
        'value'
    ];

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = \maybe_serialize($value);
    }

    public function getValueAttribute($value)
    {
        return \maybe_unserialize($value);
    }

}
