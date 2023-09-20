import ContactSection from './ContactSection';

const CRMApp = window.FLUENTCRM;

// add Booking route under contact route of CRM. this will actually trigger the ContactSection component
CRMApp.addFilter('fluentcrm_profile_routes', 'fluent_pipeline', function (profileRoute) {
    profileRoute.children.push({
        name: 'booking',
        path: 'booking',
        component: ContactSection,
        meta: {
            parent: 'subscribers',
            active_menu: 'contacts',
            permission: 'fcrm_read_contacts'
        }
    });
    return profileRoute;
}, 1);

// Add Booking section Menu under contact section of CRM
CRMApp.addFilter('fluentcrm_profile_sections', 'fluent_booking', function (sections) {
    sections.booking = {
        title: 'Bookings',
        name: 'booking', // name of the route which will be used to navigate
        handler: 'route'
    };
    return sections;
}, 1);
