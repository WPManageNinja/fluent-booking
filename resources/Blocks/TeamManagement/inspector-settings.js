/*eslint-disable*/
const {useState, useEffect} = wp.element;
const {InspectorControls, MediaUpload} = wp.blockEditor;
const {__} = wp.i18n;
const {
    PanelBody,
    PanelRow,
    Dropdown,
    Button,
    CheckboxControl,
} = wp.components;

const calendarsVar = window.fluent_booking_block.hosts;
const calendars = Object.values(calendarsVar);

const InspectorSettings = props => {
    const {
        attributes: {
            headerImage,
            calendarHosts
        }, setAttributes
    } = props;

    let calendarOptions = [
        {
            label: __('Select Host'),
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

    if (calendarHosts.length) {
        let hostIds = [];
        calendarHosts.map(calHost => {
            hostIds.push(calHost.id);
            calendarOptions = calendarOptions.filter(host => host.value != calHost.id);
        })
    }


    const handleNewHost = (newHost) => {
        let newHostId = newHost.target.value;
        let exists = false;
        let hIds = calendarHosts;
        for (let host of calendarHosts) {

            if (host.id == newHostId) {
                exists = true;
            }

        }
        if (calendarHosts.length) {
            if (!exists) {
                hIds.push({
                    id: newHostId,
                    events: ['all']
                })
            }
        } else {
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

    }

    function handleRemoveHost(e) {
        for (let i = 0; i < calendarHosts.length; i++) {
            let calHost = calendarHosts[i];
            if (calHost.id == e.target.value) {
                let updatedArray = calendarHosts;
                updatedArray.splice(i, 1)
                setAttributes({
                    calendarHosts: [...updatedArray]
                })
                break;
            }
        }
    }

    const isChecked = (event, host, index) => {
        let matched = false;
        if (event != 'all') {
            for (let item of calendarHosts) {
                if (item.events) {
                    item.events.forEach((itm) => {
                        if (itm == event.id) {
                            matched = true;
                        }
                    })
                }
            }
        }
        return matched;
    }

    const isCheckAll = (index, currentHostId, event) => {

        for (let i = 0; i < calendarHosts.length; i++) {
            let calHost = calendarHosts[i];
            if (calHost.id == currentHostId) {

                return calHost.events == 'all';
                break;
            }
        }
        return false;
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
                                popoverProps={{placement: 'bottom-start'}}
                                renderToggle={({isOpen, onToggle}) => (
                                    <Button
                                        variant="primary"
                                        onClick={onToggle}
                                        aria-expanded={isOpen}
                                    >
                                        {__('+Add New Host')}
                                    </Button>
                                )}
                                renderContent={() => <div className="fcal-add-host-popover">
                                    {
                                        calendars.length ?
                                            calendarOptions.map(host => {
                                                return <div className="fcal-host-list">
                                                    {
                                                        calendarOptions.length && calendarOptions.length > 1 ?
                                                            host.value ?
                                                                <button value={host.value}
                                                                        onClick={handleNewHost}>
                                                                    <img src={host?.author?.avatar} alt={host.label} />
                                                                    {host.label}</button>
                                                                : <span className="select-host">{host.label}</span>
                                                            :
                                                            <div>
                                                                <span className="select-host">{host.label}</span>
                                                                <p><b>{__('You have added all hosts!')}</b></p>
                                                            </div>
                                                    }
                                                </div>
                                            })
                                        : <span className="select-host">{__('No Hosts Found!')}</span>
                                    }

                                </div>}
                            />
                            <ul className="accordion-list">
                                {calendarHosts.length ?
                                    calendarHosts.map((host, index) => {
                                        return <div key={index}>
                                            <h3>
                                                <img src={calendarsVar[host.id]?.author?.avatar} alt={calendarsVar[host.id].title} />
                                                {calendarsVar[host.id].title}
                                                <button className="remove-host" value={calendarsVar[host.id].id} onClick={handleRemoveHost}>+
                                                </button>
                                            </h3>

                                            <CheckboxControl
                                                className="all-event-checked"
                                                label={__('All')}
                                                value="all"
                                                checked={isCheckAll(index, calendarsVar[host.id].id, 'all')}
                                                onChange={(checked) => {

                                                    let updatedArray = calendarHosts;
                                                    let updateableIndex = null;

                                                    for (let i = 0; i < calendarHosts.length; i++) {
                                                        let calHost = calendarHosts[i];
                                                        if (calHost.id == calendarsVar[host.id].id) {
                                                            updateableIndex = i;
                                                            break;
                                                        }
                                                    }

                                                    if (updateableIndex != null) {
                                                        if (checked) {
                                                            updatedArray[updateableIndex].events = ['all'];

                                                        } else {
                                                            updatedArray[updateableIndex].events = [];
                                                        }

                                                        setAttributes({
                                                            calendarHosts: [...updatedArray]
                                                        })
                                                    }
                                                }}
                                            />

                                            {
                                                host.events != 'all' ?
                                                    calendarsVar[host.id]?.events.map(event => {
                                                        return <div key={'event-' + event.id}
                                                                    className="fcal_calendar_event">

                                                            <CheckboxControl
                                                                label={event.title}
                                                                value={event.id}
                                                                checked={isChecked(event, host, index)}
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
