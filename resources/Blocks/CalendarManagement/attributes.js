// eslint-disable-next-line
const { __ } = wp.i18n;

export const attributes = {
    title: {
        type: 'string',
        default: ''
    },
    description: {
        type: 'string',
        default: ''
    },
    headerImage: {
        type: 'object',
        default: {}
    },
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
