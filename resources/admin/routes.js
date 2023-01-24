import Dashboard from './Components/Dashboard.vue';
import AllCalendars from './Modules/Calendars/AllCalendars.vue';
import CreateCalendar from './Modules/Calendars/CreateNew.vue';
import SlotSettings from "./Modules/Calendars/Edit/SlotSettings.vue";
import CreateCalendarSlot from "./Modules/Calendars/Edit/CreateCalendarSlot.vue";
import AllSchedules from "./Modules/Schedules/AllSchedules.vue";

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
        path: '/calendars/:calendar_id/create-event-type',
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
    }
];

