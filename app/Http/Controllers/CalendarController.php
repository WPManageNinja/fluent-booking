<?php

namespace FluentCalendar\App\Http\Controllers;

use FluentCalendar\App\Models\Booking;
use FluentCalendar\App\Models\Calendar;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Services\Helper;
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
            foreach ($calendar->slots as $slot) {
                $slot->public_url = site_url($calendar->slug . '/' . $slot->slug);
            }
        }

        return [
            'calendars' => $calendars
        ];
    }

    public function checkSlug(Request $request)
    {
        $slug = sanitize_text_field(trim($request->get('slug')));

        if (!Helper::isCalendarSlugAvailable($slug, true)) {
            return $this->sendError([
                'message' => 'The provided slug is not available. Please choose a different one'
            ], 423);
        }

        return [
            'status' => true
        ];
    }

    public function create(Request $request)
    {
        $data = $request->get('calendar');

        $this->validate($data, apply_filters('fluent_calendar/create_calender_validation_rule', [
            'author_timezone'       => 'required',
            'slot.duration'         => 'required|int',
            'slot.schedule_type'    => 'required',
            'slot.title'            => 'required',
            'slot.weekly_schedules' => 'required_if:slot.schedule_type,weekly_schedules',
        ], $data));

        $user = get_user_by('ID', get_current_user_id());

        if (!empty($data['slug'])) {
            $slug = trim(sanitize_text_field($data['slug']));
            if (!Helper::isCalendarSlugAvailable($slug, true)) {
                return $this->sendError([
                    'message' => 'The provided slug is not available. Please choose a different one'
                ], 423);
            }

            $calendarData = [
                'user_id' => $user->ID,
                'title'   => sprintf('Booking schedule with %s', trim($user->first_name . ' ' . $user->last_name)),
                'slug'    => $slug
            ];

            $calendar = Calendar::create($calendarData);
        } else {
            $calendar = Calendar::where('user_id', $user->ID)->first();
        }

        if (!$calendar) {
            return $this->sendError([
                'message' => 'Calendar could not be found. Please try again'
            ], 423);
        }

        if (!empty($data['author_timezone'])) {
            $calendar->author_timezone = sanitize_text_field($data['author_timezone']);
            $calendar->save();
        } else {
            $data['author_timezone'] = 'UTC';
        }


        $slot = $data['slot'];
        $title = (!empty($slot['title'])) ? sanitize_text_field($slot['title']) : $slot['duration'] . ' Minute Meeting';

        $slotData = [
            'title'            => $title,
            'slug'             => sanitize_title($slot['duration'] . ' Minutes Meeting', $slot['duration'] . '-minutes-meeting', 'display'),
            'calendar_id'      => $calendar->id,
            'duration'         => (int)$slot['duration'],
            'settings'         => [
                'schedule_type'    => sanitize_text_field($slot['schedule_type']),
                'weekly_schedules' => SanitizeService::weeklySchedules($slot['weekly_schedules'], $calendar->author_timezone, 'UTC')
            ],
            'status'           => 'active',
            'location_type'    => sanitize_text_field(Arr::get($slot, 'location_type')),
            'location_heading' => sanitize_text_field(Arr::get($slot, 'location_heading')),
            'location_settings' => wp_kses_post_deep(Arr::get($slot, 'location_settings', [])),
        ];

        $slotData['settings'] = wp_parse_args($slotData['settings'], (new CalendarSlot())->getSlotSettingsSchema());

        $slot = CalendarSlot::create($slotData);

        return [
            'calendar'     => $calendar,
            'slot'         => $slot,
            'redirect_url' => Helper::getAppBaseUrl('calendars')
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

        $slotSettings['date_overrides'] = (object) SanitizeService::slotDateOverrides(Arr::get($slotSettings, 'date_overrides', []), 'UTC', $slot->calendar->author_timezone, $slot);

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
                'date_overrides' => SanitizeService::slotDateOverrides(Arr::get($slot['settings'], 'date_overrides', []), $calendar->author_timezone, 'UTC'),
                'range_type' => sanitize_text_field(Arr::get($slot['settings'], 'range_type')),
                'range_days' => (int) (Arr::get($slot['settings'], 'range_days', 60)) ?: 60,
                'range_date_between' => SanitizeService::rangeDateBetween(Arr::get($slot['settings'], 'range_date_between', ['', ''])),
                'schedule_conditions' => SanitizeService::scheduleConditions(Arr::get($slot['settings'], 'schedule_conditions', [])),
            ],
            'status'        => 'active',
            'location_type' => sanitize_text_field(Arr::get($slot, 'location_type')),
            'location_heading' => wp_kses_post(Arr::get($slot, 'location_heading')),
            'location_settings' => wp_kses_post_deep(Arr::get($slot, 'location_settings', []))
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
            'title'         => 'required',
            'duration'      => 'required|int',
            'location_type' => 'required'
        ]);

        $slot->settings = [
            'schedule_type'    => sanitize_text_field($data['settings']['schedule_type']),
            'weekly_schedules' => SanitizeService::weeklySchedules($data['settings']['weekly_schedules'], $slot->calendar->author_timezone, 'UTC'),
            'date_overrides' => SanitizeService::slotDateOverrides(Arr::get($data['settings'], 'date_overrides', []), $slot->calendar->author_timezone, 'UTC'),
            'range_type' => sanitize_text_field(Arr::get($data['settings'], 'range_type')),
            'range_days' => (int) (Arr::get($data['settings'], 'range_days', 60)) ?: 60,
            'range_date_between' => SanitizeService::rangeDateBetween(Arr::get($data['settings'], 'range_date_between', ['', ''])),
            'schedule_conditions' => SanitizeService::scheduleConditions(Arr::get($data['settings'], 'schedule_conditions', [])),
        ];

        $slot->title = sanitize_text_field($data['title']);
        $slot->duration = (int) $data['duration'];
        $slot->description = sanitize_textarea_field(Arr::get($data, 'description'));
        $slot->location_type = sanitize_text_field(Arr::get($data, 'location_type'));
        $slot->location_heading = wp_kses_post(Arr::get($data, 'location_heading'));
        $slot->location_settings = wp_kses_post_deep(Arr::get($data, 'location_settings', []));
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

    public function getSlotNotifications(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        /*
         * Confirmation Email to Attendee
         * Confirmation Email to Organizer
         * Reminder Email to Attendee [before 1 day, 1 hour, 30 minutes, 5 minutes]
         * Cancelled By Organizer to Attendee
         * Cancelled By Attendee to Organizer
         */

        return [
            'notifications' => $slot->getNotifications()
        ];
    }

    public function saveSlotNotifications(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        $notifications = $request->get('notifications');

        $formattedNotifications = [];

        foreach ($notifications as $key => $value) {
            $formattedNotifications[$key] = [
                'title' => sanitize_text_field($value['title']),
                'enabled' => Arr::isTrue($value, 'enabled'),
            ];
        }

        $slot->setNotifications($formattedNotifications);

        return [
            'message' => 'Notifications has been saved'
        ];
    }

    public function deleteCalendarSlot(Request $request, $calendarId, $slotId)
    {
        $calendar = Calendar::findOrFail($calendarId);
        $slot = CalendarSlot::where('calendar_id', $calendar->id)->findOrFail($slotId);
        // Let's delete all the events related to this slot
        Booking::where('slot_id', $slot->id)
            ->where('calendar_id', $calendar->id)
            ->delete();

        $slot->delete();

        return [
            'message' => 'Slot has been deleted'
        ];
    }
}
