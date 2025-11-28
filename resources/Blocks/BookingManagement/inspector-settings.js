/*eslint-disable*/
const { InspectorControls } = wp.blockEditor;
const {__} = wp.i18n;
const {
    PanelBody,
    PanelRow,
    TextControl,
    SelectControl,
    CheckboxControl,
} = wp.components;

const calendarsVar = window.fluent_booking_block.hosts;
const calendars = Object.values(calendarsVar);

const InspectorSettings = props => {
    const {
        attributes: {
            calendarIds,
            period,
            perPage,
            showFilter,
            showPagination,
            noBookingsMessage
        }, setAttributes
    } = props;

    let calendarOptions = [];

    calendars.map(calendar => {
        calendarOptions.push({
            title: calendar.title,
            id: calendar.id,
        });
    });

    const isChecked = (calendar) => {
        let matched = false;
        if (calendarIds) {
            calendarIds.forEach((calendarId) => {
                if (calendarId == calendar.id) {
                    matched = true;
                }
            })
        }
        return matched;
    }

    const isCheckAll = () => {
        return calendarIds == 'all';
    }

    return (
        <InspectorControls>
            <PanelBody title="General Settings" initialOpen={true}>
                <PanelRow>
                    <div className="fcal_block_settings">
                        <div className="fcal_block_inspector_widget">
                            <ul className="accordion-list">
                                <div>
                                    <CheckboxControl
                                        className="all_calendar_checked"
                                        label={__('All Calendars')}
                                        value="all"
                                        checked={isCheckAll()}
                                        onChange={(checked) => {
                                            const updatedIds = [];
                                            if (checked) {
                                                updatedIds.push('all');
                                            }
                                            setAttributes({
                                                calendarIds: [...updatedIds]
                                            })
                                        }}
                                    />

                                    {calendarIds != 'all' ?
                                        calendarOptions.map(calendar => {
                                            return <div key={'calendar-' + calendar.id} className="fcal_booking_calendar">
                                                <CheckboxControl
                                                    label={calendar.title}
                                                    value={calendar.id}
                                                    checked={isChecked(calendar)}
                                                    onChange={(checked) => {
                                                        let updatedIds = calendarIds || [];
                                                        if (checked) {
                                                            updatedIds.push(calendar.id)
                                                        } else {
                                                            updatedIds = updatedIds.filter((clndr) => clndr != calendar.id)
                                                        }
                                                        setAttributes({
                                                            calendarIds: [...updatedIds]
                                                        })
                                                    }}
                                                />

                                            </div>
                                        }) : ''
                                    }
                                </div>
                            </ul>
                        </div>
                        <div className="fcal_block_inspector_widget">
                            <SelectControl
                                label={__('Default Period')}
                                value={period}
                                options={[
                                    { label: 'Upcoming', value: 'upcoming' },
                                    { label: 'Completed', value: 'completed' },
                                    { label: 'Cancelled', value: 'cancelled' },
                                    { label: 'Pending', value: 'pending' },
                                    { label: 'All', value: 'all' },
                                ]}
                                onChange={(value) => {
                                    setAttributes({
                                        period: value
                                    })
                                }}
                            />
                            <TextControl
                                label="Per Page"
                                type="number"
                                value={perPage}
                                onChange={(value) => {
                                    setAttributes({ perPage: value });
                                }}
                                onBlur={(e) => {
                                    let newValue = e.target.value;
                                    if (isNaN(newValue) || (newValue <= 0) || (newValue > 100)) {
                                        newValue = 10;
                                    }
                                    setAttributes({ perPage: parseInt(newValue) });
                                }}
                            />
                            <CheckboxControl
                                label={__('Show Filter')}
                                value={showFilter}
                                checked={showFilter}
                                className="fcal_checkbox_control"
                                onChange={() => {
                                    setAttributes({
                                        showFilter: !showFilter
                                    })
                                }}
                            />
                            <CheckboxControl
                                label={__('Show Pagination')}
                                value={showPagination}
                                checked={showPagination}
                                onChange={() => {
                                    setAttributes({
                                        showPagination: !showPagination
                                    })
                                }}
                            />
                            <TextControl
                                label="No Bookings Message"
                                value={noBookingsMessage}
                                onChange={(value) => {
                                    setAttributes({ noBookingsMessage: value });
                                }}
                            />
                        </div>
                    </div>
                </PanelRow>
            </PanelBody>
        </InspectorControls>
    );
};
export default InspectorSettings;
