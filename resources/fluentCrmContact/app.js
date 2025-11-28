import ScheduledMeetigns from './ScheduledMeetigns';

const CRMApp = window.FLUENTCRM;

// add Booking route under contact route of CRM. this will actually trigger the ScheduledMeetigns component
CRMApp.addFilter('fluentcrm_profile_routes', 'fluent_booking', function (profileRoute) {
    profileRoute.children.push({
        name: 'booking',
        path: 'booking',
        component: ScheduledMeetigns,
        meta: {
            parent: 'subscribers',
            active_menu: 'contacts',
            permission: 'fcrm_read_contacts'
        }
    });
    return profileRoute;
}, 1);
