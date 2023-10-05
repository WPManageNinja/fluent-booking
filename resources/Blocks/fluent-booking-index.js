const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

import Edit from './edit';
import Save from './save';
import { attributes } from './attributes';
// const el = wp.element.createElement;


registerBlockType('fluent-booking/calendar', {
    title: __('Fluent Booking'),
    description: __('Fluent Booking'),
    category: 'layout',
    icon: '',
    keywords: [__('fluent'), __('fluent booking'), __('calendar'), __('booking')],
    supports: {
        align: ['wide', 'full'],
        html: true
    },
    attributes,
    edit: Edit,
    save: Save
});
