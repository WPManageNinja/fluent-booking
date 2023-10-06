<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Availability;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\LandingPage\LandingPageHelper;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\App\Services\AvailabilityService;
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
            $calendar->public_url = $calendar->getLandingPageUrl();
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
            'author_timezone'        => 'required',
            'slot.duration'          => 'required|int',
            'slot.event_type'        => 'required',
            'slot.availability_type' => 'required',
            'slot.schedule_type'     => 'required',
            'slot.title'             => 'required',
            'slot.weekly_schedules'  => 'required_if:slot.schedule_type,weekly_schedules',
            'user_id'                => 'required|int'
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

        $defaultSchedule = AvailabilityService::defaultScheduleSchema(
            $calendar->user_id, 'Weekly Hours', true, $calendar->author_timezone
        );

        $availability = Availability::create($defaultSchedule);

        $slot = $data['slot'];
        $title = (!empty($slot['title'])) ? sanitize_text_field($slot['title']) : $slot['duration'] . ' Minute Meeting';

        $slotData = [
            'title'             => $title,
            'slug'              => Helper::generateSlotSlug($slot['duration'] . 'min', $calendar),
            'calendar_id'       => $calendar->id,
            'user_id'           => $calendar->user_id,
            'duration'          => (int)$slot['duration'],
            'description'       => sanitize_textarea_field(Arr::get($slot, 'description')),
            'settings'          => [
                'schedule_type'    => sanitize_text_field($slot['schedule_type']),
                'weekly_schedules' => SanitizeService::weeklySchedules($slot['weekly_schedules'], $calendar->author_timezone, 'UTC')
            ],
            'status'            => SanitizeService::checkCollection($slot['status'], ['active', 'draft']),
            'color_schema'      => sanitize_text_field(Arr::get($slot, 'color_schema', '#0099ff')),
            'event_type'        => sanitize_text_field(Arr::get($slot, 'event_type')),
            'availability_type' => SanitizeService::checkCollection($slot['availability_type'], ['existing_schedule', 'custom']),
            'availability_id'   => (int)$availability->id,
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

        $calendar->author_profile = $calendar->getAuthorProfile();

        $data = [
            'calendar' => $calendar
        ];

        if (in_array('settings_menu', $request->get('with', []))) {
            $baseUrl = Helper::getAppBaseUrl();
            $data['settings_menu'] = apply_filters('fluent_booking/calendar_setting_menu_items', [
                'google_calendar'       => [
                    'type'    => 'route',
                    'route'   => [
                        'name'   => 'google_calendar',
                        'params' => [
                            'id' => $calendar->id
                        ]
                    ],
                    'label'   => __('Google Calendar', 'fluent-booking'),
                    'svgIcon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20.4935 2H17.2422V6.71125H21.9997V3.32845C22.001 3.32845 21.7646 2.12848 20.4935 2Z" fill="#1967D2"/><path d="M17.2422 21.978V21.9895V21.9998L21.9997 17.2886H21.957L17.2422 21.978Z" fill="#1967D2"/><path d="M22.0032 17.2883V17.2461L21.9609 17.2883H22.0032Z" fill="#FBBC05"/><path d="M21.9997 6.71143H17.2422V17.2465H21.9997V6.71143Z" fill="#FBBC05"/><path d="M21.957 17.2886H17.2422V21.978L21.957 17.2886Z" fill="#EA4335"/><path d="M17.2422 17.2883H21.957L21.9997 17.2461H17.2422V17.2883Z" fill="#EA4335"/><path d="M17.2266 21.9896H17.2381V21.978L17.2266 21.9896Z" fill="#34A853"/><path d="M6.64844 17.2461V21.9895H17.2291L17.2407 17.2461H6.64844Z" fill="#34A853"/><path d="M17.2381 17.2885V17.2461L17.2266 21.9895L17.2381 21.9779V17.2885Z" fill="#34A853"/><path d="M2 17.2461V20.5441C2.04263 21.6143 3.20002 21.9895 3.20002 21.9895H6.65026V17.2461H2Z" fill="#188038"/><path d="M6.65026 6.71125H17.2425V2H3.33437C3.33437 2 2.08525 2.12848 2 3.45564V17.2464H6.65026V6.71125Z" fill="#4285F4"/><path d="M10.1204 15.114C9.87806 15.114 9.64438 15.0824 9.41936 15.0193C9.20011 14.9562 8.99817 14.8615 8.81354 14.7352C8.6289 14.6032 8.46447 14.4397 8.32022 14.2446C8.18175 14.0495 8.07501 13.8228 8 13.5645L9.06452 13.1428C9.13953 13.4297 9.26646 13.6478 9.44533 13.797C9.62419 13.9404 9.84921 14.0122 10.1204 14.0122C10.2416 14.0122 10.357 13.9949 10.4666 13.9605C10.5762 13.9203 10.6714 13.8658 10.7522 13.797C10.833 13.7281 10.8964 13.6478 10.9426 13.5559C10.9945 13.4584 11.0205 13.3493 11.0205 13.2288C11.0205 12.9763 10.9253 12.7784 10.7349 12.6349C10.5502 12.4914 10.2935 12.4197 9.96461 12.4197H9.45398V11.3953H9.92133C10.0367 11.3953 10.1492 11.381 10.2589 11.3523C10.3685 11.3236 10.4637 11.2806 10.5445 11.2232C10.631 11.1601 10.6974 11.0826 10.7435 10.9908C10.7955 10.8932 10.8214 10.7813 10.8214 10.6551C10.8214 10.4599 10.7522 10.3021 10.6137 10.1816C10.4752 10.0554 10.2877 9.99225 10.0512 9.99225C9.79728 9.99225 9.60111 10.0611 9.46264 10.1988C9.32993 10.3308 9.23762 10.48 9.18569 10.6465L8.14713 10.2247C8.19906 10.0812 8.27695 9.93486 8.3808 9.78566C8.48466 9.63071 8.61448 9.49298 8.77026 9.37247C8.93182 9.24622 9.11934 9.1458 9.33282 9.07119C9.5463 8.99085 9.79151 8.95068 10.0685 8.95068C10.3512 8.95068 10.6079 8.99085 10.8387 9.07119C11.0753 9.15154 11.2772 9.26344 11.4446 9.40691C11.6119 9.54463 11.7417 9.71105 11.834 9.90617C11.9263 10.0955 11.9725 10.3021 11.9725 10.5259C11.9725 10.6981 11.9494 10.853 11.9032 10.9908C11.8629 11.1285 11.808 11.2519 11.7388 11.3609C11.6696 11.4699 11.5888 11.5646 11.4965 11.645C11.4099 11.7196 11.3205 11.7798 11.2282 11.8257V11.8946C11.5051 12.0036 11.733 12.1787 11.9119 12.4197C12.0965 12.6607 12.1889 12.9649 12.1889 13.3321C12.1889 13.5904 12.1398 13.8285 12.0417 14.0466C11.9436 14.2589 11.8023 14.4454 11.6176 14.6061C11.4388 14.7668 11.2224 14.8902 10.9685 14.9763C10.7147 15.0681 10.432 15.114 10.1204 15.114Z" fill="#4285F4"/<path d="M14.2556 14.9763V10.414L13.2084 10.853L12.793 9.89756L14.5326 9.08841H15.3894V14.9763H14.2556Z" fill="#4285F4"/></svg>'
                ],
                'landing_page_settings' => [
                    'type'    => 'route',
                    'route'   => [
                        'name'   => 'landing_page_settings',
                        'params' => [
                            'id' => $calendar->id
                        ]
                    ],
                    'label'   => __('Landing Page Settings', 'fluent-booking'),
                    'svgIcon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 12.8799V11.1199C2 10.0799 2.85 9.21994 3.9 9.21994C5.71 9.21994 6.45 7.93994 5.54 6.36994C5.02 5.46994 5.33 4.29994 6.24 3.77994L7.97 2.78994C8.76 2.31994 9.78 2.59994 10.25 3.38994L10.36 3.57994C11.26 5.14994 12.74 5.14994 13.65 3.57994L13.76 3.38994C14.23 2.59994 15.25 2.31994 16.04 2.78994L17.77 3.77994C18.68 4.29994 18.99 5.46994 18.47 6.36994C17.56 7.93994 18.3 9.21994 20.11 9.21994C21.15 9.21994 22.01 10.0699 22.01 11.1199V12.8799C22.01 13.9199 21.16 14.7799 20.11 14.7799C18.3 14.7799 17.56 16.0599 18.47 17.6299C18.99 18.5399 18.68 19.6999 17.77 20.2199L16.04 21.2099C15.25 21.6799 14.23 21.3999 13.76 20.6099L13.65 20.4199C12.75 18.8499 11.27 18.8499 10.36 20.4199L10.25 20.6099C9.78 21.3999 8.76 21.6799 7.97 21.2099L6.24 20.2199C5.33 19.6999 5.02 18.5299 5.54 17.6299C6.45 16.0599 5.71 14.7799 3.9 14.7799C2.85 14.7799 2 13.9199 2 12.8799Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                ],
                'webhook_settings' => [
                    'type'    => 'route',
                    'route'   => [
                        'name'   => 'webhook_settings',
                        'params' => [
                            'id' => $calendar->id
                        ]
                    ],
                    'label'   => __('Webhook Settings', 'fluent-booking'),
                    'svgIcon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 12.8799V11.1199C2 10.0799 2.85 9.21994 3.9 9.21994C5.71 9.21994 6.45 7.93994 5.54 6.36994C5.02 5.46994 5.33 4.29994 6.24 3.77994L7.97 2.78994C8.76 2.31994 9.78 2.59994 10.25 3.38994L10.36 3.57994C11.26 5.14994 12.74 5.14994 13.65 3.57994L13.76 3.38994C14.23 2.59994 15.25 2.31994 16.04 2.78994L17.77 3.77994C18.68 4.29994 18.99 5.46994 18.47 6.36994C17.56 7.93994 18.3 9.21994 20.11 9.21994C21.15 9.21994 22.01 10.0699 22.01 11.1199V12.8799C22.01 13.9199 21.16 14.7799 20.11 14.7799C18.3 14.7799 17.56 16.0599 18.47 17.6299C18.99 18.5399 18.68 19.6999 17.77 20.2199L16.04 21.2099C15.25 21.6799 14.23 21.3999 13.76 20.6099L13.65 20.4199C12.75 18.8499 11.27 18.8499 10.36 20.4199L10.25 20.6099C9.78 21.3999 8.76 21.6799 7.97 21.2099L6.24 20.2199C5.33 19.6999 5.02 18.5299 5.54 17.6299C6.45 16.0599 5.71 14.7799 3.9 14.7799C2.85 14.7799 2 13.9199 2 12.8799Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                ]
            ], $calendar);
        }

        return $data;
    }

    public function getSharingSettings(Request $request, $id)
    {
        $calendar = Calendar::findOrFail($id);

        return [
            'settings' => LandingPageHelper::getSettings($calendar)
        ];
    }

    public function saveSharingSettings(Request $request, $id)
    {
        $calendar = Calendar::findOrFail($id);

        $description = wp_kses_post($request->get('description'));
        $calendar->description = $description;
        $calendar->save();

        $sharingSettings = $request->get('settings', []);
        LandingPageHelper::updateSettings($calendar, $sharingSettings);

        return [
            'message' => __('Landing Page settings has been updated', 'fluent-booking')
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

        $availableSchedules = AvailabilityService::availablitySchedules($slot->calendar->author_timezone);

        $scheduleOptions = AvailabilityService::getScheduleOptions();

        $slotSettings['schedule_options'] = $scheduleOptions;

        $slotSettings['available_schedules'] = $availableSchedules;

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
            'title'        => '',
            'status'       => 'active',
            'description'  => '',
            'duration'     => '30',
            'color_schema' => '#0099ff',
            'calendar'     => $calendar,
            'settings'     => $settingsSchema
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

        $availability = Availability::where('object_type', 'availability')
            ->where('object_id', $calendar->user_id)
            ->first();

        $availabilityId = $availability ? $availability->id : '';

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
            'availability_type' => 'existing_schedule',
            'availability_id'   => $availabilityId,
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
            'title'         => 'required',
            'duration'      => 'required|numeric',
            'location_type' => 'required'
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
        $slot->is_display_spots = (bool)Arr::get($data, 'is_display_spots');
        $slot->availability_id = (int)Arr::get($data, 'availability_id');
        $slot->availability_type = SanitizeService::checkCollection($data['availability_type'], ['existing_schedule', 'custom']);
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

    private function sanitize_data($settings)
    {

        $sanitizerMap = [
            'value'   => 'intval',
            'unit'    => 'sanitize_text_field',
            'subject' => 'sanitize_text_field',
            'body'    => 'fcal_sanitize_html',
        ];

        return Helper::fcal_backend_sanitizer($settings, $sanitizerMap);
    }

    public function deleteCalendar(Request $request, $calendarId)
    {
        $calendar     = Calendar::findOrFail($calendarId);
        $slots        = CalendarSlot::where('calendar_id', $calendar->id);
        $bookings     = Booking::where('calendar_id', $calendar->id);
        $availability = Availability::where('object_id', $calendar->user_id);

        // Let's delete all the data related to this caledar
        $bookings->delete();

        $slots->delete();

        $availability->delete();

        $calendar->delete();

        return [
            'message' => __('Calendar Deleted Successfully!', 'fluent-booking')
        ];
    }
}
