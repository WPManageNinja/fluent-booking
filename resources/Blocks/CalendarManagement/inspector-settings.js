/*eslint-disable*/
const { InspectorControls } = wp.blockEditor;
const {__} = wp.i18n;
const {
    PanelBody,
    PanelRow,
    DropdownMenu,
    CheckboxControl,
} = wp.components;

const calendarsVar = window.fluent_booking_block.hosts;
const calendars = Object.values(calendarsVar);

const InspectorSettings = props => {
    const {
        attributes: {
            calendarId,
            eventIds,
        }, setAttributes
    } = props;

    let calendarOptions = [
        {
            label: __('Select Calendar'),
            value: ''
        }
    ];

    calendars.map(calendar => {
        calendarOptions.push({
            label: calendar.title,
            value: calendar.id,
            author: calendar?.author
        });
    });

    let calendar = {};

    if (calendarId) {
        calendar = calendars.find(cal => cal.id === calendarId);
        calendarOptions = calendarOptions.map(calendar => ({
            ...calendar,
            selected: calendar.value === calendarId
        }));
    }

    const selectCalendarId = (selectedId, callback) => {
        calendar = calendars.find(cal => cal.id === selectedId);
        setAttributes({
            calendarId: selectedId
        });
        callback();
    }

    function removeCalendar() {
        calendar = {};
        setAttributes({ 
            calendarId: '' 
        });
    }

    const isChecked = (event) => {
        let matched = false;
        if (eventIds) {
            eventIds.forEach((evnt) => {
                if (evnt == event.id) {
                    matched = true;
                }
            })
        }
        return matched;
    }

    const isCheckAll = () => {
        return eventIds == 'all';
    }

    return (
        <InspectorControls>
            <PanelBody title="General Settings" initialOpen={true}>
                <PanelRow>
                    <div className="fcal_block_settings">
                        <div className="fcal_block_inspector_widget fcal_inspector_host_select">
                            <DropdownMenu
                                className="fcal-add-calendar-container"
                                icon={null}
                                text={calendarId == '' ? __('-- Select a Calendar --') : calendar.title}
                                label={calendarId == '' ? __('-- Select a Calendar --') : calendar.title}>
                                { ( { onClose } ) => (
                                    <div className='fcal-add-calendar-content'>
                                        {
                                            calendars.length ?
                                                calendarOptions.map((calendar, index) => {
                                                    return <div key={index} className="fcal-host-list">
                                                        {
                                                            calendar.value ?
                                                                <button value={calendar.value} onClick={() => { selectCalendarId(calendar.value, onClose); }}>
                                                                    <img src={calendar?.author?.avatar} alt={calendar.label} />
                                                                    <p className={`${calendar.selected ? 'selected' : ''}`}>{calendar.label}</p>
                                                                </button>
                                                            : <span className="select-host">{calendar.label}</span>
                                                        }
                                                    </div>
                                                })
                                            : <span className="select-host">{__('No Hosts Found!')}</span>
                                        }
                                    </div>
                                ) }
                            </DropdownMenu>
                            <ul className="accordion-list">
                                {calendarId && calendar ?
                                    <div>
                                        <h3>
                                            <img src={calendar.author?.avatar} alt={calendar.title} />
                                            {calendar.title}
                                            <button className="remove-host" value={calendar.id} onClick={removeCalendar}>+</button>
                                        </h3>

                                        <CheckboxControl
                                            className="all-event-checked"
                                            label={__('All Events')}
                                            value="all"
                                            checked={isCheckAll()}
                                            onChange={(checked) => {
                                                const updatedIds = [];
                                                if (checked) {
                                                    updatedIds.push('all');
                                                }
                                                setAttributes({
                                                    eventIds: [...updatedIds]
                                                })
                                            }}
                                        />

                                        {
                                            eventIds != 'all' ?
                                                calendar?.events.map(event => {
                                                    return <div key={'event-' + event.id} className="fcal_calendar_event">
                                                        <CheckboxControl
                                                            label={event.title}
                                                            value={event.id}
                                                            checked={isChecked(event)}
                                                            onChange={(checked) => {
                                                                let updatedIds = eventIds || [];
                                                                if (checked) {
                                                                    updatedIds.push(event.id)
                                                                } else {
                                                                    updatedIds = updatedIds.filter((evnt) => evnt != event.id)
                                                                }
                                                                setAttributes({
                                                                    eventIds: [...updatedIds]
                                                                })
                                                            }}
                                                        />

                                                    </div>
                                                })
                                                : ''
                                        }
                                    </div> : ''
                                }
                            </ul>
                        </div>
                    </div>
                </PanelRow>
            </PanelBody>
        </InspectorControls>
    );
};
export default InspectorSettings;
