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
    TextareaControl
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
            selectedCalendars
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

    const handleCheck = (e) => {
        const ids = '';
        console.log(e);
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
                        <div className="fcal_block_inspector_widget">
                            <TextControl
                                label={__('Title')}
                                value={title}
                                onChange={(value) => setAttributes({title: value})}
                            />
                            <TextareaControl
                                label={__('Description')}
                                value={description}
                                onChange={(value) => setAttributes({description: value})}
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
                        <div className="fcal_block_inspector_widget fcal_block_calendars_select">
                            <h3>Select Calendars</h3>
                            {calendars.map(function (calender) {
                                return <CheckboxControl
                                    label={calender.title}
                                    checked={selectedCalendars.includes(calender.id+"")}
                                    onChange={(value) => {
                                        if (value === true) {
                                            if (!selectedCalendars.includes(calender.id+"")) {
                                                let oldChecked = selectedCalendars;
                                                oldChecked.push(calender.id+"")
                                                setAttributes({selectedCalendars:  [...selectedCalendars, ...oldChecked]})
                                            }
                                        }
                                        else {
                                            const index = selectedCalendars.indexOf(calender.id+"");
                                            if (index > -1) {
                                                let oldChecked =  selectedCalendars.filter(id => {
                                                    return id !== calender.id+"";
                                                })
                                                setAttributes({selectedCalendars:  [...oldChecked]})
                                            }
                                        }
                                    }}
                                />
                            })}
                            <p className="info">{__('Leave this field empty to display all calendars.')}</p>
                        </div>
                        <div className="fcal_block_inspector_widget">
                            {/*<SelectControl*/}
                            {/*    label={__('Select calendars:')}*/}
                            {/*    value={selectedCalendars} // e.g: value = [ '1', '2' ]*/}
                            {/*    onChange={(calendarIds) => {*/}
                            {/*        setAttributes({selectedCalendars: calendarIds});*/}
                            {/*    }}*/}
                            {/*    options={calendarOptions}*/}
                            {/*    __nextHasNoMarginBottom*/}
                            {/*    multiple={true}*/}
                            {/*/>*/}
                            {/*<p className="info">{__('Leave this field empty to display all calendars.')}</p>*/}
                        </div>
                        {/*<div className="fcal_block_inspector_widget">*/}
                        {/*    <Dropdown*/}
                        {/*        className="fcal_block_dropdown_wrapper"*/}
                        {/*        contentClassName="fcal_block_dropdown_container"*/}
                        {/*        popoverProps={ { placement: 'bottom-start' } }*/}
                        {/*        renderToggle={ ( { isOpen, onToggle } ) => (*/}
                        {/*            <Button*/}
                        {/*                variant="primary"*/}
                        {/*                onClick={ onToggle }*/}
                        {/*                aria-expanded={ isOpen }*/}
                        {/*            >*/}
                        {/*                Select Calendars*/}
                        {/*            </Button>*/}
                        {/*        ) }*/}
                        {/*        renderContent={ () => <div className="fcal_block_dropdown_content_wrap">*/}
                        {/*            {*/}
                        {/*                calendars.map(calendar => {*/}
                        {/*                    return <CheckboxControl*/}
                        {/*                        label={calendar.title}*/}
                        {/*                        value={calendar.id}*/}
                        {/*                        checked={ calendarChecked.includes( calendar.id ) ? true : false }*/}
                        {/*                        onChange={ handleCheck(calendar.id) }*/}
                        {/*                    />*/}
                        {/*                })*/}
                        {/*            }*/}
                        {/*        </div> }*/}
                        {/*    />*/}
                        {/*</div>*/}

                    </div>
                </PanelRow>
            </PanelBody>

            <div className="fluent-latest-posts-content-color-settings">

            </div>
        </InspectorControls>
    );
};
export default InspectorSettings;
