<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\Models\Availability;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\Framework\Support\Arr;

class AvailabilityService
{
    public static function availablitySchedules($toTimezone)
    {
        $availabilities = Availability::where('object_type', 'availability')->get();

        $formattedSchedules = [];

        foreach ($availabilities as $availability) {
            $formattedSchedules[] = [
                'id'        => (int)Arr::get($availability, 'id'),
                'object_id' => (int)Arr::get($availability, 'object_id'),
                'title'     => sanitize_text_field(Arr::get($availability, 'key')),
                'settings'  => [
                    'default'          => Arr::isTrue($availability, 'value.default'),
                    'timezone'         => sanitize_text_field($toTimezone),
                    'date_overrides'   => SanitizeService::slotDateOverrides(Arr::get($availability, 'value.date_overrides', []), 'UTC', $toTimezone),
                    'weekly_schedules' => SanitizeService::weeklySchedules(Arr::get($availability, 'value.weekly_schedules', []), 'UTC', $toTimezone),
                ]
            ];
        }
        return $formattedSchedules;
    }

    public static function getFormattedSchedule($schedule)
    {
        $timezone = sanitize_text_field(Arr::get($schedule->value, 'timezone', 'UTC'));

        $author = $schedule->getAuthor();

        return [
            'id'          => $schedule->id,
            'host_name'   => $author['name'],
            'host_avatar' => $author['avatar'],
            'title'       => $schedule->key,
            'usage_count' => AvailabilityService::getAvailabilityUsageCount($schedule->id),
            'created_at'  => $schedule->created_at->format('Y-m-d H:i:s'),
            'settings'    => [
                'default'          => Arr::isTrue($schedule, 'value.default'),
                'timezone'         => $timezone,
                'date_overrides'   => SanitizeService::slotDateOverrides(Arr::get($schedule, 'value.date_overrides', []), 'UTC', $timezone),
                'weekly_schedules' => SanitizeService::weeklySchedules(Arr::get($schedule, 'value.weekly_schedules'), 'UTC', $timezone)
            ]
        ];
    }

    public static function isTitleAlreadyExist($title, $userId, $currentTitle = '')
    {
        if ($title == $currentTitle) {
            return false;
        }

        $scheduleTitles = Availability::where('object_type', 'availability')
            ->where('object_id', $userId)
            ->pluck('key')
            ->toArray();

        if (in_array($title, $scheduleTitles)) {
            return true;
        }

        return false;
    }

    public static function updateOtherDefaultStatus($schedule, $id)
    {
        $schedules = Availability::where('object_type', 'availability')
            ->where('object_id', $schedule->object_id)
            ->where('id', '!=', $id)
            ->get();

        foreach ($schedules as $schedule) {
            $schedule = Availability::findOrFail($schedule->id);
            $updatedSettings = [
                'default'          => false,
                'timezone'         => Arr::get($schedule, 'value.timezone', 'UTC'),
                'date_overrides'   => Arr::get($schedule, 'value.data_overrides', []),
                'weekly_schedules' => Arr::get($schedule, 'value.weekly_schedules'),
            ];

            $schedule->value = $updatedSettings;
            $schedule->save();
        }
    }

    public static function getScheduleOptions()
    {
        $calendars = Calendar::with(['user'])->get();

        $scheduleOptions = [];

        foreach ($calendars as $index => $calendar) {
            $availabilities = Availability::where('object_type', 'availability')
                ->where('object_id', $calendar->user_id)
                ->get();

            $options = [];
            foreach ($availabilities as $availability) {
                $options[] = [
                    'label' => Arr::get($availability, 'key'),
                    'value' => Arr::get($availability, 'id')
                ];
            }

            $hostName = $calendar->user->full_name;
            if ($calendar->user_id == get_current_user_id()) {
                $hostName = __('My Schedules', 'fluent-booking');
            }

            if (!empty($options)) {
                $scheduleOptions[$index] = [
                    'hostName'  => $hostName,
                    'schedules' => $options
                ];
            }
        }

        return apply_filters('fluent_booking/availability_schedule_options', $scheduleOptions);
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

    public static function getAvailabilityUsageCount($scheduleId)
    {
        return CalendarSlot::where('availability_type', 'existing_schedule')
            ->where('availability_id', $scheduleId)
            ->count();
    }
}
