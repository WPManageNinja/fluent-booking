// eslint-disable-next-line
const { __ } = wp.i18n;

export const attributes = {
    title: {
        type: 'string',
        default: 'My Bookings'
    },
    showFilter: {
        type: 'boolean',
        default: true
    },
    showPagination: {
        type: 'boolean',
        default: true
    },
    period: {
        type: 'string',
        default: 'all'
    },
    perPage: {
        type: 'number',
        default: 5
    },
    noBookingsMessage: {
        type: 'string',
        default: 'No bookings found'
    },
    calendarIds: {
        type: 'array',
        default: ['all']
    }
};
