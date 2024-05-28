<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Availability;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\PaymentMethods\CurrenciesHelper;
use FluentBooking\App\Services\LandingPage\LandingPageHelper;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\App\Services\AvailabilityService;
use FluentBooking\App\Services\SanitizeService;
use FluentBooking\App\Services\CalendarService;
use FluentBooking\App\Services\BookingFieldService;
use FluentBooking\Framework\Request\Request;
use FluentBooking\Framework\Support\Arr;

class CalendarController extends Controller
{
    public function getAllCalendars(Request $request)
    {
        do_action('fluent_booking/before_get_all_calendars', $request);

        if (PermissionManager::hasAllCalendarAccess(true)) {
            $calendars = Calendar::with(['slots'])->latest()->paginate();
        } else {
            $calendars = Calendar::with(['slots'])->where('user_id', get_current_user_id())->latest()->paginate();
        }

        foreach ($calendars as $calendar) {
            $calendar->author_profile = $calendar->getAuthorProfile();
            $calendar->public_url = $calendar->getLandingPageUrl();
            foreach ($calendar->slots as $slot) {
                $slot->shortcode = '[fluent_booking id="' . $slot->id . '"]';
                $slot->public_url = $slot->getPublicUrl();
                $slot->duration = $slot->getDefaultDuration();
                $slot->price_total = $slot->getPricingTotal();
                $slot->location_fields = $slot->getLocationFields();
                $slot->author_profiles = $slot->isTeamEvent() ? $slot->getAuthorProfiles() : [];
                do_action_ref_array('fluent_booking/calendar_slot', [&$slot]);
            }

            if(empty($calendar->author_profile['ID'])) {
                $calendar->generic_error = '<p style="color: red; margin:0;">Connected Host user is missing</p>';
            }

            do_action_ref_array('fluent_booking/calendar', [&$calendar, 'lists']);
        }

        $data = [
            'calendars' => $calendars
        ];

        if (in_array('calendar_event_lists', $request->get('with', []))) {
            $data['calendar_event_lists'] = [
                'events' => CalendarService::getCalendarOptionsByTitle('without_team'),
                'teams'  => CalendarService::getCalendarOptionsByTitle('only_team')
            ];
        }

        return $data;
    }

    public function checkSlug(Request $request)
    {
        $slug = sanitize_text_field(trim($request->get('slug')));

        if (!Helper::isCalendarSlugAvailable($slug, true)) {
            return $this->sendError([
                'message' => __('The provided slug is not available. Please choose a different one', 'fluent-booking-pro')
            ], 422);
        }

        return [
            'status' => true
        ];
    }

    public function createCalendar(Request $request)
    {
        $data = $request->get('calendar');

        $rules = [
            'author_timezone'                            => 'required',
            'slot.duration'                              => 'required|int',
            'slot.event_type'                            => 'required',
            'slot.availability_type'                     => 'required',
            'slot.schedule_type'                         => 'required',
            'slot.title'                                 => 'required',
            'slot.weekly_schedules'                      => 'required_if:slot.schedule_type,weekly_schedules',
            'slot.location_settings.*.type'              => 'required',
            'slot.location_settings.*.host_phone_number' => 'required_if:location_settings.*.type,phone_organizer'
        ];

        $messages = [
            'author_timezone.required'                               => __('Author timezone field is required', 'fluent-booking-pro'),
            'slot.duration.required'                                 => __('Event duration field is required', 'fluent-booking-pro'),
            'slot.event_type.required'                               => __('Event type field is required', 'fluent-booking-pro'),
            'slot.availability_type.required'                        => __('Event availability type field is required', 'fluent-booking-pro'),
            'slot.schedule_type.required'                            => __('Event schedule type field is required', 'fluent-booking-pro'),
            'slot.title.required'                                    => __('Event title field is required', 'fluent-booking-pro'),
            'slot.weekly_schedules.required_if'                      => __('Event weekly schedules field is required', 'fluent-booking-pro'),
            'slot.location_settings.*.type.required'                 => __('Event location type field is required', 'fluent-booking-pro'),
            'slot.location_settings.*.host_phone_number.required_if' => __('Event location host phone number field is required', 'fluent-booking-pro')
        ];

        $validationConfig = apply_filters('fluent_booking/create_calendar_validation_rule', [
            'rules'    => $rules,
            'messages' => $messages
        ], $data);

        $this->validate($data, $validationConfig['rules'], $validationConfig['messages']);

        do_action('fluent_booking/before_create_calendar', $data, $this);

        if (!empty($data['user_id']) && PermissionManager::userCan('invite_team_members')) {
            $user = get_user_by('ID', $data['user_id']);
        } else {
            $user = get_user_by('ID', get_current_user_id());
        }

        if (!$user) {
            return $this->sendError([
                'message' => __('User not found', 'fluent-booking-pro')
            ], 422);
        }

        $title = sanitize_text_field(Arr::get($data, 'title', ''));

        $isTeam = $title ? true : false;

        if (!$isTeam && Calendar::where('user_id', $user->ID)->where('type', '!=', 'team')->first()) {
            return $this->sendError([
                'message' => __('The user already have a calendar. Please delete it first to create a new one', 'fluent-booking-pro')
            ], 422);
        }

        if (!$isTeam) {
            $userName = $user->user_login;
            if (is_email($userName)) {
                $userName = explode('@', $userName);
                $userName = $userName[0] . '-' . time();
            }
            $data['slug'] = sanitize_title($userName, '', 'display');
        } else {
            $data['slug'] = sanitize_title($title, '', 'display');
        }


        if (!Helper::isCalendarSlugAvailable($data['slug'], true)) {
            $data['slug'] .= '-' . time();
        }

        if (!empty($data['slug'])) {
            $slug = trim(sanitize_text_field($data['slug']));
            if (!Helper::isCalendarSlugAvailable($slug, true)) {
                return $this->sendError([
                    'message' => __('The provided slug is not available. Please choose a different one', 'fluent-booking-pro')
                ], 422);
            }

            $personName = trim($user->first_name . ' ' . $user->last_name);
            if (!$personName) {
                $personName = $user->display_name;
            }

            $calendarData = [
                'slug'            => $slug,
                'user_id'         => $user->ID,
                'title'           => $isTeam ? $title : $personName,
                'type'            => $isTeam ? 'team' : 'simple',
                'author_timezone' => sanitize_text_field($data['author_timezone']) ?: 'UTC',
            ];
            $calendar = Calendar::create($calendarData);
        } else {
            $calendar = Calendar::where('user_id', $user->ID)->first();
        }

        if (!$calendar) {
            return $this->sendError([
                'message' => __('Calendar could not be found. Please try again', 'fluent-booking-pro')
            ], 422);
        }

        $availability = AvailabilityService::getDefaultSchedule($calendar->user_id);

        if (!$availability) {
            $weeklySchedule = Arr::get($data, 'slot.weekly_schedules', []);

            $defaultSchedule = AvailabilityService::createScheduleSchema(
                $calendar->user_id, 'Weekly Hours', true, $calendar->author_timezone, 'UTC', $weeklySchedule
            );

            $availability = Availability::create($defaultSchedule);
        }

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
                'team_members'     => $isTeam ? [$user->ID] : [],
                'schedule_type'    => sanitize_text_field($slot['schedule_type']),
                'weekly_schedules' => SanitizeService::weeklySchedules($slot['weekly_schedules'], $calendar->author_timezone, 'UTC')
            ],
            'status'            => SanitizeService::checkCollection($slot['status'], ['active', 'draft']),
            'color_schema'      => sanitize_text_field(Arr::get($slot, 'color_schema', '#0099ff')),
            'event_type'        => sanitize_text_field(Arr::get($slot, 'event_type')),
            'availability_type' => SanitizeService::checkCollection($slot['availability_type'], ['existing_schedule', 'custom'], 'existing_schedule'),
            'availability_id'   => (int)$availability->id,
            'location_type'     => sanitize_text_field(Arr::get($slot, 'location_type')),
            'location_heading'  => wp_kses_post(Arr::get($slot, 'location_heading')),
            'location_settings' => SanitizeService::locationSettings(Arr::get($slot, 'location_settings', [])),
        ];

        $slotData['settings'] = wp_parse_args($slotData['settings'], (new CalendarSlot())->getSlotSettingsSchema());

        $slot = CalendarSlot::create($slotData);

        do_action('fluent_booking/after_create_calendar', $calendar);

        do_action('fluent_booking/after_create_calendar_slot', $slot, $calendar);

        return [
            'calendar'     => $calendar,
            'slot'         => $slot,
            'redirect_url' => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/slot-settings/' . $slot->id)
        ];
    }

    public function getCalendar(Request $request, $calendarId)
    {
        $calendar = Calendar::with(['slots'])->findOrFail($calendarId);

        $calendar->author_profile = $calendar->getAuthorProfile();

        $data = [
            'calendar' => $calendar
        ];

        if (in_array('settings_menu', $request->get('with', []))) {
            $data['settings_menu'] = apply_filters('fluent_booking/calendar_setting_menu_items', [
                'calendar_settings' => [
                    'type'    => 'route',
                    'route'   => [
                        'name'   => 'calendar_settings',
                        'params' => [
                            'calendar_id' => $calendar->id
                        ]
                    ],
                    'label'   => __('Calendar Settings', 'fluent-booking-pro'),
                    'svgIcon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 12.8799V11.1199C2 10.0799 2.85 9.21994 3.9 9.21994C5.71 9.21994 6.45 7.93994 5.54 6.36994C5.02 5.46994 5.33 4.29994 6.24 3.77994L7.97 2.78994C8.76 2.31994 9.78 2.59994 10.25 3.38994L10.36 3.57994C11.26 5.14994 12.74 5.14994 13.65 3.57994L13.76 3.38994C14.23 2.59994 15.25 2.31994 16.04 2.78994L17.77 3.77994C18.68 4.29994 18.99 5.46994 18.47 6.36994C17.56 7.93994 18.3 9.21994 20.11 9.21994C21.15 9.21994 22.01 10.0699 22.01 11.1199V12.8799C22.01 13.9199 21.16 14.7799 20.11 14.7799C18.3 14.7799 17.56 16.0599 18.47 17.6299C18.99 18.5399 18.68 19.6999 17.77 20.2199L16.04 21.2099C15.25 21.6799 14.23 21.3999 13.76 20.6099L13.65 20.4199C12.75 18.8499 11.27 18.8499 10.36 20.4199L10.25 20.6099C9.78 21.3999 8.76 21.6799 7.97 21.2099L6.24 20.2199C5.33 19.6999 5.02 18.5299 5.54 17.6299C6.45 16.0599 5.71 14.7799 3.9 14.7799C2.85 14.7799 2 13.9199 2 12.8799Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                ]
            ], $calendar);
        }

        return $data;
    }

    public function getSharingSettings(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);

        return [
            'settings'  => LandingPageHelper::getSettings($calendar),
            'share_url' => $calendar->getLandingPageUrl(true)
        ];
    }

    public function saveSharingSettings(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);

        $calendarDataItems = Arr::only($request->get('calendar_data', []), ['title', 'timezone', 'description', 'calendar_avatar', 'featured_image', 'phone']);

        if ($calendarDataItems) {
            $this->validate($calendarDataItems, [
                'title'           => 'required',
                'calendar_avatar' => 'url'
            ]);

            $updatedTimezone = sanitize_text_field(Arr::get($calendarDataItems, 'timezone'));
            if ($updatedTimezone && $updatedTimezone != $calendar->author_timezone) {
                CalendarService::updateCalendarEventsSchedule($calendarId, $calendar->author_timezone, $updatedTimezone);
                $calendar->author_timezone = $updatedTimezone;
            }

            $calendar->title = sanitize_text_field(Arr::get($calendarDataItems, 'title'));
            $calendar->description = wp_kses_post(Arr::get($calendarDataItems, 'description'));
            $calendar->save();
            $calendar->updateMeta('profile_photo_url', sanitize_url(Arr::get($calendarDataItems, 'calendar_avatar')));
            $calendar->updateMeta('featured_image_url', sanitize_url(Arr::get($calendarDataItems, 'featured_image')));

            if ($calendar->user) {
                $calendar->user->updateMeta('host_phone', sanitize_text_field(Arr::get($calendarDataItems, 'phone')));
            }
        }

        $sharingSettings = $request->get('landing_page_settings', []);
        LandingPageHelper::updateSettings($calendar, $sharingSettings);

        return [
            'message' => __('Landing Page settings has been updated', 'fluent-booking-pro')
        ];
    }

    public function updateCalendar(Request $request, $calendarId)
    {
        $data = $request->all();

        $calendar = Calendar::findOrFail($calendarId);

        do_action_ref_array('fluent_booking/before_update_calendar', [&$calendar, $data]);

        $calendar->description = wp_kses_post($request->get('description'));
        $calendar->save();
        do_action('fluent_booking/after_update_calendar', $calendar, $data);

        $calendar->author_profile = $calendar->getAuthorProfile();

        do_action_ref_array('fluent_booking/calendar', [&$calendar, 'update']);

        return [
            'calendar' => $calendar,
            'message'  => __('Calendar has been updated successfully', 'fluent-booking-pro')
        ];
    }

    public function getEvent(Request $request, $calendarId, $slotId)
    {
        $calendarEvent = CalendarSlot::where('calendar_id', $calendarId)->with(['calendar.user'])->findOrFail($slotId);

        $calendarEvent->author_profile = $calendarEvent->getAuthorProfile();

        $calendarEvent->calendar->author_profile = $calendarEvent->calendar->getAuthorProfile();

        $calendarEvent->public_url = $calendarEvent->getPublicUrl();

        $eventSettings = $calendarEvent->settings;

        $eventSettings['weekly_schedules'] = SanitizeService::weeklySchedules($eventSettings['weekly_schedules'], 'UTC', $calendarEvent->calendar->author_timezone);

        $eventSettings['date_overrides'] = (object)SanitizeService::slotDateOverrides(Arr::get($eventSettings, 'date_overrides', []), 'UTC', $calendarEvent->calendar->author_timezone, $calendarEvent);

        $eventSettings['location_fields'] = $calendarEvent->getLocationFields();

        $calendarEvent->settings = apply_filters('fluent_booking/get_calendar_event_settings', $eventSettings, $calendarEvent, $calendarEvent->calendar);

        $data = [
            'calendar_event' => $calendarEvent
        ];

        if (in_array('calendar', $this->request->get('with', []))) {
            $calendar = $calendarEvent->calendar;
            $calendar->author_profile = $calendar->getAuthorProfile();
            $data['calendar'] = $calendar;
        }

        if (in_array('smart_codes', $this->request->get('with', []))) {
            $data['smart_codes'] = [
                'texts' => Helper::getEditorShortCodes($calendarEvent),
                'html'  => Helper::getEditorShortCodes($calendarEvent, true)
            ];
        }

        if (in_array('settings_menu', $this->request->get('with', []))) {
            $data['settings_menu'] = Helper::getEventSettingsMenuItems($calendarEvent);
        }

        return $data;
    }

    public function getEventSchema(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);

        $userCalendarId = $calendar->type != 'team' ? $calendarId : null;

        $settingsSchema = (new CalendarSlot())->getSlotSettingsSchema($userCalendarId);

        $schema = [
            'title'             => '',
            'status'            => 'active',
            'description'       => '',
            'duration'          => '30',
            'color_schema'      => '#0099ff',
            'calendar'          => $calendar,
            'settings'          => $settingsSchema,
            'max_book_per_slot' => 1,
            'location_settings' => [
                [
                    'type'              => '',
                    'title'             => '',
                    'description'       => '',
                    'host_phone_number' => ''
                ]
            ]
        ];

        return [
            'slot' => $schema
        ];
    }

    public function getAvailabilitySettings(Request $request, $calendarId, $slotId)
    {
        $availableSchedules = AvailabilityService::availabilitySchedules();

        $scheduleOptions = AvailabilityService::getScheduleOptions();

        return [
            'schedule_options'    => $scheduleOptions,
            'available_schedules' => $availableSchedules
        ];
    }

    public function createCalendarEvent(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);

        $slot = $request->all();

        $rules = [
            'title'                                 => 'required',
            'duration'                              => 'required|int',
            'status'                                => 'required',
            'event_type'                            => 'required',
            'location_settings.*.type'              => 'required',
            'location_settings.*.title'             => 'required_if:location_settings.*.type,custom',
            'location_settings.*.description'       => 'required_if:location_settings.*.type,address_organizer',
            'location_settings.*.host_phone_number' => 'required_if:location_settings.*.type,phone_organizer'
        ];

        $messages = [
            'title.required'                                    => __('Event title field is required', 'fluent-booking-pro'),
            'duration.required'                                 => __('Event duration field is required', 'fluent-booking-pro'),
            'status.required'                                   => __('Event status field is required', 'fluent-booking-pro'),
            'event_type.required'                               => __('Event type field is required', 'fluent-booking-pro'),
            'location_settings.*.type.required'                 => __('Event location type field is required', 'fluent-booking-pro'),
            'location_settings.*.title.required_if'             => __('Event location title field is required', 'fluent-booking-pro'),
            'location_settings.*.description.required_if'       => __('Event location description field is required', 'fluent-booking-pro'),
            'location_settings.*.host_phone_number.required_if' => __('Event location host phone number field is required', 'fluent-booking-pro')
        ];

        $validationConfig = apply_filters('fluent_booking/create_calendar_event_validation_rule', [
            'rules'    => $rules,
            'messages' => $messages
        ], $slot);

        $this->validate($slot, $validationConfig['rules'], $validationConfig['messages']);

        $availability = AvailabilityService::getDefaultSchedule($calendar->user_id);

        $slotData = [
            'title'             => $slot['title'],
            'slug'              => Helper::generateSlotSlug($slot['duration'] . 'min', $calendar),
            'calendar_id'       => $calendar->id,
            'user_id'           => $calendar->user_id,
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
                'buffer_time_before'  => sanitize_text_field(Arr::get($slot['settings'], 'buffer_time_before', '0')),
                'buffer_time_after'   => sanitize_text_field(Arr::get($slot['settings'], 'buffer_time_after', '0')),
                'slot_interval'       => sanitize_text_field(Arr::get($slot['settings'], 'slot_interval', '')),
                'team_members'        => $calendar->type == 'team' ? [$calendar->user_id] : [],
            ],
            'status'            => SanitizeService::checkCollection($slot['status'], ['active', 'draft'], 'active'),
            'color_schema'      => sanitize_text_field(Arr::get($slot, 'color_schema', '#0099ff')),
            'event_type'        => sanitize_text_field(Arr::get($slot, 'event_type')),
            'availability_type' => 'existing_schedule',
            'availability_id'   => $availability->id,
            'location_type'     => sanitize_text_field(Arr::get($slot, 'location_type')),
            'location_settings' => SanitizeService::locationSettings(Arr::get($slot, 'location_settings', [])),
            'max_book_per_slot' => (int)Arr::get($slot, 'max_book_per_slot', 1),
            'is_display_spots'  => (bool)Arr::get($slot, 'is_display_spots', false),
        ];

        do_action('fluent_booking/before_create_event', $calendar, $slotData);

        $createdSlot = CalendarSlot::create($slotData);

        do_action('fluent_booking/after_create_event', $calendar, $createdSlot);

        return [
            'message' => __('New Event Type has been created successfully', 'fluent-booking-pro'),
            'slot'    => $createdSlot
        ];
    }

    public function updateEventDetails(Request $request, $calendarId, $eventId)
    {
        $data = $request->all();

        $event = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($eventId);

        $rules = [
            'title'                                 => 'required',
            'duration'                              => 'required|numeric',
            'location_settings.*.type'              => 'required',
            'location_settings.*.title'             => 'required_if:location_settings.*.type,in_person_organizer',
            'location_settings.*.host_phone_number' => 'required_if:location_settings.*.type,phone_organizer'
        ];

        $messages = [
            'title.required'                                    => __('Event title field is required', 'fluent-booking-pro'),
            'duration.required'                                 => __('Event duration field is required', 'fluent-booking-pro'),
            'location_settings.*.type.required'                 => __('Event location type field is required', 'fluent-booking-pro'),
            'location_settings.*.title.required_if'             => __('Event location title field is required', 'fluent-booking-pro'),
            'location_settings.*.host_phone_number.required_if' => __('Event location host phone number field is required', 'fluent-booking-pro')
        ];

        if ('group' === $event->event_type) {
            $rules = array_merge($rules, [
                'max_book_per_slot' => 'required|numeric|min:1',
                'is_display_spots'  => 'required|min:0|max:1',
            ]);
            $messages = array_merge($messages, [
                'max_book_per_slot.required' => __('Event max book per slot field is required', 'fluent-booking-pro'),
                'is_display_spots.required'  => __('Event is display spots field is required', 'fluent-booking-pro')
            ]);
        } else {
            $rules = array_merge($rules, [
                'multi_duration.default_duration'    => 'required_if:multi_duration.enabled,true',
                'multi_duration.available_durations' => 'required_if:multi_duration.enabled,true'
            ]);
            $messages = array_merge($messages, [
                'multi_duration.default_duration.required_if'    => __('Event default duration is required', 'fluent-booking-pro'),
                'multi_duration.available_durations.required_if' => __('Event available durations is required', 'fluent-booking-pro')
            ]);
        }

        $validationConfig = apply_filters('fluent_booking/update_event_details_validation_rule', [
            'rules'    => $rules,
            'messages' => $messages
        ], $event);

        $this->validate($data, $validationConfig['rules'], $validationConfig['messages']);

        $event->title = sanitize_text_field($data['title']);
        $event->duration = (int)$data['duration'];
        $event->status = SanitizeService::checkCollection($data['status'], ['active', 'draft']);
        $event->color_schema = sanitize_text_field(Arr::get($data, 'color_schema', '#0099ff'));
        $event->description = sanitize_textarea_field(Arr::get($data, 'description'));
        $event->max_book_per_slot = (int)Arr::get($data, 'max_book_per_slot');
        $event->is_display_spots = (bool)Arr::get($data, 'is_display_spots');
        $event->location_settings = SanitizeService::locationSettings(Arr::get($data, 'location_settings', []));

        $event->settings = [
            'multi_duration'  => [
                'enabled'             => Arr::isTrue($data, 'multi_duration.enabled'),
                'default_duration'    => Arr::get($data, 'multi_duration.default_duration', ''),
                'available_durations' => array_map('sanitize_text_field', Arr::get($data, 'multi_duration.available_durations', []))
            ]
        ];

        $event->save();

        do_action('fluent_booking/after_update_event_details', $event);

        return [
            'message' => __('Data has been updated', 'fluent-booking-pro'),
            'event'   => $event
        ];
    }

    public function updateEventAvailability(Request $request, $calendarId, $eventId)
    {
        $data = $request->all();

        $event = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($eventId);

        $event->settings = [
            'schedule_type'      => sanitize_text_field(Arr::get($data, 'schedule_type')),
            'weekly_schedules'   => SanitizeService::weeklySchedules(Arr::get($data, 'weekly_schedules'), $event->calendar->author_timezone, 'UTC'),
            'date_overrides'     => SanitizeService::slotDateOverrides(Arr::get($data, 'date_overrides', []), $event->calendar->author_timezone, 'UTC'),
            'range_type'         => sanitize_text_field(Arr::get($data, 'range_type')),
            'range_days'         => (int)(Arr::get($data, 'range_days', 60)) ?: 60,
            'range_date_between' => SanitizeService::rangeDateBetween(Arr::get($data, 'range_date_between', ['', ''])),
            'common_schedule'    => Arr::isTrue($data, 'common_schedule', false)
        ];

        $event->availability_id = (int)Arr::get($data, 'availability_id');
        $event->availability_type = SanitizeService::checkCollection(Arr::get($data, 'availability_type'), ['existing_schedule', 'custom']);

        $event->save();

        return [
            'message' => __('Data has been updated', 'fluent-booking-pro'),
            'event'   => $event
        ];
    }

    public function updateAssignments(Request $request, $calendarId, $eventId)
    {
        $data = $request->all();

        $this->validate($data,
            ['team_members' => 'required'],
            ['team_members.required' => __('There should be at least one member', 'fluent-booking-pro')]
        );

        $event = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($eventId);

        $event->settings = [
            'team_members' => array_map('intval', Arr::get($data, 'team_members', []))
        ];

        $event->save();

        return [
            'message' => __('Data has been updated', 'fluent-booking-pro'),
            'event'   => $event
        ];
    }

    public function updateEventLimits(Request $request, $calendarId, $eventId)
    {
        $data = $request->all();

        $event = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($eventId);

        $event->settings = [
            'schedule_conditions'   => SanitizeService::scheduleConditions(Arr::get($data['settings'], 'schedule_conditions', [])),
            'buffer_time_before'    => sanitize_text_field(Arr::get($data, 'settings.buffer_time_before', '0')),
            'buffer_time_after'     => sanitize_text_field(Arr::get($data, 'settings.buffer_time_after', '0')),
            'slot_interval'         => sanitize_text_field(Arr::get($data, 'settings.slot_interval', '')),
            'booking_frequency'     => [
                'enabled' => Arr::isTrue($data, 'settings.booking_frequency.enabled'),
                'limits'  => $this->sanitize_mapped_data(Arr::get($data, 'settings.booking_frequency.limits'))
            ],
            'booking_duration'      => [
                'enabled' => Arr::isTrue($data, 'settings.booking_duration.enabled'),
                'limits'  => $this->sanitize_mapped_data(Arr::get($data, 'settings.booking_duration.limits'))
            ],
            'lock_timezone'         => [
                'enabled'  => Arr::isTrue($data, 'settings.lock_timezone.enabled'),
                'timezone' => sanitize_text_field(Arr::get($data, 'settings.lock_timezone.timezone'))
            ],
        ];

        $event->save();

        return [
            'message' => __('Data has been updated', 'fluent-booking-pro'),
            'event'   => $event
        ];
    }

    public function updateAdvancedSettings(Request $request, $calendarId, $eventId)
    {
        $data = $request->all();

        $event = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($eventId);

        $rules = [
            'slug'                                  => 'required',
            'custom_redirect.redirect_url'          => 'required_if:custom_redirect.enabled,true',
            'custom_redirect.query_string'          => 'required_if:custom_redirect.is_query_string,yes',
            'can_not_cancel.type'                   => ['required_if:can_not_cancel.enabled,true','in:always,conditional'],
            'can_not_cancel.condition.unit'         => ['required_if:can_not_cancel.type,conditional','in:minutes,hours,days'],
            'can_not_cancel.condition.value'        => ['required_if:can_not_cancel.type,conditional','integer','min:1'],
            'can_not_reschedule.type'               => ['required_if:can_not_reschedule.enabled,true','in:always,conditional'],
            'can_not_reschedule.condition.unit'     => ['required_if:can_not_reschedule.type,conditional','in:minutes,hours,days'],
            'can_not_reschedule.condition.value'    => ['required_if:can_not_reschedule.type,conditional','integer','min:1'],
            'requires_confirmation.type'            => ['required_if:requires_confirmation.enabled,true','in:always,conditional'],
            'requires_confirmation.condition.unit'  => ['required_if:requires_confirmation.type,conditional','in:minutes,hours,days'],
            'requires_confirmation.condition.value' => ['required_if:requires_confirmation.type,conditional','integer','min:1'],
        ];

        $messages = [
            'slug.required'                                     => __('Event slug field is required', 'fluent-booking-pro'),
            'custom_redirect.redirect_url.required_if'          => __('Event redirect url field is required', 'fluent-booking-pro'),
            'custom_redirect.query_string.required_if'          => __('Event query string field is required', 'fluent-booking-pro'),
            'requires_confirmation.type.required_if'            => __('Event confirmation type field is required', 'fluent-booking-pro'),
            'requires_confirmation.type.in'                     => __('Event confirmation type field is invalid', 'fluent-booking-pro'),
            'requires_confirmation.condition.unit.in'           => __('Event confirmation condition unit field is invalid', 'fluent-booking-pro'),
            'requires_confirmation.condition.value.integer'     => __('Event confirmation condition value field is invalid', 'fluent-booking-pro'),
            'requires_confirmation.condition.unit.required_if'  => __('Event confirmation condition unit field is required', 'fluent-booking-pro'),
            'requires_confirmation.condition.value.required_if' => __('Event confirmation condition value field is required', 'fluent-booking-pro')
        ];

        $validationConfig = apply_filters('fluent_booking/update_advanced_settings_validation_rule', [
            'rules'    => $rules,
            'messages' => $messages
        ], $event);

        $this->validate($data, $validationConfig['rules'], $validationConfig['messages']);

        if ($slug = sanitize_title(Arr::get($data, 'slug'))) {
            if (!Helper::isEventSlugAvailable($slug, true, $calendarId, $eventId)) {
                return $this->sendError([
                    'message' => __('The provided slug is not available. Please choose a different one', 'fluent-booking-pro')
                ], 422);
            }
            $event->slug = $slug;
        }

        $event->settings = [
            'booking_title'   => sanitize_text_field(Arr::get($data, 'booking_title')),
            'custom_redirect' => [
                'enabled'         => Arr::isTrue($data, 'custom_redirect.enabled'),
                'redirect_url'    => sanitize_text_field(Arr::get($data, 'custom_redirect.redirect_url')),
                'is_query_string' => Arr::get($data, 'custom_redirect.is_query_string') == 'yes' ? 'yes' : 'no',
                'query_string'    => sanitize_text_field(Arr::get($data, 'custom_redirect.query_string')),
            ],
            'requires_confirmation' => [
                'enabled'   => Arr::isTrue($data, 'requires_confirmation.enabled'),
                'type'      => sanitize_text_field(Arr::get($data, 'requires_confirmation.type')),
                'condition' => [
                    'unit'  => sanitize_text_field(Arr::get($data, 'requires_confirmation.condition.unit')),
                    'value' => intval(Arr::get($data, 'requires_confirmation.condition.value'))
                ]
            ],
            'can_not_cancel'       => [
                'enabled'   => Arr::isTrue($data, 'can_not_cancel.enabled'),
                'type'      => sanitize_text_field(Arr::get($data, 'can_not_cancel.type')),
                'message'   => sanitize_text_field(Arr::get($data, 'can_not_cancel.message')),
                'condition' => [
                    'unit'  => sanitize_text_field(Arr::get($data, 'can_not_cancel.condition.unit')),
                    'value' => intval(Arr::get($data, 'can_not_cancel.condition.value'))
                ]
            ],
            'can_not_reschedule'   => [
                'enabled'   => Arr::isTrue($data, 'can_not_reschedule.enabled'),
                'type'      => sanitize_text_field(Arr::get($data, 'can_not_reschedule.type')),
                'message'   => sanitize_text_field(Arr::get($data, 'can_not_reschedule.message')),
                'condition' => [
                    'unit'  => sanitize_text_field(Arr::get($data, 'can_not_reschedule.condition.unit')),
                    'value' => intval(Arr::get($data, 'can_not_reschedule.condition.value'))
                ]
            ]
        ];

        $event->save();

        do_action('fluent_booking/after_update_advanced_settings', $event);

        return [
            'message' => __('Data has been updated', 'fluent-booking-pro'),
            'event'   => $event
        ];
    }

    public function patchCalendarEvent(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        $status = $request->get('status');

        if ($status) {
            $slot->status = $status;
            $slot->save();
        }

        return [
            'message' => __('Data has been updated', 'fluent-booking-pro')
        ];

    }

    public function cloneCalendarEvent(Request $request, $calendarId, $eventId)
    {
        $newCalendarId = intval($request->get('new_calendar_id')) ?: $calendarId;

        $calendar = Calendar::findOrFail($newCalendarId);

        $originalEvent = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($eventId);

        $clonedEvent = $originalEvent->replicate();

        $clonedEvent->calendar_id = $calendar->id;

        $clonedEvent->user_id = $calendar->user_id;

        $clonedEvent->title = $originalEvent->title . ' (clone)';

        $clonedEvent->slug = Helper::generateSlotSlug($clonedEvent->duration . 'min', $calendar);

        $clonedEvent->save();

        $eventsMeta = $originalEvent->getCalendarEventsMeta();

        $integrationsMeta = $originalEvent->getIntegrationsMeta();

        foreach ($eventsMeta as $meta) {
            $clonedMeta = $meta->replicate();
            $clonedMeta->object_id = $clonedEvent->id;
            $clonedMeta->save();
        }

        foreach ($integrationsMeta as $meta) {
            $clonedMeta = $meta->replicate();
            $clonedMeta->object_id = $clonedEvent->id;
            $clonedMeta->save();
        }

        return [
            'message' => __('The Event Type has been cloned successfully', 'fluent-booking-pro'),
            'slot'    => $clonedEvent
        ];
    }

    public function getEventEmailNotifications(Request $request, $calendarId, $slotId)
    {
        $calendarEvent = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        /*
         * Confirmation Email to Attendee
         * Confirmation Email to Organizer
         * Reminder Email to Attendee [before 1 day, 1 hour, 30 minutes, 5 minutes]
         * Cancelled By Organizer to Attendee
         * Cancelled By Attendee to Organizer
         */
        $data = [
            'notifications' => $calendarEvent->getNotifications(true)
        ];

        if (in_array('smart_codes', $request->get('with', []))) {
            $data['smart_codes'] = [
                'texts' => Helper::getEditorShortCodes($calendarEvent),
                'html'  => Helper::getEditorShortCodes($calendarEvent, true)
            ];
        }

        return $data;
    }

    public function saveEventEmailNotifications(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        $notifications = $request->get('notifications', []);

        $formattedNotifications = [];

        foreach ($notifications as $key => $value) {
            $formattedNotifications[$key] = [
                'title'   => sanitize_text_field(Arr::get($value, 'title')),
                'enabled' => Arr::isTrue($value, 'enabled'),
                'email'   => $this->sanitize_mapped_data(Arr::get($value, 'email')),
                'is_host' => Arr::isTrue($value, 'is_host')
            ];
        }

        $slot->setNotifications($formattedNotifications);

        return [
            'message' => __('Notifications has been saved', 'fluent-booking-pro')
        ];
    }

    public function getEventBookingFields(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        return [
            'fields' => $slot->getBookingFields()
        ];
    }

    public function saveEventBookingFields(Request $request, $calendarId, $eventId)
    {
        $calendarEvent = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($eventId);

        $bookingFields = $request->get('booking_fields');

        $optionRequiredFields = ['dropdown', 'radio', 'checkbox-group', 'multi-select'];

        $formattedFields = [];

        $textFields = ['type', 'name', 'label', 'placeholder', 'limit', 'help_text', 'date_format'];
        $booleanFields = ['enabled', 'required', 'system_defined', 'disable_alter', 'is_sms_number'];

        foreach ($bookingFields as $value) {
            if (empty($value['name'])) {
                $value['name'] = BookingFieldService::generateFieldName($calendarEvent, $value['label']);
            }

            $textValues = array_map('sanitize_text_field', Arr::only($value, $textFields));

            $booleanValues = array_map(function ($valueItem) {
                return $valueItem === true || $valueItem === 'true' || $valueItem == 1;
            }, Arr::only($value, $booleanFields));

            $formattedField = array_merge($textValues, $booleanValues);

            $formattedField['index'] = (int)Arr::get($value, 'index');
            if ($value['type'] == 'payment' && $calendarEvent->type === 'paid') {
                $formattedField['payment_items'] = Arr::get($value, 'payment_items');
                $formattedField['currency_sign'] = \FluentBooking\App\Services\Integrations\PaymentMethods\CurrenciesHelper::getGlobalCurrencySign();
            }
            if (in_array(Arr::get($value, 'type'), $optionRequiredFields)) {
                $sanitizedOptions = array_map('sanitize_text_field', Arr::get($value, 'options'));
                $formattedField['options'] = $sanitizedOptions;
            }

            $formattedFields[] = $formattedField;
        }

        $calendarEvent->setBookingFields($formattedFields);

        return [
            'message' => __('Fields has been updated', 'fluent-booking-pro')
        ];
    }

    public function deleteCalendarEvent(Request $request, $calendarId, $calendarEventId)
    {
        $calendar = Calendar::query()->findOrFail($calendarId);
        $calendarEvent = CalendarSlot::query()->where('calendar_id', $calendar->id)->findOrFail($calendarEventId);

        do_action('fluent_booking/before_delete_calendar_event', $calendarEvent, $calendar);
        $calendarEvent->delete();
        do_action('fluent_booking/after_delete_calendar_event', $calendarEventId, $calendar);

        return [
            'message' => __('Calendar Event has been deleted', 'fluent-booking-pro')
        ];
    }

    private function sanitize_mapped_data($settings)
    {
        $sanitizerMap = [
            'value'                 => 'intval',
            'unit'                  => 'sanitize_text_field',
            'subject'               => 'sanitize_text_field',
            'body'                  => 'fcal_sanitize_html',
            'additional_recipients' => 'sanitize_text_field'
        ];

        return Helper::fcal_backend_sanitizer($settings, $sanitizerMap);
    }

    public function deleteCalendar(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);

        do_action('fluent_booking/before_delete_calendar', $calendar);

        $calendar->delete();

        do_action('fluent_booking/after_delete_calendar', $calendarId);

        return [
            'message' => __('Calendar Deleted Successfully!', 'fluent-booking-pro')
        ];
    }
}
