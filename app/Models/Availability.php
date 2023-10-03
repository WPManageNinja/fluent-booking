<?php

namespace FluentBooking\App\Models;

use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;
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

    public function availablitySchedules($toTimezone)
    {
        $availabilities = Availability::where('object_type', 'availability')->get();

        $formattedSchedules = [];
        foreach ($availabilities as $availability) {
            $formattedSchedules[] = [
                'object_id' => (int)Arr::get($availability, 'object_id'),
                'key'       => sanitize_text_field(Arr::get($availability, 'title')),
                'value'     => [
                    'default'          => Arr::isTrue($availability, 'value.default'),
                    'timezone'         => sanitize_text_field($toTimezone),
                    'date_overrides'   => SanitizeService::slotDateOverrides(Arr::get($availability, 'value.date_overrides', []), 'UTC', $toTimezone),
                    'weekly_schedules' => SanitizeService::weeklySchedules(Arr::get($availability, 'value.weekly_schedules', []), 'UTC', $toTimezone),
                ]
            ];
        }
        return $formattedSchedules;
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
