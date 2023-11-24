/*eslint-disable*/
const { InspectorControls, PanelColorSettings } = wp.blockEditor;
const {__} = wp.i18n;
const {
    PanelBody,
    PanelRow,
    SelectControl,
    RadioControl
} = wp.components;

const assets_url = window.fluent_booking_block.assets_url;

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
                            <h3 className="label">{__('Select An Slot')}</h3>
                            <select
                                value={[slotId, calendarId]}
                                onChange={calendarChangeHandler}
                            >
                                <option value="">---Select a Slot---</option>
                                {calendars.map((item, index) => {
                                    return <optgroup label={item.title} key={index}>
                                        {
                                            item.slots.map(slot => {
                                                return <option key={'slot-'+slot.id} value={[slot.id, item.id]}>
                                                    {slot.title}
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
                                <option value="4px">Square</option>
                                <option value="50%">Rounded</option>
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
                            <RadioControl
                                label={__('Host Info')}
                                help={__('You can show/hide host info')}
                                selected={ hideHostInfo }
                                options={ [
                                    { label: __('Show'), value: 'no' },
                                    { label: __('Hide'), value: 'yes' },
                                ] }
                                onChange={ ( value ) => setAttributes({hideHostInfo: value} ) }
                            />
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
