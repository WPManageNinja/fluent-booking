<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\Models\Availability;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Services\SanitizeService;

class AvailabilityService
{
    public static function availablitySchedules($toTimezone)
    {
        $availabilities = Availability::where('object_type', 'availability')->get();

        $formattedSchedules = [];
        
        foreach ($availabilities as $availability) {
            $formattedSchedules[] = [
                'id'      => (int)Arr::get($availability, 'id'),
                'object_id' => (int)Arr::get($availability, 'object_id'),
                'key'       => sanitize_text_field(Arr::get($availability, 'key')),
                'value' => [
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