<?php

namespace FluentCalendar\App\Http\Controllers;

use FluentCalendar\App\Models\Calendar;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Services\SanitizeService;
use FluentCalendar\Framework\Request\Request;
use FluentCalendar\Framework\Support\Arr;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $calendars = Calendar::with(['slots'])->latest()->paginate();

        foreach ($calendars as $calendar) {
            $calendar->author_profile = $calendar->getAuthorProfile();
        }

        return [
            'calendars' => $calendars
        ];
    }

    public function create(Request $request)
    {
        $data = $request->get('calendar');

        $this->validate($data, apply_filters('fluent_calendar/create_calender_validation_rule', [
            'title'                 => 'required|unique:fcal_calendars',
            'author_timezone'       => 'required',
            'slot.duration'         => 'required|int',
            'slot.schedule_type'    => 'required',
            'slot.weekly_schedules' => 'required_if:slot.schedule_type,weekly_schedules',
        ], $data));

        $calendarData = [
            'title'           => sanitize_text_field($data['title']),
            'slug'            => sanitize_title($data['title'], 'calendar', 'display'),
            'author_timezone' => sanitize_text_field($data['author_timezone']),
        ];

        $calendar = Calendar::create($calendarData);

        $slot = $data['slot'];

        $slotData = [
            'title'         => $slot['duration'] . ' Minute Meeting',
            'slug'          => sanitize_title($slot['duration'] . ' Minute Meeting', $slot['duration'] . '-minute-meeting', 'display'),
            'calendar_id'   => $calendar->id,
            'duration'      => (int)$slot['duration'],
            'settings'      => [
                'schedule_type'    => sanitize_text_field($slot['schedule_type']),
                'weekly_schedules' => SanitizeService::weeklySchedules($slot['weekly_schedules'], $calendar->author_timezone, 'UTC'),
            ],
            'status'        => 'active',
            'location_type' => 'online'
        ];

        $slot = CalendarSlot::create($slotData);

        return [
            'calendar' => $calendar,
            'slot'     => $slot
        ];
    }

    public function getCalendar(Request $request, $id)
    {
        $calendar = Calendar::with(['slots'])->findOrFail($id);

        return [
            'calendar' => $calendar
        ];
    }

    public function getSlot(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->with(['calendar'])->findOrFail($slotId);
        $slot->author_profile = $slot->getAuthorProfile();

        $slotSettings = $slot->settings;

        $slotSettings['weekly_schedules'] = SanitizeService::weeklySchedules($slotSettings['weekly_schedules'], 'UTC', $slot->calendar->author_timezone);

        $slot->settings = $slotSettings;

        return [
            'slot' => $slot
        ];
    }

    public function getSlotSchema(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);

        $schema = [
            'title'       => '',
            'description' => '',
            'duration'    => 30,
            'settings'    => (new CalendarSlot())->getSlotSettingsSchema(),
            'calendar'    => $calendar
        ];


        return [
            'slot' => $schema
        ];
    }

    public function createCalendarSlot(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);

        $slot = $request->all();

        $this->validate($slot, [
            'title'                     => 'required',
            'duration'                  => 'required|int',
            'settings.schedule_type'    => 'required',
            'settings.weekly_schedules' => 'required_if:settings.schedule_type,weekly_schedules',
        ]);

        $slotData = [
            'title'         => $slot['duration'] . ' Minute Meeting',
            'slug'          => sanitize_title($slot['duration'] . ' Minute Meeting', $slot['duration'] . '-minute-meeting', 'display'),
            'calendar_id'   => $calendar->id,
            'duration'      => (int)$slot['duration'],
            'description'   => sanitize_textarea_field(Arr::get($slot, 'description')),
            'settings'      => [
                'schedule_type'    => sanitize_text_field($slot['settings']['schedule_type']),
                'weekly_schedules' => SanitizeService::weeklySchedules($slot['settings']['weekly_schedules'], $calendar->author_timezone, 'UTC'),
            ],
            'status'        => 'active',
            'location_type' => 'online'
        ];

        $createdSlot = CalendarSlot::create($slotData);

        return [
            'message' => 'New Event Type has been created successfully',
            'slot'    => $createdSlot
        ];
    }

    public function updateCalendarSlot(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        $data = $request->all();

        $this->validate($data, [
            'title'    => 'required',
            'duration' => 'required|int'
        ]);

        $slot->settings = [
            'schedule_type'    => sanitize_text_field($data['settings']['schedule_type']),
            'weekly_schedules' => SanitizeService::weeklySchedules($data['settings']['weekly_schedules'], $slot->calendar->author_timezone, 'UTC'),
        ];

        $slot->title = sanitize_text_field($data['title']);
        $slot->description = sanitize_text_field(Arr::get($data, 'description'));
        $slot->save();

        return [
            'message' => 'Data has been updated',
            'slot'    => $slot
        ];

    }

    public function patchCalendarSlot(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        $status = $request->get('status');

        if ($status) {
            $slot->status = $status;
            $slot->save();
        }

        return [
            'message' => 'Data has been updated'
        ];

    }
}
