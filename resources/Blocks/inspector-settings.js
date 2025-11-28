/*eslint-disable*/
const { InspectorControls, PanelColorSettings } = wp.blockEditor;
const {__} = wp.i18n;
const {
    PanelBody,
    PanelRow
} = wp.components;

const hostsVar = window.fluent_booking_block.hosts;
const calendarsVar = Object.values(hostsVar);

const InspectorSettings = props => {
    const {
        attributes: {
            slotId,
            calendarId,
            calendars,
            primary_color,
            date_round,
            avatarStyle,
            hideHostInfo,
            theme
        }, setAttributes
    } = props;

    const calendarChangeHandler = (event) => {
        const ids = event.target.value.split(",");

        setAttributes( { slotId: ids[0] } );
        setAttributes( { calendarId: ids[1] } );
    }

    const dateStyleChangeHandle = (event) => {
        setAttributes({date_round: event.target.value});
    }

    const avatarStyleChangeHandle = (event) => {
        setAttributes({avatarStyle: event.target.value});
    }

    const colorSchemaHandle = (event) => {
        setAttributes({theme: event.target.value});
    }

    const hostInfoHandle = (event) => {
        setAttributes({hideHostInfo: event.target.value});
    }


    const colorHandles = [
        {
            value: primary_color,
            onChange: (val) => {
                setAttributes({primary_color: val})
            },
            label: __('Primary Color')
        }
    ];


    return (
        <InspectorControls>
            <PanelBody title="General Settings"
                       initialOpen={true}
            >
                <PanelRow>
                    <div className="fcal_block_settings">
                        <div className="fcal_block_inspector_widget">
                            <h3 className="label">{__('Select An Event')}</h3>
                            <select
                                value={[slotId, calendarId]}
                                onChange={calendarChangeHandler}
                            >
                                <option key="default" value="">{__('---Select a Event---')}</option>
                                {calendarsVar.map((item, index) => {
                                    return <optgroup label={item.title} key={index}>
                                        {
                                            item.events.map((event, index) => {
                                                return <option key={index} value={[event.id, item.id]}>
                                                    {event.title}
                                                </option>
                                            })
                                        }
                                    </optgroup>
                                })}
                            </select>
                        </div>

                        <div className="fcal_block_inspector_widget">
                            <h3 className="label">{__('Date Style')}</h3>
                            <select
                                value={date_round}
                                onChange={dateStyleChangeHandle}
                            >
                                <option value="4px">{__('Square')}</option>
                                <option value="50%">{__('Rounded')}</option>
                            </select>
                        </div>

                        <div className="fcal_block_inspector_widget">
                            <h3 className="label">{__('Avatar Style')}</h3>
                            <select
                                value={avatarStyle}
                                onChange={avatarStyleChangeHandle}
                            >
                                <option value="8px">{__('Square')}</option>
                                <option value="50%">{__('Rounded')}</option>
                            </select>
                        </div>

                        <div className="fcal_block_inspector_widget fcal_block_inspector_host_info">
                            <h3 className="label">{__('Host Info')}</h3>
                            <select
                                value={hideHostInfo}
                                onChange={hostInfoHandle}
                            >
                                <option value="no">{__('Show')}</option>
                                <option value="yes">{__('Hide')}</option>
                            </select>
                        </div>

                        <div className="fcal_block_inspector_widget fcal_block_theme">
                            <h3 className="label">{__('Color Schema')}</h3>
                            <select
                                value={theme}
                                onChange={colorSchemaHandle}
                            >
                                <option value="system-default">{__('System Default')}</option>
                                <option value="light">{__('Light')}</option>
                                <option value="dark">{__('Dark')}</option>
                            </select>
                        </div>

                    </div>
                </PanelRow>
            </PanelBody>

            {
                theme != 'dark' ?
                <div className="fluent-latest-posts-content-color-settings">
                    <PanelColorSettings
                        title={__('Customization')}
                        colorSettings={ colorHandles }
                    />
                </div>
                : ''
            }
        </InspectorControls>
    );
};
export default InspectorSettings;
