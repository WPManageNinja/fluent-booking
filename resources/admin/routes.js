import Dashboard from './Components/Dashboard.vue';
import AllCalendars from './Modules/Calendars/AllCalendars.vue';
import CreateCalendar from './Modules/Calendars/CreateNew.vue';
import SlotSettings from "./Modules/Calendars/Edit/SlotSettings.vue";
import CreateCalendarSlot from "./Modules/Calendars/Edit/CreateCalendarSlot.vue";
import AllSchedules from "./Modules/Schedules/AllSchedules.vue";
import Availabilities from "./Modules/Availability/Availabilities.vue";
import AvailabilitySettings from "./Modules/Settings/AvailabilitySettings";
import Settings from "./Modules/Settings/Settings.vue";
import ProfileSettings from "./Modules/Settings/ProfileSettings.vue";
import GeneralSettings from "./Modules/Settings/GeneralSettings";
import IntegrationSettings from "./Modules/Settings/IntegrationSettings.vue";
import ConfigureIntegrationSettings from "./Modules/Settings/ConfigureIntegrationSettings.vue";
import SingleIntegration from "./Modules/Calendars/Edit/SingleIntegration.vue";
import IntegrationGoogle from "./Modules/Calendars/integrations/IntegrationGoogle";

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
        path: '/calendars/:id/single-integration',
        component: SingleIntegration,
        props: true,
        meta: {
            active_menu: 'calendars'
        },
        children: [
            {
                name: 'single-integration',
                path: '/calendars/:id/single-integration',
                component: IntegrationGoogle,
                meta: {
                    active_menu: 'calendars',
                    title: 'Booking Types'
                },
            },
            {
                name: 'google_calendar',
                path: '/calendars/:id/single-integration/:settings_key',
                component: IntegrationGoogle,
                meta: {
                    active_menu: 'calendars',
                    title: 'Booking Types'
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
                component: AvailabilitySettings,
                meta: {
                    active_menu: 'settings',
                    title: 'Availability'
                },
            },
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
                name: 'integrations',
                path: 'integrations/:settings_key',
                props: true,
                component: IntegrationSettings,
                meta: {
                    active_menu: 'settings',
                    title: 'Integrations'
                },
            }
        ]
    }
];

