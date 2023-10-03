<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Models\Availability;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Request\Request;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\App\Services\SanitizeService;
use FluentBooking\Framework\Support\Arr;

class AvailabilityController extends Controller
{
    public function index()
    {
        $query = Availability::where('object_type', 'availability');

        if (!PermissionManager::hasAllCalendarAccess()) {
            $hostId = get_current_user_id();
            $query->where('object_id', $hostId);
        }

        $schedules = $query->get();

        do_action('fluent_booking/availability_schedules', $schedules);

        $formattedSchedules = [];

        foreach ($schedules as $schedule)
        {
            $timezone =  sanitize_text_field(Arr::get($schedule, 'value.timezone'));

            $formattedSchedules[] = [
                'id'    => $schedule->id,
                'title' => $schedule->key,
                'settings' => [
                    'default'          => Arr::isTrue($schedule, 'value.default'),
                    'timezone'         => $timezone,
                    'date_overrides'   => SanitizeService::slotDateOverrides(Arr::get($schedule, 'value.date_overrides', []), $timezone, 'UTC'),
                    'weekly_schedules' => SanitizeService::weeklySchedules(Arr::get($schedule, 'value.weekly_schedules'), $timezone, 'UTC')
                ]
            ];
        }

        return [
            'schedules' => $formattedSchedules
        ];
    }

    public function createSchedule(Request $request)
    {
        $userId = get_current_user_id();

        $timezone = Calendar::where('user_id', $userId)->value('author_timezone');

        $data = $request->all();

        $this->validate($data, [
            'title'   => 'required',
        ]);

        $scheduleData = [
            'object_id' => $userId,
            'key'       => sanitize_text_field($data['title']),
            'value'     => [
                'default'          => false,
                'timezone'         => $timezone,
                'date_overrides'   => [],
                'weekly_schedules' => SanitizeService::weeklySchedules($data['weekly_schedules'], $timezone, 'UTC'),
            ]
        ];

        $createSchedule = Availability::create($scheduleData);

        do_action('fluent_booking/avaibility_schedule_created', $createSchedule);

        return [
            'message'  => __('Schedule has been created successfully', 'fluent-booking'),
            'schedule' => $createSchedule,
        ];
    }

    public function updateSchedule(Request $request, $id)
    {
        $userId = get_current_user_id();

        $schedule = Availability::findOrFail($id);

        $timezone = Calendar::where('user_id', $userId)->value('author_timezone');

        $data = $request->all();

        $this->validate($data, [
            'title'   => 'required',
        ]);

        $scheduleData = [
            'default'          => Arr::isTrue($data, 'settings.default'),
            'timezone'         => sanitize_text_field($timezone),
            'date_overrides'   => SanitizeService::slotDateOverrides(Arr::get($data['settings'], 'date_overrides', []), $timezone, 'UTC'),
            'weekly_schedules' => SanitizeService::weeklySchedules($data['settings']['weekly_schedules'], $timezone, 'UTC'),
        ];

        $schedule->key   = sanitize_text_field($data['title']);
        $schedule->value = $scheduleData;
        $schedule->save();

        do_action('fluent_booking/avaibility_schedule_updated', $schedule, $scheduleData);

        return [
            'message'  => __('Schedule has been updated successfully', 'fluent-booking'),
            'schedule' => $schedule,
            'timezone' => $timezone
        ];
    }

    public function deleteSchedule(Request $request, $id)
    {
        error_log($id);
    }
}
