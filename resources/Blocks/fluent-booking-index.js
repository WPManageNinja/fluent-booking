const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

import Edit from './edit';
import Save from './save';
import { attributes } from './attributes';
import CalendarIcon from './icons/CalendarIcon';

registerBlockType('fluent-booking/calendar', {
    title: __('FluentBooking Event'),
    description: __('FluentBooking Event'),
    category: 'widgets',
    icon: CalendarIcon,
    keywords: [__('fluent'), __('fluent booking'), __('calendar'), __('booking')],
    supports: {
        align: ['wide', 'full'],
        html: true
    },
    attributes,
    edit: Edit,
    save: Save
});
