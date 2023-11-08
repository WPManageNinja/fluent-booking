/*eslint-disable*/
const {useState, useEffect} = wp.element;
const {InspectorControls, PanelColorSettings, MediaUpload} = wp.blockEditor;
const {__} = wp.i18n;
const {
    PanelBody,
    PanelRow,
    SelectControl,
    RadioControl,
    DropdownMenu,
    TextControl,
    Dropdown,
    Button,
    CheckboxControl,
    MenuGroup,
    MenuItem,
    TextareaControl,
    DropdownMenuGroup,
    DropdownMenuGroupLabel,
    DropdownMenuCheckboxItem,
    DropdownMenuSeparator
} = wp.components;

const assets_url = window.fluent_booking_block.assets_url;

const InspectorSettings = props => {
    const {
        attributes: {
            title,
            description,
            headerImage,
            calendars,
            calendarChecked,
            hosts
        }, setAttributes
    } = props;

    // const calendarChangeHandler = (event) => {
    //     const ids = event.target.value.split(",");
    //
    //     setAttributes( { slotId: ids[0] } );
    //     setAttributes( { calendarId: ids[1] } );
    // }
    //
    // const dateStyleChangeHandle = (event) => {
    //     setAttributes({date_round: event.target.value});
    // }
    //
    // const avatarStyleChangeHandle = (event) => {
    //     setAttributes({avatarStyle: event.target.value});
    // }

    const calendarOptions = [];

    calendars.map(calendar => {
        calendarOptions.push({
            label: calendar.title,
            value: calendar.id
        });
    })
    return (
        <InspectorControls>
            <PanelBody title={__('Header Settings')}
                       initialOpen={true}
            >
                <PanelRow>
                    <div className="fcal_block_settings">
                        <div className="fcal_block_inspector_widget fcal_block_media">
                            <MediaUpload
                                onSelect={(media) => {
                                    setAttributes({
                                        headerImage: {
                                            title: media.title,
                                            filename: media.filename,
                                            url: media.url,
                                        },
                                    });
                                }}
                                multiple={false}
                                render={({open}) => (
                                    <>
                                        <button onClick={open}>
                                            {
                                                headerImage.url !== '' ?
                                                    __('Change Image')
                                                    : __('Upload Image')
                                            }

                                        </button>
                                        <p className='fcal-render-image'>
                                            {headerImage === null
                                                ? ''
                                                : <img src={headerImage.url} alt={headerImage.title}/>}
                                        </p>
                                    </>
                                )}
                            />
                        </div>
                    </div>
                </PanelRow>
            </PanelBody>

            <PanelBody title="General Settings"
                       initialOpen={true}
            >
                <PanelRow>
                    <div className="fcal_block_settings">

                        <div className="fcal_block_inspector_widget fcal_inspector_host_select">
                            <h3>{__('Select Hosts')}</h3>
                            <ul className="accordion-list">
                                {
                                    calendars.map(cal => {
                                        return <div>
                                            <CheckboxControl
                                                label={cal.title}
                                                value={cal.id}
                                                checked={hosts.hasOwnProperty(cal.id)}
                                                onChange={(checked) => {
                                                    let oldHosts = hosts;
                                                    if (checked) {
                                                        if (!oldHosts.hasOwnProperty(cal.id)) {
                                                            oldHosts[cal.id] = [];
                                                        }
                                                    } else {
                                                        if (oldHosts.hasOwnProperty(cal.id)) {
                                                            delete oldHosts[cal.id]
                                                        }
                                                    }

                                                    setAttributes({
                                                        hosts: {...oldHosts}
                                                    })
                                                }}
                                            />

                                            {
                                                hosts.hasOwnProperty(cal.id) ?
                                                    <PanelBody title={__('Events')}
                                                               initialOpen={true}
                                                    >
                                                        <PanelRow>
                                                            <div className="answer">
                                                                <div className="fcal_calendar_events_lists">

                                                                    {/*<CheckboxControl*/}
                                                                    {/*    label="All"*/}
                                                                    {/*    value="all"*/}
                                                                    {/*    checked={hosts[cal.id]?.includes('all')}*/}
                                                                    {/*    onChange={(checked) => {*/}

                                                                    {/*        let oldHosts = hosts;*/}
                                                                    {/*        if (checked){*/}
                                                                    {/*            if (!oldHosts.hasOwnProperty('all')){*/}
                                                                    {/*                oldHosts[cal.id] = [];*/}
                                                                    {/*            }*/}
                                                                    {/*            oldHosts[cal.id].push('all')*/}
                                                                    {/*        } else {*/}
                                                                    {/*            if (oldHosts.hasOwnProperty('all')){*/}
                                                                    {/*                oldHosts[cal.id].push('')*/}
                                                                    {/*            }*/}
                                                                    {/*        }*/}
                                                                    {/*        setAttributes({*/}
                                                                    {/*            hosts: {...oldHosts}*/}
                                                                    {/*        })*/}
                                                                    {/*    }}*/}
                                                                    {/*/>*/}
                                                                    {
                                                                        cal?.slots.map(event => {
                                                                            return <div key={'event-'+event.id} className="fcal_calendar_event">
                                                                                <CheckboxControl
                                                                                    label={event.title}
                                                                                    value={event.id}
                                                                                    checked={hosts[cal.id]?.includes(event.id)}
                                                                                    onChange={(checked) => {

                                                                                        let oldHosts = hosts;
                                                                                        if (checked){
                                                                                            if (!oldHosts.hasOwnProperty(cal.id)){
                                                                                                oldHosts[cal.id] = [];
                                                                                            }
                                                                                            oldHosts[cal.id].push(event.id)
                                                                                        } else{
                                                                                            if (oldHosts.hasOwnProperty(cal.id)){
                                                                                                let eventIds = oldHosts[cal.id] ;
                                                                                                oldHosts[cal.id] =  eventIds.filter(id => {
                                                                                                    return id != event.id;
                                                                                                })
                                                                                            }
                                                                                        }
                                                                                        setAttributes({
                                                                                            hosts: {...oldHosts}
                                                                                        })
                                                                                    }}
                                                                                />
                                                                            </div>
                                                                        })
                                                                    }
                                                                </div>
                                                            </div>
                                                        </PanelRow>
                                                    </PanelBody>
                                                : ''
                                            }
                                        </div>
                                    })
                                }

                            </ul>
                        </div>
                    </div>
                </PanelRow>
            </PanelBody>

            <div className="fluent-latest-posts-content-color-settings">

            </div>
        </InspectorControls>
    );
};
export default InspectorSettings;
