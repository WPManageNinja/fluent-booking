import Dashboard from './Components/Dashboard.vue';
import AllCalendars from './Modules/Calendars/AllCalendars.vue';
import CreateCalendar from './Modules/Calendars/CreateNew.vue';
import SlotSettings from "./Modules/Calendars/Edit/SlotSettings.vue";
import CreateCalendarSlot from "./Modules/Calendars/Edit/CreateCalendarSlot.vue";
import AllSchedules from "./Modules/Schedules/AllSchedules.vue";
import Availabilities from "./Modules/Availability/Availabilities.vue";
import Settings from "./Modules/Settings/Settings.vue";
import ProfileSettings from "./Modules/Settings/ProfileSettings.vue";
import GeneralSettings from "./Modules/Settings/GeneralSettings";
import Integrations from "./Modules/Settings/Integrations/Integrations.vue";
import ConfigureIntegrationSettings from "./Modules/Settings/ConfigureIntegrationSettings.vue";

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
        path: '/calendars/new',
        name: 'create_calendar',
        component: CreateCalendar,
        meta: {
            active_menu: 'calendars'
        }
    },
    {
        path: '/calendars/:calendar_id/slot-settings/:slot_id',
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
        path: '/availability',
        name: 'availability',
        component: Availabilities,
        meta: {
            active_menu: 'availability'
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
                name: 'settings',
                path: '/settings',
                component: GeneralSettings,
                meta: {
                    active_menu: 'settings',
                    title: 'General'
                },
            },
            {
                name: 'profile-settings',
                path: 'profile-settings',
                component: ProfileSettings,
                meta: {
                    active_menu: 'settings',
                    title: 'Profile'
                },
            },
            {
                name: 'configure-integrations',
                path: 'configure-integrations',
                component: ConfigureIntegrationSettings,
                meta: {
                    active_menu: 'settings',
                    title: 'Configure Integrations'
                },
            }
        ]
    },
    {
        path: '/integrations',
        name: 'integrations',
        component: Integrations,
        meta: {
            active_menu: 'integrations'
        }
    }
];

