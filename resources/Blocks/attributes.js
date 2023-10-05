// eslint-disable-next-line
const { __ } = wp.i18n;

export const attributes = {
    slotId: {
        type: 'string',
        default: ''
    },
    calendarId: {
        type: 'string',
        default: ''
    },
    calendars: {
        type: 'array',
        default: []
    }
};
