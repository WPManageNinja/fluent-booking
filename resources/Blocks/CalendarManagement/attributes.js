// eslint-disable-next-line
const { __ } = wp.i18n;

export const attributes = {
    calendarId: {
        type: 'string',
        default: ''
    },
    eventIds: {
        type: 'array',
        default: []
    },
    hideInfo: {
        type: 'boolean',
        default: false
    }
};
