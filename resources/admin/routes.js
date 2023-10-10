import Dashboard from './Components/Dashboard.vue';
import AllCalendars from './Modules/Calendars/AllCalendars.vue';
import CreateCalendar from './Modules/Calendars/CreateNew.vue';
import SlotSettings from "./Modules/Calendars/Edit/SlotSettings.vue";
import CreateCalendarSlot from "./Modules/Calendars/Edit/CreateCalendarSlot.vue";
import AllSchedules from "./Modules/Schedules/AllSchedules.vue";
import Settings from "./Modules/Settings/Settings.vue";
import ConfigureIntegrationSettings from "./Modules/Settings/ConfigureIntegrationSettings.vue";
import CalendarSettings from "./Modules/Calendars/Edit/CalendarSettings.vue";
import RemoteCalendarsSettings from "./Modules/Calendars/integrations/RemoteCalendarsSettings";
import CalendarGeneralSettings from "./Modules/Calendars/integrations/CalendarGeneralSettings.vue";
import AvailabilityRoute from "./Modules/Availability/AvailabilityRoute.vue";
import AllAvailabilities from "./Modules/Availability/AllAvailabilities.vue";
import AvailabilityDetails from "./Modules/Availability/AvailabilityDetails.vue";
import PaymentSettingsIndex from "./Modules/Calendars/integrations/Payments/PaymentSettingsIndex.vue";

export var routes = [
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
        meta: {
            active_menu: 'dashboard'
        }
    },
    {
        path: '/calendars',
        name: 'calendars',
        component: AllCalendars,
        meta: {
            active_menu: 'calendars'
        }
    },
    {
        path: '/calendars/:id/settings',
        component: CalendarSettings,
        props: true,
        meta: {
            active_menu: 'calendars'
        },
        children: [
            {
                name: 'remote_calendars',
                path: 'remote-calendars',
                component: RemoteCalendarsSettings,
                meta: {
                    active_menu: 'calendars',
                    title: 'Booking Types'
                },
            },
            {
                name: 'calendar_settings',
                path: 'calendar-settings',
                component: CalendarGeneralSettings,
                meta: {
                    active_menu: 'calendars',
                    title: 'Calendar Settings'
                },
            }
        ]
    },
    {
        path: '/calendars/:host_id/:event_type/new',
        name: 'create_calendar',
        component: CreateCalendar,
        props: true,
        meta: {
            active_menu: 'calendars'
        }
    },
    {
        path: '/calendars/:calendar_id/slot-settings/:event_id',
        name: 'slot_settings',
        component: SlotSettings,
        props: true,
        meta: {
            active_menu: 'calendars'
        }
    },
    {
        path: '/calendars/:calendar_id/:event_type/create-event-type',
        name: 'create_slot_event',
        component: CreateCalendarSlot,
        props: true,
        meta: {
            active_menu: 'calendars'
        }
    },
    {
        path: '/scheduled-events',
        name: 'scheduled_events',
        component: AllSchedules,
        meta: {
            active_menu: 'scheduled_events'
        }
    },
    {
        path: '/settings',
        component: Settings,
        props: true,
        meta: {
            active_menu: 'settings'
        },
        children: [
            {
                name: 'configure-integrations',
                path: 'configure-integrations/:settings_key',
                props: true,
                component: ConfigureIntegrationSettings,
                meta: {
                    active_menu: 'settings',
                    title: 'Configure Integrations'
                },
            },
            {
                name: 'PaymentSettingsIndex',
                path: 'configure-integrations/payment/:settings_key',
                props: true,
                component: PaymentSettingsIndex,
                meta: {
                    active_menu: 'settings',
                    title: 'Configure Integrations'
                },
            },
        ]
    },
    {
        path: '/availability',
        component: AvailabilityRoute,
        props: true,
        meta: {
            active_menu: 'availability'
        },
        children: [
            {
                name: 'availability',
                path: '',
                component: AllAvailabilities,
            },
            {
                path: ':schedule_id',
                name: 'availability_details',
                component: AvailabilityDetails,
                props: true,
                meta: {
                    active_menu: 'availability'
                },
            }
        ]
    },
];

