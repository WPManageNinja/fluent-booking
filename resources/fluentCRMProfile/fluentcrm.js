import ScheduledMeetings from './ScheduledMeetings';

const CrmApp = window.FLUENTCRM;
console.log("CrmApp");

CrmApp.addFilter('fluentcrm_profile_routes', 'fluent_booking', function (profileRoute) {
    profileRoute.children.push({
        name: 'fluent_booking',
        path: 'scheduled_meetings',
        component: ScheduledMeetings,
        meta: {
            parent: 'subscribers',
            active_menu: 'contacts',
            permission: 'fcrm_read_contacts'
        }
    });
    return profileRoute;
}, 1);

CrmApp.addFilter('fluentcrm_profile_sections', 'fluent_booking', function (sections) {
    sections.fluent_booking = {
        title: 'Bookings',
        name: 'fluent_booking',
        handler: 'route'
    };
    return sections;
}, 1);
