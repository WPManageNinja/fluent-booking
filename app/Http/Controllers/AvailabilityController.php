<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Models\Availability;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Request\Request;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\App\Services\SanitizeService;
use FluentBooking\App\Services\AvailabilityService;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\Framework\Support\Arr;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->get('filters', []);

        $query = Availability::with(['calendar'])->where('object_type', 'availability');

        $host = Arr::get($filters, 'author');

        if ($host == 'me') {
            $host = get_current_user_id();
        } else if ($host !== 'all') {
            $host = (int)$host;
        }

        if (!PermissionManager::hasAllCalendarAccess()) {
            $host = get_current_user_id();
        }

        if ($host && $host !== 'all') {
            $query->where('object_id', $host);
        }

        do_action_ref_array('fluent_booking/availability_schedules_query', [&$query]);

        $schedules = $query->latest()->paginate();
        
        $currentPage = $schedules->currentPage();
        $totalData   = $schedules->total();

        do_action('fluent_booking/availability_schedules', $schedules);

        $formattedSchedules = [];
        foreach ($schedules as $schedule)
        {
            $author = ['name' => 'Deleted User', 'avatar' => ''];

            if ($schedule->calendar) {
                $author = $schedule->calendar->getAuthorProfile();
            }

            $timezone =  sanitize_text_field(Arr::get($schedule, 'value.timezone', 'UTC'));

            $formattedSchedules[] = [
                'id'          => $schedule->id,
                'host_name'   => $author['name'],
                'host_avatar' => $author['avatar'],
                'title'       => $schedule->key,
                'created_at'  => DateTimeHelper::convertFromUtc($schedule->created_at, $timezone, 'd M Y'),
                'settings'  => [
                    'default'          => Arr::isTrue($schedule, 'value.default'),
                    'timezone'         => $timezone,
                    'date_overrides'   => SanitizeService::slotDateOverrides(Arr::get($schedule, 'value.date_overrides', []), 'UTC', $timezone),
                    'weekly_schedules' => SanitizeService::weeklySchedules(Arr::get($schedule, 'value.weekly_schedules'), 'UTC', $timezone)
                ]
            ];
        }

        return $this->sendSuccess([
            'schedules'    => $formattedSchedules,
            'current_page' => $currentPage,
            'total'        => $totalData,
        ]);
    }

    public function getSchedule(Request $request, $scheduleId)
    {
        $schedule = Availability::with('calendar')->findOrFail($scheduleId);

        $formattedSchedule = AvailabilityService::getFormattedSchedule($schedule);

        return $this->sendSuccess([
            'schedule' => $formattedSchedule,
        ]);
    }

    public function createSchedule(Request $request)
    {
        $userId = get_current_user_id();

        $timezone = Calendar::where('user_id', $userId)->value('author_timezone');

        $data = $request->all();

        $this->validate($data, [
            'title'   => 'required',
        ]);

        $isTitleExist = AvailabilityService::isTitleAlreadyExist($data['title'], $userId);

        if ($isTitleExist) {   
            $message = $data['title'] . ' is already exist';
            return $this->sendError([
                'message' => $message,
            ], 422);
        }

        $scheduleData = AvailabilityService::defaultScheduleSchema($userId, $data['title'], false, $timezone);

        Availability::create($scheduleData);

        do_action('fluent_booking/avaibility_schedule_created', $createSchedule);

        return $this->sendSuccess([
            'message'  => __('Schedule has been created successfully', 'fluent-booking'),
        ]);
    }

    public function updateSchedule(Request $request, $scheduleId)
    {
        $userId = get_current_user_id();

        $schedule = Availability::findOrFail($scheduleId);

        $timezone = Calendar::where('user_id', $userId)->value('author_timezone');

        $data = $request->all();

        $scheduleData = [
            'default'          => Arr::isTrue($data, 'schedule.settings.default'),
            'timezone'         => sanitize_text_field($timezone),
            'date_overrides'   => SanitizeService::slotDateOverrides(Arr::get($data, 'schedule.settings.date_overrides', []), $timezone, 'UTC'),
            'weekly_schedules' => SanitizeService::weeklySchedules(Arr::get($data, 'schedule.settings.weekly_schedules', []), $timezone, 'UTC'),
        ];

        $schedule->value = $scheduleData;
        $schedule->save();

        do_action('fluent_booking/avaibility_schedule_updated', $schedule, $scheduleData);

        return $this->sendSuccess([
            'message'  => __('Schedule has been updated successfully', 'fluent-booking'),
            'schedule' => $schedule,
            'timezone' => $timezone
        ]);
    }

    public function updateScheduleTitle(Request $request, $scheduleId)
    {
        $title = $request->get('title');

        $schedule = Availability::findOrFail($scheduleId);

        $isTitleExist = AvailabilityService::isTitleAlreadyExist($title, $userId, $schedule->key);

        if ($isTitleExist) {   
            $message = $title . ' is already exist';
            return $this->sendError([
                'message' => $message,
            ], 422);
        }

        $schedule->key = $title;
        $schedule->save();

        return $this->sendSuccess([
            'message' => __('Schedule title has been updated successfully', 'fluent-booking'),
            'title'   => $schedule->key
        ]);
    }

    public function updateDefaultStatus(Request $request, $scheduleId)
    {
        $schedule = Availability::findOrFail($scheduleId);

        $updatedSettings = [
            'default'          => true,
            'timezone'         => Arr::get($schedule, 'value.timezone', 'UTC'),
            'date_overrides'   => Arr::get($schedule, 'value.data_overrides', []),
            'weekly_schedules' => Arr::get($schedule, 'value.weekly_schedules'),
        ];

        $schedule->value = $updatedSettings;
        $schedule->save();

        AvailabilityService::updateOtherDefaultStatus($schedule, $scheduleId);

        return $this->sendSuccess([
            'message' => __('Status has been updated successfully', 'fluent-booking')
        ]);
    }
    
    public function deleteSchedule(Request $request, $scheduleId)
    {
        $schedule = Availability::findOrFail($scheduleId);

        $isDefault = Arr::isTrue($schedule, 'value.default');

        if ($isDefault) {
            return $this->sendError([
                'message' => __('Default Schedule can not be deleted', 'fluent-booking')
            ], 422);
        }

        $schedule->delete();

        return $this->sendSuccess([
            'message' => __('Schedule Availability has been deleted successfully', 'fluent-booking')
        ]);
    }
}
