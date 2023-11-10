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
    calendarHosts: {
        type: 'array',
        default: []
    },
};
