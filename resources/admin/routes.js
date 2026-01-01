import DashboardWrapper from './Components/DashboardWrapper.vue';
import AllCalendars from './Modules/Calendars/AllCalendars.vue';
import CreateCalendar from './Modules/Calendars/CreateNew.vue';
import SlotSettings from "./Modules/Calendars/Edit/SlotSettings.vue";
import EventDetails from "./Modules/Calendars/Edit/_EventDetails.vue";
import AvailabilitySettings from "./Modules/Calendars/Edit/_AvailabilitySettings.vue";
import EventAvailabilitySettings from "./Modules/Calendars/Edit/_EventAvailabilitySettings.vue";
import Assignment from "./Modules/Calendars/Edit/_Assignment.vue";
import LimitSettings from "./Modules/Calendars/Edit/_LimitSettings.vue";
import EmailNotification from "./Modules/Calendars/Edit/_EmailNotificationSettings.vue";
import SMSNotification from "./Modules/Calendars/Edit/_SmsNotificationSettings.vue";
import QuestionSettings from "./Modules/Calendars/Edit/_QuestionSettings.vue";
import AdvancedSettings from "./Modules/Calendars/Edit/_AdvancedSettings.vue";
import RecurringSettings from "./Modules/Calendars/Edit/_RecurringSettings.vue";
import PaymentSettings from "./Modules/Calendars/Edit/Payments/PaymentSettings.vue";
import WebhookSettings from "./Modules/Calendars/Edit/WebHook/WebhookSettings.vue";
import Integrations from "./Modules/Calendars/Edit/GeneralIntegration/Integration.vue";
import CreateCalendarSlot from "./Modules/Calendars/Edit/CreateCalendarSlot.vue";
import AllSchedules from "./Modules/Schedules/AllSchedules.vue";

import Settings from "./Modules/Settings/Settings.vue";
import ConfigureIntegrationSettings from "./Modules/Settings/ConfigureIntegrationSettings.vue";
import GeneralSettings from "./Modules/Settings/GeneralSettings.vue";
import ZoomIntegrationSettings from "./Modules/Settings/ZoomIntegration/ZoomIntegrationSettings.vue";
import TeamManagement from "./Modules/Settings/Team/TeamManagement.vue";

import CalendarSettings from "./Modules/Calendars/Edit/CalendarSettings.vue";
import UserZoomSettings from "./Modules/Calendars/Edit/HostSettings/UserZoomSettings.vue";
import CalendarGeneralSettings from "./Modules/Calendars/Edit/HostSettings/CalendarGeneralSettings.vue";
import RemoteCalendarsSettings from "./Modules/Calendars/Edit/HostSettings/RemoteCalendarsSettings";

import AvailabilityRoute from "./Modules/Availability/AvailabilityRoute.vue";
import AllAvailabilities from "./Modules/Availability/AllAvailabilities.vue";
import AvailabilityDetails from "./Modules/Availability/AvailabilityDetails.vue";
import GlobalPaymentSettings from "./Modules/Settings/Payment/GlobalPaymentSettings.vue";
import PaymentMethodsSettings from "./Modules/Settings/Payment/PaymentMethodsSettings.vue";
import PaymentCouponsSettings from "./Modules/Settings/Payment/PaymentCouponsSettings.vue";
import GeneralIntegrationFeedSettings from "./Modules/Calendars/integrations/GeneralIntegrationFeedSettings.vue";
import GloablModules from "./Modules/Settings/GloablModules.vue";
import License from "./Modules/Settings/License.vue";

import IntegrationEditor from "./Modules/Calendars/Edit/GeneralIntegration/IntegrationEditor.vue";
import ConfigureGoogleCalendarSettings from "@/Modules/Settings/ConfigureGoogleCalendarSettings.vue";
import AddOrEditCoupon from "./Modules/Settings/Payment/_AddOrEditCoupon.vue";

export var routes = [
    {
        path: '/',
        name: 'dashboard',
        component: DashboardWrapper,
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
        path: '/calendars/:calendar_id/settings',
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
                    title: 'Remote Calendar Settings'
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
            },
            {
                name: 'user_zoom_integration',
                path: 'zoom-integration',
                component: UserZoomSettings,
                meta: {
                    active_menu: 'calendars',
                    title: 'Zoom Settings'
                },
            },
            {
                name: 'calendar_general_integration_settings',
                path: 'calendar-general-integration-settings/:settings_key',
                component: GeneralIntegrationFeedSettings,
                meta: {
                    active_menu: 'calendars',
                    title: 'Calendar Settings'
                },
                props: true
            }
        ]
    },
    {
        path: '/calendars/:host_id/:event_type/new',
        name: 'create_calendar',
        component: CreateCalendar,
        props: true,
        meta: {
            active_menu: 'calendars',
            title: 'Create Calendar'
        }
    },
    {
        path: '/calendars/:calendar_id/slot-settings/:event_id',
        component: SlotSettings,
        props: true,
        meta: {
            active_menu: 'calendars',
            title: 'Event Settings'
        },
        children: [
            {
                path: 'event-details',
                name: 'event_details',
                component: EventDetails,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'Event Details'
                },
            },
            {
                path: 'availability-settings',
                name: 'availability_settings',
                component: AvailabilitySettings,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'Availability Settings'
                },
            },
            {
                path: 'event-availability-settings',
                name: 'event_availability_settings',
                component: EventAvailabilitySettings,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'Availability Settings'
                },
            },
            {
                path: 'assignment',
                name: 'assignment',
                component: Assignment,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'Assignment'
                },
            },
            {
                path: 'limit-settings',
                name: 'limit_settings',
                component: LimitSettings,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'Limit Settings'
                },
            },
            {
                path: 'email-notification',
                name: 'email_notification',
                component: EmailNotification,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'Email Notification'
                },
            },
            {
                path: 'sms-notification',
                name: 'sms_notification',
                component: SMSNotification,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'SMS Notification'
                },
            },
            {
                path: 'question-settings',
                name: 'question_settings',
                component: QuestionSettings,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'Question Settings'
                },
            },
            {
                path: 'recurring-settings',
                name: 'recurring_settings',
                component: RecurringSettings,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'Recurring Settings'
                },
            },
            {
                path: 'advanced-settings',
                name: 'advanced_settings',
                component: AdvancedSettings,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'Advanced Settings'
                },
            },
            {
                path: 'payment-settings',
                name: 'payment_settings',
                component: PaymentSettings,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'Payment Settings'
                },
            },
            {
                path: 'webhook-settings',
                name: 'webhook_settings',
                component: WebhookSettings,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'Webhook Settings'
                },
            },
            {
                path: 'integrations',
                name: 'integrations',
                component: Integrations,
                props: true,
                meta: {
                    active_menu: 'calendars',
                    title: 'Integrations'
                },
            },
        ]
    },
    {
        path: '/calendars/:calendar_id/slot-settings/:event_id/integrations/:integration_id/:integration_name',
        name: 'edit_integration',
        component: IntegrationEditor,
        props: true,
        meta: {
            active_menu: 'calendars',
            title: 'Integrations'
        }
    },
    {
        path: '/calendars/:calendar_id/:event_type/create-event-type',
        name: 'create_slot_event',
        component: CreateCalendarSlot,
        props: true,
        meta: {
            active_menu: 'calendars',
            title: 'Create Event Type'
        }
    },
    {
        path: '/scheduled-events',
        name: 'scheduled_events',
        component: AllSchedules,
        meta: {
            active_menu: 'scheduled-events',
            title: 'Bookings'
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
                path: 'general-settings',
                name: 'general_settings',
                component: GeneralSettings,
                meta: {
                    active_menu: 'settings',
                    title: 'Settings'
                }
            },
            {
                path: 'global-payment-settings',
                name: 'global_payment_settings',
                props: true,
                component: GlobalPaymentSettings,
                meta: {
                    active_menu: 'settings',
                    title: 'Payment Settings',
                    children: true
                },
            },
            {
                path: 'payment-methods/:settings_key',
                name: 'payment_methods',
                props: true,
                component: PaymentMethodsSettings,
                meta: {
                    active_menu: 'settings',
                    title: 'Payment Methods',
                    parent: 'global_payment_settings'
                },
            },
            {
                path: 'payment-coupons',
                name: 'payment_coupons',
                props: true,
                component: PaymentCouponsSettings,
                meta: {
                    active_menu: 'settings',
                    title: 'Payment Coupons',
                    parent: 'global_payment_settings'
                },
                children: [
                    {
                        path: 'add',
                        name: 'add_payment_coupon',
                        props: true,
                        component: AddOrEditCoupon,
                        meta: {
                            active_menu: 'settings',
                            title: 'Add Payment Coupon',
                            parent: 'global_payment_settings',
                            children_of: 'payment_coupons'
                        }
                    },
                    {
                        path: ':coupon_id',
                        name: 'edit_payment_coupon',
                        props: true,
                        component: AddOrEditCoupon,
                        meta: {
                            active_menu: 'settings',
                            title: 'Edit Payment Coupon',
                            parent: 'global_payment_settings',
                            children_of: 'payment_coupons'
                        }
                    }
                ]
            },
            {
                name: 'team_members',
                path: 'team-members',
                component: TeamManagement,
                meta: {
                    active_menu: 'settings',
                    title: 'Team Management'
                }
            },
            {
                name: 'zoom_integrations',
                path: 'zoom-integrations',
                component: ZoomIntegrationSettings,
                meta: {
                    active_menu: 'settings',
                    title: 'Zoom Integrations'
                }
            },
            {
                path: 'configure-integrations/google',
                name: 'configure-google',
                component: ConfigureGoogleCalendarSettings,
                meta: {
                    active_menu: 'settings',
                    title: 'Configure Google'
                },
            },
            {
                path: 'configure-integrations/:settings_key',
                name: 'configure-integrations',
                props: true,
                component: ConfigureIntegrationSettings,
                meta: {
                    active_menu: 'settings',
                    title: 'Configure Calendar'
                },
            },
            {
                path: 'configure-integrations/global-modules',
                name: 'globalModules',
                component: GloablModules,
                meta: {
                    active_menu: 'settings',
                    title: 'Gloabl Modules'
                },
            },
            {
                path: 'license',
                name: 'license',
                props: true,
                component: License,
                meta: {
                    active_menu: 'settings',
                    title: 'License'
                },
            },
        ]
    },
    {
        path: '/availability',
        component: AvailabilityRoute,
        props: true,
        meta: {
            active_menu: 'availability',
            title: 'Availability'
        },
        children: [
            {
                path: '',
                name: 'availability',
                component: AllAvailabilities,
                meta: {
                    active_menu: 'availability',
                    title: 'Availability'
                },
            },
            {
                path: ':schedule_id',
                name: 'availability_details',
                component: AvailabilityDetails,
                props: true,
                meta: {
                    active_menu: 'availability',
                    title: 'Availability Details'
                },
            }
        ]
    },
];

