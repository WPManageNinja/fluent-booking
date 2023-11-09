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

const calendarsVar = window.fluent_booking_block.hosts;
const calendars = Object.values(calendarsVar);

const InspectorSettings = props => {
    const {
        attributes: {
            title,
            description,
            headerImage,
            calendarChecked,
            hosts,
            calendarHosts
        }, setAttributes
    } = props;

    const calendarOptions = [
        {
            label: 'Select Host',
            value: ''
        }
    ];

    calendars.map(calendar => {
        calendarOptions.push({
            label: calendar.title,
            value: calendar.id
        });
    });
    // const selectedHostIds = [];
    // calendarHosts.map(host => {
    //     selectedHostIds.push(host.id);
    // });
    //
    // calendarOptions.filter(filterHost => selectedHostIds.includes(filterHost.value));

    let calForHosts = calendars;
    if (calendarHosts.length) {
        let hostIds = [];
        let isAll = false;
        calendarHosts.map(host => {
            if (host.events == 'all') {
                isAll = true;
            }
            hostIds.push(host.id);
        })
        if (!isAll) {
            calForHosts = calForHosts.filter(cal => hostIds.includes(cal.id));
        }
    }


    const [addHost, setHost] = useState(false);

    const handleNewHost = (newHost) => {
        let newHostId = newHost.target.value;
        let exists = false;
        let hIds = calendarHosts;
        for (let host of calendarHosts) {

            if (host.id == newHostId) {
                exists = true;
            }

        }

        if(calendarHosts.length) {
            if (!exists) {
                hIds.push({
                    id: newHostId,
                    events: ['all']
                })
            }
        }else {
            hIds = [{
                id: newHostId,
                events: ['all']
            }]
        }

        setAttributes({
            selectedHost: newHostId
        })
        setAttributes({
            calendarHosts: hIds
        });
        setHost(false);

    }

    function handleRemoveHost(e) {
        let newResult = calendarHosts.filter(item => item.id != e.target.value);

        setAttributes({
            calendarHosts: newResult
        })
    }

    const isChecked = (event) => {
        let matched = false;
        for (let item of calendarHosts) {
            if (item.events) {
                item.events.forEach((itm) => {
                    if (itm == event.id) {
                        matched = true;
                    }
                })
            }
        }
        return matched;
    }
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
                            <Dropdown
                                className="fcal-add-host-container"
                                contentClassName="fcal-add-host-content"
                                popoverProps={ { placement: 'bottom-start' } }
                                renderToggle={ ( { isOpen, onToggle } ) => (
                                    <Button
                                        variant="primary"
                                        onClick={ onToggle }
                                        aria-expanded={ isOpen }
                                    >
                                        {__('+Add New Host')}
                                    </Button>
                                ) }
                                renderContent={ () => <div className="fcal-add-host-popover">
                                    {
                                        calendarOptions.map(host => {
                                            return <div className="fcal-host-list">
                                                {host.value ?
                                                    <button value={host.value} onClick={handleNewHost}>{host.label}</button>
                                                    :
                                                    <span className="select-host">{host.label}</span>
                                                }
                                                </div>

                                        })
                                    }

                                </div> }
                            />
                            <ul className="accordion-list">
                                {calendarHosts.length ?
                                    calForHosts.map((host, index) => {
                                        return <div>
                                            <h3>
                                                {host.title}
                                                <button className="remove-host" value={host.id} onClick={handleRemoveHost}>x</button>
                                            </h3>

                                            <CheckboxControl
                                                className="all-event-checked"
                                                label={__('All')}
                                                value="all"
                                                onChange={(checked) => {

                                                    let updatedArray = calendarHosts;
                                                    if (checked){
                                                        updatedArray = calendarHosts.map(cal => {
                                                            if (host.id == cal.id) {
                                                                cal.events.push('all')
                                                            }
                                                            return cal;
                                                        })

                                                        // if (!oldHosts.hasOwnProperty('all')){
                                                        //     oldHosts[cal.id] = [];
                                                        // }
                                                        // oldHosts[cal.id].push('all')
                                                    } else {
                                                        updatedArray = calendarHosts.map(cal => {
                                                            let item = {
                                                                ...cal,
                                                                events: cal.events.filter((it) => it != 'all')
                                                            }
                                                            return item;
                                                        })
                                                    }

                                                    console.log({updatedArray});
                                                    setAttributes({
                                                        calendarHosts: [...updatedArray]
                                                    })
                                                }}
                                            />

                                            {
                                                host?.events[0] != 'all' ?
                                                    host?.events.map(event => {
                                                        return <div key={'event-' + event.id}
                                                                    className="fcal_calendar_event">

                                                            <CheckboxControl
                                                                label={event.title}
                                                                value={event.id}
                                                                checked={isChecked(event)}
                                                                onChange={(checked) => {


                                                                    let updatedArray = calendarHosts;
                                                                    if (checked) {
                                                                        updatedArray = calendarHosts.map(cal => {
                                                                            if (host.id == cal.id) {
                                                                                cal.events.push(event.id)
                                                                            }
                                                                            return cal;
                                                                        })
                                                                    } else {
                                                                        updatedArray = calendarHosts.map(cal => {
                                                                            let item = {
                                                                                ...cal,
                                                                                events: cal.events.filter((it) => it != event.id)
                                                                            }
                                                                            return item;
                                                                        })
                                                                    }

                                                                    setAttributes({
                                                                        calendarHosts: [...updatedArray]
                                                                    })
                                                                }}
                                                            />

                                                        </div>
                                                    })
                                                : ''
                                            }
                                        </div>
                                    })
                                    : ''
                                }
                                {/*{*/}
                                {/*    calendars.map(cal => {*/}
                                {/*        return <div>*/}
                                {/*            <CheckboxControl*/}
                                {/*                label={cal.title}*/}
                                {/*                value={cal.id}*/}
                                {/*                checked={hosts.hasOwnProperty(cal.id)}*/}
                                {/*                onChange={(checked) => {*/}
                                {/*                    let oldHosts = hosts;*/}
                                {/*                    if (checked) {*/}
                                {/*                        if (!oldHosts.hasOwnProperty(cal.id)) {*/}
                                {/*                            oldHosts[cal.id] = [];*/}
                                {/*                        }*/}
                                {/*                    } else {*/}
                                {/*                        if (oldHosts.hasOwnProperty(cal.id)) {*/}
                                {/*                            delete oldHosts[cal.id]*/}
                                {/*                        }*/}
                                {/*                    }*/}

                                {/*                    setAttributes({*/}
                                {/*                        hosts: {...oldHosts}*/}
                                {/*                    })*/}
                                {/*                }}*/}
                                {/*            />*/}

                                {/*            {*/}
                                {/*                hosts.hasOwnProperty(cal.id) ?*/}
                                {/*                    <PanelBody title={__('Events')}*/}
                                {/*                               initialOpen={true}*/}
                                {/*                    >*/}
                                {/*                        <PanelRow>*/}
                                {/*                            <div className="answer">*/}
                                {/*                                <div className="fcal_calendar_events_lists">*/}

                                {/*                                    <CheckboxControl*/}
                                {/*                                        className="all-event-checked"*/}
                                {/*                                        label={__('All')}*/}
                                {/*                                        value="all"*/}
                                {/*                                        checked={hosts[cal.id]?.includes('all')}*/}
                                {/*                                        onChange={(checked) => {*/}

                                {/*                                            let oldHosts = hosts;*/}
                                {/*                                            if (checked){*/}
                                {/*                                                if (!oldHosts.hasOwnProperty('all')){*/}
                                {/*                                                    oldHosts[cal.id] = [];*/}
                                {/*                                                }*/}
                                {/*                                                oldHosts[cal.id].push('all')*/}
                                {/*                                            } else {*/}
                                {/*                                                if (oldHosts.hasOwnProperty(cal.id)){*/}
                                {/*                                                    let eventIds = oldHosts[cal.id] ;*/}
                                {/*                                                    oldHosts[cal.id] =  eventIds.filter(id => {*/}
                                {/*                                                        return id != 'all';*/}
                                {/*                                                    })*/}
                                {/*                                                }*/}
                                {/*                                            }*/}
                                {/*                                            setAttributes({*/}
                                {/*                                                hosts: {...oldHosts}*/}
                                {/*                                            })*/}
                                {/*                                        }}*/}
                                {/*                                    />*/}
                                {/*                                    {*/}
                                {/*                                        cal?.slots.map(event => {*/}
                                {/*                                            return <div key={'event-'+event.id} className="fcal_calendar_event">*/}
                                {/*                                                {*/}
                                {/*                                                    hosts[cal.id]?.includes('all') ?*/}
                                {/*                                                    ''*/}
                                {/*                                                    :*/}
                                {/*                                                    <CheckboxControl*/}
                                {/*                                                        label={event.title}*/}
                                {/*                                                        value={event.id}*/}
                                {/*                                                        checked={hosts[cal.id]?.includes(event.id)}*/}
                                {/*                                                        onChange={(checked) => {*/}

                                {/*                                                            let oldHosts = hosts;*/}
                                {/*                                                            if (checked){*/}
                                {/*                                                                if (!oldHosts.hasOwnProperty(cal.id)){*/}
                                {/*                                                                    oldHosts[cal.id] = [];*/}
                                {/*                                                                }*/}
                                {/*                                                                oldHosts[cal.id].push(event.id)*/}
                                {/*                                                            } else{*/}
                                {/*                                                                if (oldHosts.hasOwnProperty(cal.id)){*/}
                                {/*                                                                    let eventIds = oldHosts[cal.id] ;*/}
                                {/*                                                                    oldHosts[cal.id] =  eventIds.filter(id => {*/}
                                {/*                                                                        return id != event.id;*/}
                                {/*                                                                    })*/}
                                {/*                                                                }*/}
                                {/*                                                            }*/}
                                {/*                                                            setAttributes({*/}
                                {/*                                                                hosts: {...oldHosts}*/}
                                {/*                                                            })*/}
                                {/*                                                        }}*/}
                                {/*                                                    />*/}
                                {/*                                                }*/}
                                {/*                                            </div>*/}
                                {/*                                        })*/}
                                {/*                                    }*/}
                                {/*                                </div>*/}
                                {/*                            </div>*/}
                                {/*                        </PanelRow>*/}
                                {/*                    </PanelBody>*/}
                                {/*                : ''*/}
                                {/*            }*/}
                                {/*        </div>*/}
                                {/*    })*/}
                                {/*}*/}

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
