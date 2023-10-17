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
use FluentBooking\Framework\Request\Request;
use FluentBooking\Framework\Support\Arr;

class CalendarController extends Controller
{
    public function getAllCalendars(Request $request)
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
                $slot->public_url = $slot->getPublicUrl();
                $slot->price_total = $slot->getPricingTotal();
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
            ], 422);
        }

        return [
            'status' => true
        ];
    }

    public function createCalendar(Request $request)
    {
        $data = $request->get('calendar');

        $this->validate($data, apply_filters('fluent_booking/create_calender_validation_rule', [
            'author_timezone'                            => 'required',
            'slot.duration'                              => 'required|int',
            'slot.event_type'                            => 'required',
            'slot.availability_type'                     => 'required',
            'slot.schedule_type'                         => 'required',
            'slot.title'                                 => 'required',
            'slot.weekly_schedules'                      => 'required_if:slot.schedule_type,weekly_schedules',
            'user_id'                                    => 'required|int',
            'slot.location_settings.*.type'              => 'required',
            'slot.location_settings.*.host_phone_number' => 'required_if:location_settings.*.type,phone_organizer'
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
                ], 422);
            }

            $personName = trim($user->first_name . ' ' . $user->last_name);
            if (!$personName) {
                $personName = $user->display_name;
            }

            $calendarData = [
                'user_id' => $user->ID,
                'title'   => $personName,
                'slug'    => $slug
            ];

            $calendar = Calendar::create($calendarData);
        } else {
            $calendar = Calendar::where('user_id', $user->ID)->first();
        }

        if (!$calendar) {
            return $this->sendError([
                'message' => __('Calendar could not be found. Please try again', 'fluent-booking')
            ], 422);
        }

        if (!empty($data['author_timezone'])) {
            $calendar->author_timezone = sanitize_text_field($data['author_timezone']);
            $calendar->save();
        } else {
            $data['author_timezone'] = 'UTC';
        }

        $weeklySchedule = Arr::get($data, 'slot.weekly_schedules');

        $defaultSchedule = AvailabilityService::createScheduleSchema(
            $calendar->user_id, 'Weekly Hours', true, $calendar->author_timezone, 'UTC', $weeklySchedule
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
            'location_settings' => SanitizeService::locationSettings(Arr::get($slot, 'location_settings', [])),
        ];

        $slotData['settings'] = wp_parse_args($slotData['settings'], (new CalendarSlot())->getSlotSettingsSchema($calendar));

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
            $data['settings_menu'] = apply_filters('fluent_booking/calendar_setting_menu_items', [
                'calendar_settings' => [
                    'type'    => 'route',
                    'route'   => [
                        'name'   => 'calendar_settings',
                        'params' => [
                            'id' => $calendar->id
                        ]
                    ],
                    'label'   => __('Calendar Settings', 'fluent-booking'),
                    'svgIcon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 12.8799V11.1199C2 10.0799 2.85 9.21994 3.9 9.21994C5.71 9.21994 6.45 7.93994 5.54 6.36994C5.02 5.46994 5.33 4.29994 6.24 3.77994L7.97 2.78994C8.76 2.31994 9.78 2.59994 10.25 3.38994L10.36 3.57994C11.26 5.14994 12.74 5.14994 13.65 3.57994L13.76 3.38994C14.23 2.59994 15.25 2.31994 16.04 2.78994L17.77 3.77994C18.68 4.29994 18.99 5.46994 18.47 6.36994C17.56 7.93994 18.3 9.21994 20.11 9.21994C21.15 9.21994 22.01 10.0699 22.01 11.1199V12.8799C22.01 13.9199 21.16 14.7799 20.11 14.7799C18.3 14.7799 17.56 16.0599 18.47 17.6299C18.99 18.5399 18.68 19.6999 17.77 20.2199L16.04 21.2099C15.25 21.6799 14.23 21.3999 13.76 20.6099L13.65 20.4199C12.75 18.8499 11.27 18.8499 10.36 20.4199L10.25 20.6099C9.78 21.3999 8.76 21.6799 7.97 21.2099L6.24 20.2199C5.33 19.6999 5.02 18.5299 5.54 17.6299C6.45 16.0599 5.71 14.7799 3.9 14.7799C2.85 14.7799 2 13.9199 2 12.8799Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                ],
                'remote_calendars'  => [
                    'type'    => 'route',
                    'route'   => [
                        'name'   => 'remote_calendars',
                        'params' => [
                            'id' => $calendar->id
                        ]
                    ],
                    'label'   => __('Remote Calendars', 'fluent-booking'),
                    'svgIcon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-[16px] w-[16px] stroke-[2px] ltr:mr-2 rtl:ml-2 md:mt-0" data-testid="icon-component"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>'
                ]
            ], $calendar);
        }

        return $data;
    }

    public function getSharingSettings(Request $request, $id)
    {
        $calendar = Calendar::findOrFail($id);

        return [
            'settings'  => LandingPageHelper::getSettings($calendar),
            'share_url' => $calendar->getLandingPageUrl(true)
        ];
    }

    public function saveSharingSettings(Request $request, $id)
    {
        $calendar = Calendar::findOrFail($id);

        $calendarDataItems = Arr::only($request->get('calendar_data', []), ['title', 'description', 'calendar_avatar', 'featured_image']);

        if ($calendarDataItems) {
            $this->validate($calendarDataItems, [
                'title'           => 'required',
                'calendar_avatar' => 'url'
            ]);

            $calendar->title = sanitize_text_field(Arr::get($calendarDataItems, 'title'));
            $calendar->description = wp_kses_post(Arr::get($calendarDataItems, 'description'));
            $calendar->save();
            $calendar->updateMeta('profile_photo_url', sanitize_url(Arr::get($calendarDataItems, 'calendar_avatar')));
            $calendar->updateMeta('featured_image_url', sanitize_url(Arr::get($calendarDataItems, 'featured_image')));
        }


        $sharingSettings = $request->get('landing_page_settings', []);
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

        $slot->public_url = $slot->getPublicUrl();

        $slotSettings = $slot->settings;

        $slotSettings['weekly_schedules'] = SanitizeService::weeklySchedules($slotSettings['weekly_schedules'], 'UTC', $slot->calendar->author_timezone);

        $slotSettings['date_overrides'] = (object)SanitizeService::slotDateOverrides(Arr::get($slotSettings, 'date_overrides', []), 'UTC', $slot->calendar->author_timezone, $slot);

        $availableSchedules = AvailabilityService::availablitySchedules($slot->calendar->author_timezone);

        $scheduleOptions = AvailabilityService::getScheduleOptions();

        $slotSettings['schedule_options'] = $scheduleOptions;

        $slotSettings['available_schedules'] = $availableSchedules;

        $slotSettings['location_fields'] = $slot->calendar->getLocationFields();

        $slot->settings = $slotSettings;

        $data = [
            'slot' => $slot
        ];

        if (in_array('calendar', $this->request->get('with', []))) {
            $calendar = $slot->calendar;
            $calendar->author_profile = $calendar->getAuthorProfile();
            $data['calendar'] = $calendar;
        }

        return $data;
    }

    public function getSlotSchema(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);

        $settingsSchema = (new CalendarSlot())->getSlotSettingsSchema($calendar);

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

    public function createCalendarSlot(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);

        $slot = $request->all();

        $this->validate($slot, [
            'title'                                 => 'required',
            'duration'                              => 'required|int',
            'status'                                => 'required',
            'settings.schedule_type'                => 'required',
            'settings.weekly_schedules'             => 'required_if:settings.schedule_type,weekly_schedules',
            'event_type'                            => 'required',
            'location_settings.*.type'              => 'required',
            'location_settings.*.title'             => 'required_if:location_settings.*.type,custom',
            'location_settings.*.description'       => 'required_if:location_settings.*.type,address_organizer',
            'location_settings.*.host_phone_number' => 'required_if:location_settings.*.type,phone_organizer'
        ]);

        $availability = AvailabilityService::getDefaultSchedule($calendar->user_id);

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
            'availability_id'   => $availability->id,
            'location_type'     => sanitize_text_field(Arr::get($slot, 'location_type')),
            'location_settings' => SanitizeService::locationSettings(Arr::get($slot, 'location_settings', [])),
            'max_book_per_slot' => (int)Arr::get($slot, 'max_book_per_slot', 1),
            'is_display_spots'  => (bool)Arr::get($slot, 'is_display_spots', false),
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
            'title'                                 => 'required',
            'duration'                              => 'required|numeric',
            'location_settings.*.type'              => 'required',
            'location_settings.*.title'             => 'required_if:location_settings.*.type,in_person_organizer',
            'location_settings.*.host_phone_number' => 'required_if:location_settings.*.type,phone_organizer'
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
        $slot->location_settings = SanitizeService::locationSettings(Arr::get($data, 'location_settings', []));

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

    public function getSlotEmailNotifications(Request $request, $calendarId, $slotId)
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

    public function saveSlotEmailNotifications(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        $notifications = $request->get('notifications', []);

        $formattedNotifications = [];

        foreach ($notifications as $key => $value) {
            $formattedNotifications[$key] = [
                'title'   => sanitize_text_field($value['title']),
                'enabled' => Arr::isTrue($value, 'enabled'),
                'email'   => $this->sanitize_notification_data($value['email']),
                'is_host' => Arr::isTrue($value, 'is_host')
            ];
        }

        $slot->setNotifications($formattedNotifications);

        return [
            'message' => __('Notifications has been saved', 'fluent-booking')
        ];
    }

    public function getSlotBookingFields(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        return [
            'fields' => $slot->getBookingFields()
        ];
    }

    public function saveSlotBookingFields(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);
        $currencySign = CurrenciesHelper::getGlobalCurrencySign();

        $bookingFields = $request->get('booking_fields');

        $optionRequiredFields = ['dropdown'];

        $formattedFields = [];

        $textFields = ['type', 'name', 'label', 'placeholder'];
        $booleanFields = ['enabled', 'required', 'system_defined', 'disable_alter'];

        foreach ($bookingFields as $value) {
            if (empty($value['name'])) {
                $value['name'] = 'custom_' . sanitize_title($value['label']);
            }

            $textValues = array_map('sanitize_text_field', Arr::only($value, $textFields));
            $booleanValues = array_map(function ($valueItem) {
                return $valueItem === true || $valueItem === 'true' || $valueItem == 1;
            }, Arr::only($value, $booleanFields));

            $formattedField = array_merge($textValues, $booleanValues);

            $formattedField['index'] = (int)Arr::get($value, 'index');
            if ($value['type'] == 'payment' && $slot->type === 'paid') {
                $formattedField['payment_items'] = Arr::get($value, 'payment_items');
                $formattedField['currency_sign'] = $currencySign;
            }
            if (in_array(Arr::get($value, 'type'), $optionRequiredFields)) {
                $sanitizedOptions = array_map('sanitize_text_field', Arr::get($value, 'options'));
                $formattedField['options'] = $sanitizedOptions;
            }

            $formattedFields[] = $formattedField;
        }

        $slot->setBookingFields($formattedFields);

        return [
            'message' => __('Fields has been updated', 'fluent-booking')
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
            'message' => __('Calendar Event has been deleted', 'fluent-booking')
        ];
    }

    private function sanitize_notification_data($settings)
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
            'message' => __('Calendar Deleted Successfully!', 'fluent-booking')
        ];
    }
}
