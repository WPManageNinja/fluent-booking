/*eslint-disable*/
const { Fragment } = wp.element;
const {__} = wp.i18n;

import './fcal-calendar-management-block.scss';

const calendarsVar = window.fluent_booking_block.hosts;

const calendars = Object.values(calendarsVar);

export const LandingPage = props => {
    let {
        attributes: {
            calendarId,
            eventIds,
            hideInfo
        },
    } = props;

    let calendar = {};

    if (calendarId) {
        calendar = calendars.find(cal => cal.id === calendarId);
    }

    return [
        <Fragment>
            <div className="fcal_calendar_management_block_wrap">
                { eventIds.length && calendar.events?.length ?
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
                                                        {
                                                            event?.desctiption &&
                                                                <p className="fcal_description">{event.desctiption}</p>
                                                        }
                                                        {event.duration.map((duration, index) => (
                                                            <span className="fcal_slot_duration" key={index}>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                                    <path d="M12.8334 7C12.8334 10.22 10.22 12.8333 7.00002 12.8333C3.78002 12.8333 1.16669 10.22 1.16669 7C1.16669 3.78 3.78002 1.16666 7.00002 1.16666C10.22 1.16666 12.8334 3.78 12.8334 7Z" stroke="#445164" strokeLinecap="round" strokeLinejoin="round"/>
                                                                    <path d="M9.16418 8.855L7.35585 7.77584C7.04085 7.58917 6.78418 7.14 6.78418 6.7725V4.38084" stroke="#445164" strokeLinecap="round" strokeLinejoin="round"/>
                                                                </svg>
                                                                {duration} {__('minutes')}
                                                            </span>
                                                        ))}
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
