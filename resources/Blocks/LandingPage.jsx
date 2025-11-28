/*eslint-disable*/
const {Fragment, useEffect, useState} = wp.element;
const {__} = wp.i18n;
const {
    Spinner
} = wp.components;
import { DatePicker } from './Components/DatePicker.jsx';

import './fluent-booking-block.scss';

export const LandingPage = props => {
    const {
        attributes: {
            slotId,
            calendars,
            calendarId,
            eventHash,
            avatarStyle,
            hideHostInfo,
            theme
        }, setAttributes,
    } = props;

    const [loading, setLoading] = useState(false);
    const [event, setEvent] = useState([]);
    const [error, setError] = useState(false);

    const apiFetch = wp.apiFetch;
    const {addQueryArgs} = wp.url;
    const [allCalendars, setAllCalendars] = useState([]);

    useEffect(() => {
        getCalendars();
    }, []);

    useEffect(() => {
        if (allCalendars.length) {
            if (slotId) {
                updateEventHash();
                getCalendarEvent();
            } else if (eventHash) {
                updateSlotId();
            }
        }
    }, [slotId, allCalendars]);

    const getCalendars = (queryArgs) => {
        setLoading(true);
        apiFetch({
            path: addQueryArgs('fluent-booking/v2/calendars', {
                ...queryArgs
            })
        })
        .then((response) => {
            setAllCalendars(response.calendars.data);
        })
        .catch(error => {
            setError(error);
        })
        .finally(() => {
            setLoading(false);
        });
    };

    const getCalendarEvent = (queryArgs) => {
        setLoading(true);
        apiFetch({
            path: addQueryArgs(`fluent-booking/v2/events/${slotId}`, {
                ...queryArgs,
                event_hash: eventHash
            })
        })
            .then((response) => {
                setEvent(response.calendar_event);
                setAttributes({slotId: response.calendar_event?.id});
            })
            .catch(error => {
                setError(error);
            })
            .finally(() => {
                setLoading(false);
            });
    }

    const updateEventHash = () => {
        const selectedSlot = allCalendars
            .flatMap(cal => cal.slots?.length ? cal.slots : [])
            .find(slot => slot.id == slotId);
        if (selectedSlot) {
            setAttributes({ eventHash: selectedSlot.hash });
        }
    }

    const updateSlotId = () => {
        const selectedSlot = allCalendars
            .flatMap(cal => cal.slots?.length ? cal.slots : [])
            .find(slot => slot.hash == eventHash);
        if (selectedSlot) {
            setAttributes({ slotId: selectedSlot.id });
        }
    }

    const handleCalendar = (e) => {
        const ids = e.target.value.split(",");
        const selectedSlot = allCalendars.find(cal => cal.id == ids[1])?.slots.find(slot => slot.id == ids[0]);
        setAttributes({ slotId: ids[0], calendarId: ids[1], eventHash: selectedSlot.hash });
    }

    return [
        <Fragment>
            <div className={slotId ? 'fcal_block_landing_page fcal_block_landing_preview' : 'fcal_block_landing_page '}>
                {
                    slotId && event ?
                        <div className={theme +' fcal_block_preview_wrap'}>
                            {
                                hideHostInfo == 'no' ?
                                    <div className="fcal_block_preview_aside">
                                        <div className="fcal_author">
                                            <div className="fcal_author_avatar">
                                                <img style={{borderRadius: avatarStyle?avatarStyle:'8px'}} src={event.calendar?.author_profile?.avatar} alt={event.calendar?.title} />
                                            </div>
                                            <h3 className="fcal_author_name">{event.calendar?.title}</h3>
                                        </div>

                                        <div className="fcal_slot_info">
                                            <h2 className="fcal_slot_heading">{event.title}</h2>
                                            <div className="slot_timing fcal_icon_item">
                                                <svg fill="#000000" width="16px" height="16px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#000000"><g strokeWidth="0"></g><g strokeLinecap="round" strokeLinejoin="round"></g><g><path d="M12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm5,11H12a1,1,0,0,1-1-1V6a1,1,0,0,1,2,0v5h4a1,1,0,0,1,0,2Z"></path></g></svg>
                                                <span>{event.duration} {__('Minutes')}</span>
                                            </div>
                                            {
                                                event.location_settings?.type == 'in_person_organizer' ?
                                                    <div className="slot_location fcal_icon_item">
                                                        <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" data-testid="location-marker-icon" data-id="details-item-icon"><title>Physical location</title><path d="M12 0C7.453 0 3.623 3.853 3.623 8.429c0 6.502 7.18 14.931 7.42 15.172.479.482 1.197.482 1.675.24l.24-.24c.239-.24 7.419-8.67 7.419-15.172C20.377 3.853 16.547 0 12 0zm0 11.56c-1.675 0-2.872-1.445-2.872-2.89S10.566 5.78 12 5.78c1.436 0 2.872 1.445 2.872 2.89S13.675 11.56 12 11.56z" fill="currentColor"></path></svg>
                                                        <span>{event.location_settings?.title}</span>
                                                    </div>
                                                    :
                                                    null
                                            }
                                            {
                                                event.location_settings?.type == 'phone_organizer' ?
                                                    <div className="slot_location fcal_icon_item">
                                                        <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" data-testid="location-marker-icon" data-id="details-item-icon"><title>Physical location</title><path d="M12 0C7.453 0 3.623 3.853 3.623 8.429c0 6.502 7.18 14.931 7.42 15.172.479.482 1.197.482 1.675.24l.24-.24c.239-.24 7.419-8.67 7.419-15.172C20.377 3.853 16.547 0 12 0zm0 11.56c-1.675 0-2.872-1.445-2.872-2.89S10.566 5.78 12 5.78c1.436 0 2.872 1.445 2.872 2.89S13.675 11.56 12 11.56z" fill="currentColor"></path></svg>
                                                        <span>{__('Phone Call')}</span>
                                                    </div>
                                                    :
                                                    null
                                            }
                                        </div>
                                        <div className="fcal_slot_description">
                                            <div dangerouslySetInnerHTML={{ __html: event.description }}></div>
                                        </div>
                                    </div>
                                :
                                null
                            }
                            <div className="fcal_block_preview_date_wrapper">
                                <DatePicker
                                    attributes={props.attributes}
                                    setAttributes={props.setAttributes} />
                            </div>
                        </div>
                    :
                    <div className="fcal_block_select_cal">
                        {
                            loading ?
                                <h2 className="fcal_block_loading">
                                    {__('Loading...')}
                                    <Spinner/>
                                </h2>
                                :
                            <div>
                                {
                                    allCalendars && allCalendars.length && !error ?
                                        <select
                                            value={[slotId, calendarId]}
                                            id="fcal_select_calendar"
                                            onChange={handleCalendar}
                                        >
                                            <option key="default" value="">{__('---Select a Event---')}</option>
                                            {allCalendars.map((item, index) => {
                                                return <optgroup label={item.title} key={index}>
                                                    {
                                                        item.slots.map((slot, index) => {
                                                            return <option key={index} value={[slot.id,item.id]}>
                                                                {slot.title}
                                                            </option>
                                                        })
                                                    }
                                                </optgroup>
                                            })}
                                        </select>
                                        :
                                        <div className="fcal_calendar_not_found">
                                            <h2>{error ? __('Something went wrong!') : __('No Calendars found!')}</h2>
                                        </div>
                                }

                            </div>
                        }
                    </div>
                }

            </div>
        </Fragment>
    ]
}
