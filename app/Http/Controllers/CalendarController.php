<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\App\Services\SanitizeService;
use FluentBooking\Framework\Request\Request;
use FluentBooking\Framework\Support\Arr;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        if (PermissionManager::hasAllCalendarAccess()) {
            $calendars = Calendar::with(['slots'])->latest()->paginate();
        } else {
            $calendars = Calendar::with(['slots'])->where('user_id', get_current_user_id())->latest()->paginate();
        }

        foreach ($calendars as $calendar) {
            $calendar->author_profile = $calendar->getAuthorProfile();
            foreach ($calendar->slots as $slot) {
                $slot->shortcode = '[fluent_booking id="' . $slot->id . '"]';

                do_action_ref_array('fluent_booking/calendar_slot', [&$slot]);
            }

            do_action_ref_array('fluent_booking/calendar', [&$calendar]);
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
                'message' => __('The provided slug is not available. Please choose a different one', 'fluent-booking')
            ], 423);
        }

        return [
            'status' => true
        ];
    }

    public function create(Request $request)
    {
        $data = $request->get('calendar');

        $this->validate($data, apply_filters('fluent_booking/create_calender_validation_rule', [
            'author_timezone'       => 'required',
            'slot.duration'         => 'required|int',
            'slot.event_type'       => 'required',
            'slot.schedule_type'    => 'required',
            'slot.title'            => 'required',
            'slot.weekly_schedules' => 'required_if:slot.schedule_type,weekly_schedules',
            'user_id'               => 'required|int'
        ], $data));

        do_action('fluent_booking/before_create_calendar', $data, $this);

        if (!empty($data['user_id'])) {
            $user = get_user_by('ID', $data['user_id']);

            $userName = $user->user_login;
            if (is_email($userName)) {
                $userName = explode('@', $userName);
                $userName = $userName[0] . '-' . time();
            }
            $data['slug'] = sanitize_title($userName, '', 'display');

            if (!Helper::isCalendarSlugAvailable($data['slug'], true)) {
                $data['slug'] .= '-' . time();
            }

        } else {
            $user = get_user_by('ID', get_current_user_id());
        }

        if (!empty($data['slug'])) {
            $slug = trim(sanitize_text_field($data['slug']));
            if (!Helper::isCalendarSlugAvailable($slug, true)) {
                return $this->sendError([
                    'message' => __('The provided slug is not available. Please choose a different one', 'fluent-booking')
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
                'message' => __('Calendar could not be found. Please try again', 'fluent-booking')
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
            'title'             => $title,
            'slug'              => Helper::generateSlotSlug($slot['duration'] . 'min', $calendar),
            'calendar_id'       => $calendar->id,
            'user_id'           => $calendar->user_id,
            'duration'          => (int)$slot['duration'],
            'settings'          => [
                'schedule_type'    => sanitize_text_field($slot['schedule_type']),
                'weekly_schedules' => SanitizeService::weeklySchedules($slot['weekly_schedules'], $calendar->author_timezone, 'UTC')
            ],
            'status'            => 'active',
            'event_type'        => sanitize_text_field(Arr::get($slot, 'event_type')),
            'location_type'     => sanitize_text_field(Arr::get($slot, 'location_type')),
            'location_heading'  => wp_kses_post(Arr::get($slot, 'location_heading')),
            'location_settings' => wp_kses_post_deep(Arr::get($slot, 'location_settings', [])),
        ];

        $slotData['settings'] = wp_parse_args($slotData['settings'], (new CalendarSlot())->getSlotSettingsSchema());

        $slot = CalendarSlot::create($slotData);
        do_action('fluent_booking/after_create_calendar_slot', $slot, $calendar);

        do_action('fluent_booking/after_create_calendar', $calendar);

        return [
            'calendar'     => $calendar,
            'slot'         => $slot,
            'force_reload' => true,
            'redirect_url' => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/slot-settings/' . $slot->id)
        ];
    }

    public function getCalendar(Request $request, $id)
    {
        $calendar = Calendar::with(['slots'])->findOrFail($id);

        return [
            'calendar' => $calendar
        ];
    }

    public function updateCalendar(Request $request, $id)
    {
        $data = $request->all();

        $calendar = Calendar::findOrFail($id);

        do_action_ref_array('fluent_booking/before_update_calendar', [&$calendar, $data]);

        $calendar->description = wp_kses_post($request->get('description'));
        $calendar->save();
        do_action('fluent_booking/after_update_calendar', $calendar, $data);

        $calendar->author_profile = $calendar->getAuthorProfile();

        do_action_ref_array('fluent_booking/calendar', [&$calendar]);

        return [
            'calendar' => $calendar,
            'message'  => __('Calendar has been updated successfully', 'fluent-booking')
        ];
    }

    public function getSlot(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->with(['calendar.user'])->findOrFail($slotId);

        $slot->author_profile = $slot->getAuthorProfile();
        
        $slotSettings = $slot->settings;


        $slotSettings['weekly_schedules'] = SanitizeService::weeklySchedules($slotSettings['weekly_schedules'], 'UTC', $slot->calendar->author_timezone);

        $slotSettings['date_overrides'] = (object)SanitizeService::slotDateOverrides(Arr::get($slotSettings, 'date_overrides', []), 'UTC', $slot->calendar->author_timezone, $slot);
        
        $slot->settings = $slotSettings;
        
        return [
            'slot' => $slot
        ];
    }

    public function getSlotSchema(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);

        $settingsSchema = (new CalendarSlot())->getSlotSettingsSchema();

        $schema = [
            'title'           => '',
            'status'          => 'active',
            'description'     => '',
            'duration'        => '30',
            'color_schema'    => '#0099ff',
            'calendar'        => $calendar,
            'settings'        => $settingsSchema
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
            'status'                    => 'required',
            'settings.schedule_type'    => 'required',
            'settings.weekly_schedules' => 'required_if:settings.schedule_type,weekly_schedules',
            'event_type'                => 'required'
        ]);

        $slotData = [
            'title'             => $slot['title'],
            'slug'              => Helper::generateSlotSlug($slot['duration'] . 'min', $calendar),
            'calendar_id'       => $calendar->id,
            'duration'          => (int)$slot['duration'],
            'description'       => sanitize_textarea_field(Arr::get($slot, 'description')),
            'settings'          => [
                'schedule_type'       => sanitize_text_field($slot['settings']['schedule_type']),
                'weekly_schedules'    => SanitizeService::weeklySchedules($slot['settings']['weekly_schedules'], $calendar->author_timezone, 'UTC'),
                'date_overrides'      => SanitizeService::slotDateOverrides(Arr::get($slot['settings'], 'date_overrides', []), $calendar->author_timezone, 'UTC'),
                'range_type'          => sanitize_text_field(Arr::get($slot['settings'], 'range_type')),
                'range_days'          => (int)(Arr::get($slot['settings'], 'range_days', 60)) ?: 60,
                'range_date_between'  => SanitizeService::rangeDateBetween(Arr::get($slot['settings'], 'range_date_between', ['', ''])),
                'schedule_conditions' => SanitizeService::scheduleConditions(Arr::get($slot['settings'], 'schedule_conditions', [])),
            ],
            'status'            => SanitizeService::checkCollection($slot['status'], ['active', 'draft']),
            'color_schema'      => sanitize_text_field(Arr::get($slot, 'color_schema', '#0099ff')),
            'event_type'        => sanitize_text_field(Arr::get($slot, 'event_type')),
            'location_type'     => sanitize_text_field(Arr::get($slot, 'location_type')),
            'location_heading'  => wp_kses_post(Arr::get($slot, 'location_heading')),
            'location_settings' => wp_kses_post_deep(Arr::get($slot, 'location_settings', []))
        ];

        $createdSlot = CalendarSlot::create($slotData);

        return [
            'message' => __('New Event Type has been created successfully', 'fluent-booking'),
            'slot'    => $createdSlot
        ];
    }

    public function updateCalendarSlot(Request $request, $calendarId, $slotId)
    {
        $data = $request->all();

        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        $generalRules = [
            'title'             => 'required',
            'duration'          => 'required|numeric',
            'location_type'     => 'required'
        ];

        $conditionalRules = [];
        if ('group' === $slot->event_type) {
            $conditionalRules = [
                'max_book_per_slot' => 'required|numeric|min:1',
                'is_display_spots'  => 'required|min:0|max:1',
            ];
        }

        $this->validate($data, array_merge($generalRules, $conditionalRules));

        $slot->settings = [
            'schedule_type'       => sanitize_text_field($data['settings']['schedule_type']),
            'weekly_schedules'    => SanitizeService::weeklySchedules($data['settings']['weekly_schedules'], $slot->calendar->author_timezone, 'UTC'),
            'date_overrides'      => SanitizeService::slotDateOverrides(Arr::get($data['settings'], 'date_overrides', []), $slot->calendar->author_timezone, 'UTC'),
            'range_type'          => sanitize_text_field(Arr::get($data['settings'], 'range_type')),
            'range_days'          => (int)(Arr::get($data['settings'], 'range_days', 60)) ?: 60,
            'range_date_between'  => SanitizeService::rangeDateBetween(Arr::get($data['settings'], 'range_date_between', ['', ''])),
            'schedule_conditions' => SanitizeService::scheduleConditions(Arr::get($data['settings'], 'schedule_conditions', [])),
        ];


        $slot->title = sanitize_text_field($data['title']);
        $slot->duration = (int)$data['duration'];
        $slot->status = SanitizeService::checkCollection($data['status'], ['active', 'draft']);
        $slot->color_schema = sanitize_text_field(Arr::get($data, 'color_schema', '#0099ff'));
        $slot->description = sanitize_textarea_field(Arr::get($data, 'description'));
        $slot->max_book_per_slot = (int)Arr::get($data, 'max_book_per_slot');
        $slot->is_display_spots  = (bool)Arr::get($data, 'is_display_spots');
        $slot->location_type = sanitize_text_field(Arr::get($data, 'location_type'));
        $slot->location_heading = wp_kses_post(Arr::get($data, 'location_heading'));
        $slot->location_settings = wp_kses_post_deep(Arr::get($data, 'location_settings', []));
        $slot->save();

        return [
            'message' => __('Data has been updated', 'fluent-booking'),
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
            'message' => __('Data has been updated', 'fluent-booking')
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
                'title'   => sanitize_text_field($value['title']),
                'enabled' => Arr::isTrue($value, 'enabled'),
                'email'   => $this->sanitize_data($value['email'])
            ];
        }

        $slot->setNotifications($formattedNotifications);

        return [
            'message' => __('Notifications has been saved', 'fluent-booking')
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
            'message' => __('Slot has been deleted', 'fluent-booking')
        ];
    }

    private function sanitize_data( $settings ) {

        $sanitizerMap = [
            'value'                      => 'intval',
            'unit'                       => 'sanitize_text_field',
            'subject'                    => 'sanitize_text_field',
            'body'                       => 'fcal_sanitize_html',
        ];

        return Helper::fcal_backend_sanitizer($settings, $sanitizerMap);
    }
}
