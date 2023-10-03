<?php

namespace FluentBooking\App\Models;

use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\SanitizeService;
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

    public static function defaultScheduleSchema($userId, $title, $default, $fromTimezone, $toTimezone = 'UTC')
    {
        $scheduleSchema = Helper::getWeeklyScheduleSchema();

        $defaultSchedule = [
            'object_id' => $userId,
            'key'       => sanitize_text_field($title),
            'value'     => [
                'default'          => (bool)$default,
                'timezone'         => sanitize_text_field($fromTimezone),
                'date_overrides'   => [],
                'weekly_schedules' => SanitizeService::weeklySchedules($scheduleSchema, $fromTimezone, $toTimezone),
            ]
        ];
        return $defaultSchedule;
    }
}
