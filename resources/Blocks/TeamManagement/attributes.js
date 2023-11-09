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
    calendars: {
        type: 'array',
        default: []
    },
    hosts: {
        type: 'object',
        default: {}
    }
};
