<?php

namespace FluentBooking\App\Models;

class Availability extends Model
{
    protected $table = 'fcal_meta';

    protected $guarded = ['id'];

    protected $fillable = [
        'object_type',
        'object_id',
        'key',
        'value'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->object_type = 'availability';
        });

        static::updating(function ($model) {
            $model->object_type = 'availability';
        });
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = \maybe_serialize($value);
    }

    public function getValueAttribute($value)
    {
        return \maybe_unserialize($value);
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class, 'object_id');
    }
}
