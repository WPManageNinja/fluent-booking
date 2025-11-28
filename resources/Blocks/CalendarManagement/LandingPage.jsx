/*eslint-disable*/
const { Fragment } = wp.element;
const { RichText } = wp.blockEditor;
const {__} = wp.i18n;

import './fcal-calendar-management-block.scss';

const calendarsVar = window.fluent_booking_block.hosts;

const calendars = Object.values(calendarsVar);

export const LandingPage = props => {
    let {
        attributes: {
            title,
            description,
            headerImage,
            calendarId,
            eventIds,
            hideInfo
        }, setAttributes
    } = props;

    let calendar = {};

    if (calendarId) {
        calendar = calendars.find(cal => cal.id === calendarId);
        const events = calendar?.events ?? [];
        const eventOrder = calendar?.event_order ?? [];
        if (events && eventOrder) {
            calendar.events = events.slice().sort((a, b) => {
                return eventOrder.indexOf(a.id) - eventOrder.indexOf(b.id);
            });
        }
    }

    return [
        <Fragment>
            <div className="fcal_calendar_management_block_wrap">
                <div className="fcal_calendar_management_block_header">
                    {
                        headerImage.url != '' ?
                        <img src={headerImage.url} alt={headerImage.title} />
                        :
                        <img src={assetsUrl+'/images/logo.svg'} alt="Logo" />
                    }
                    <RichText
                        className={title?'':'empty-text'}
                        tagName="h1"
                        value={ title }
                        allowedFormats={ [ 'core/bold', 'core/italic', 'core/link', 'core/text-color' ] }
                        onChange={ ( heading ) => setAttributes( { title: heading } ) }
                        placeholder={__('Enter title here...')}
                    />
                    <RichText
                        className={description?'':'empty-text'}
                        tagName="p"
                        value={ description }
                        allowedFormats={ [ 'core/bold', 'core/italic', 'core/link', 'core/text-color' ] }
                        onChange={ ( heading ) => setAttributes( { description: heading } ) }
                        placeholder={__('Enter description here...')}
                    />
                </div>
                { eventIds.length && calendar && calendar.events?.length ?
                    <div>
                        {!hideInfo &&
                            <div className="fcal_calendar_management_block_header">
                                <img src={calendar?.author?.avatar} alt={calendar?.title} />
                                <h4>{calendar?.title}</h4>
                            </div>
                        }
                        <div className="fcal_slots_wrap">
                            <div className="fcal_slots">
                                {calendar.events.map((event, index) => {
                                    if (eventIds == 'all' || eventIds.includes(event.id)) {
                                        return (
                                            <div className="fcal_slot" key={index}>
                                                <div className="fcal_card fcal_event_card">
                                                    <div className="fcal_slot_content">
                                                        <h2><span className="fcal_slot_color_schema" style={{ backgroundColor: event?.color_schema }}></span> {event.title}</h2>
                                                        {event?.description && 
                                                            <p className="fcal_description">{event.description}</p>
                                                        }
                                                        <span className="fcal_slot_duration" key={index}>
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                                <path d="M12.8334 7C12.8334 10.22 10.22 12.8333 7.00002 12.8333C3.78002 12.8333 1.16669 10.22 1.16669 7C1.16669 3.78 3.78002 1.16666 7.00002 1.16666C10.22 1.16666 12.8334 3.78 12.8334 7Z" stroke="#445164" strokeLinecap="round" strokeLinejoin="round"/>
                                                                <path d="M9.16418 8.855L7.35585 7.77584C7.04085 7.58917 6.78418 7.14 6.78418 6.7725V4.38084" stroke="#445164" strokeLinecap="round" strokeLinejoin="round"/>
                                                            </svg>
                                                            {event.durations.length > 1 ? __('Durations') : (event.durations[0])}
                                                        </span>
                                                        {event.loc_settings.length > 1 ? (
                                                            <Fragment>
                                                                <span className="fcal_slot_location" key={index}>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin">
                                                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z">
                                                                        </path><circle cx="12" cy="10" r="3"></circle>
                                                                    </svg> {__('Locations')}
                                                                </span>
                                                            </Fragment>
                                                        ) : (
                                                            <span dangerouslySetInnerHTML={{ __html: event.locations }} />
                                                        )}
                                                        {event.payment_html &&
                                                            <span dangerouslySetInnerHTML={{ __html: event.payment_html }} />
                                                        }
                                                    </div>
                                                    <button className="book_now">{__('Book Now')}</button>
                                                </div>
                                            </div>
                                        )
                                    }
                                })}
                            </div>
                        </div> 
                    </div> :
                    <div className="fcal_team_management_block_hosts">
                        <p>{__('Please select calendar from block settings')}</p>
                    </div>
                }

            </div>
        </Fragment>
    ]
}
