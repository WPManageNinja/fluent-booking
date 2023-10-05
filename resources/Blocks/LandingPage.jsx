/*eslint-disable*/
const {Fragment, useEffect, useState} = wp.element;
const {
    Spinner
} = wp.components;

import './fluent-booking-block.scss';

const assetsUrl = window.fluent_booking_block.assets_url;

export const LandingPage = props => {
    const {
        attributes: {
            slotId,
            calendars,
            calendarId
        }, setAttributes,
    } = props;

    const [isLoading, setIsLoading] = useState(false);
    const [calendar, setCalendar] = useState('');
    const [error, setError] = useState(false);


    const apiFetch = wp.apiFetch;
    const {addQueryArgs} = wp.url;

    useEffect(() => {
        getCalendars();
        getCalendar();
    }, [ slotId, calendarId ] );

    const getCalendars = (queryArgs) => {
        setIsLoading(true);
        apiFetch({
            path: addQueryArgs('fluent-booking/v2/calendars', {
                ...queryArgs
            })
        })
        .then((response) => {
            setAttributes( { calendars: response.calendars.data } );
        })
        .catch(error => {
            setError(error);
        })
        .finally(() => {
            setIsLoading(false);
        });
    };

    const getCalendar = (queryArgs) => {
        if (!slotId) {
            return false;
        }
        setIsLoading(true);
        apiFetch({
            path: addQueryArgs('fluent-booking/v2/calendars/'+calendarId+'/slots/'+slotId, {
                ...queryArgs
            })
        })
            .then((response) => {
                setCalendar(response.slot);
            })
            .catch(error => {
                setError(error);
            })
            .finally(() => {
                setIsLoading(false);
            });
    }


    const handleCalendar = (event) => {
        const ids = event.target.value.split(",");

        setAttributes( { slotId: ids[0] } );
        setAttributes( { calendarId: ids[1] } );
    }

    return [
        <Fragment>
            <div className={slotId ? 'fcal_block_landing_page fcal_block_landing_preview' : 'fcal_block_landing_page'}>
                <div className="fcal_block_header">
                    <img className="fcal_block_logo" src={assetsUrl+'Blocks/images/logo.svg'} alt="FluentBooking" />
                </div>

                {
                    slotId && calendar ?
                        <div className="fcal_block_preview_wrap">
                            <div className="fcal_block_preview_aside">
                                <div className="fcal_author">
                                    <div className="fcal_author_avatar">
                                        <img src={calendar.author_profile?.avatar} alt={calendar.author_profile?.name} />
                                    </div>
                                    <h3 className="fcal_author_name">{calendar.author_profile?.name}</h3>
                                </div>

                                <div className="fcal_slot_info">
                                    <h2 className="fcal_slot_heading">{calendar.title}</h2>
                                    <div className="slot_timing fcal_icon_item">
                                        <svg fill="#000000" width="16px" height="16px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#000000"><g strokeWidth="0"></g><g strokeLinecap="round" strokeLinejoin="round"></g><g><path d="M12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm5,11H12a1,1,0,0,1-1-1V6a1,1,0,0,1,2,0v5h4a1,1,0,0,1,0,2Z"></path></g></svg>
                                        <span>{calendar.duration} minutes</span>
                                    </div>
                                    {
                                        calendar.location_type == 'in_person' ?
                                            <div className="slot_location fcal_icon_item">
                                                <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" data-testid="location-marker-icon" data-id="details-item-icon"><title>Physical location</title><path d="M12 0C7.453 0 3.623 3.853 3.623 8.429c0 6.502 7.18 14.931 7.42 15.172.479.482 1.197.482 1.675.24l.24-.24c.239-.24 7.419-8.67 7.419-15.172C20.377 3.853 16.547 0 12 0zm0 11.56c-1.675 0-2.872-1.445-2.872-2.89S10.566 5.78 12 5.78c1.436 0 2.872 1.445 2.872 2.89S13.675 11.56 12 11.56z" fill="currentColor"></path></svg>
                                                <span>{calendar.location_heading}</span>
                                            </div>
                                            :
                                            null
                                    }
                                    {
                                        calendar.location_type == 'phone' ?
                                            <div className="slot_location fcal_icon_item">
                                                <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" data-testid="location-marker-icon" data-id="details-item-icon"><title>Physical location</title><path d="M12 0C7.453 0 3.623 3.853 3.623 8.429c0 6.502 7.18 14.931 7.42 15.172.479.482 1.197.482 1.675.24l.24-.24c.239-.24 7.419-8.67 7.419-15.172C20.377 3.853 16.547 0 12 0zm0 11.56c-1.675 0-2.872-1.445-2.872-2.89S10.566 5.78 12 5.78c1.436 0 2.872 1.445 2.872 2.89S13.675 11.56 12 11.56z" fill="currentColor"></path>
                                                </svg>
                                                <span>Phone Call</span>
                                            </div>
                                            :
                                        null
                                    }
                                </div>
                                <div className="fcal_slot_description">
                                    <p>{calendar.description}</p>
                                </div>
                            </div>
                            <div className="fcal_block_preview_date_wrapper">
                                <img src={assetsUrl+'Blocks/images/date-picker.png'} alt="FluentBooking" />
                            </div>

                        </div>
                    :
                    <div className="fcal_block_select_cal">
                        {
                            isLoading ?
                                <h2 className="fcal_block_loading">
                                    Loading ...
                                    <Spinner/>
                                </h2>
                                :
                            <div>
                                {
                                    calendars && calendars.length && !error ?
                                        <select
                                            value={[slotId, calendarId]}
                                            id="fcal_select_calendar"
                                            onChange={handleCalendar}
                                            multiple={false}
                                        >
                                            <option value="">---Select a Slot---</option>
                                            {calendars.map((item, index) => {
                                                return <optgroup label={item.title} key={index}>
                                                    {
                                                        item.slots.map(slot => {
                                                            return <option key={'slot-'+slot.id} value={[slot.id,item.id]}>
                                                                {slot.title}
                                                            </option>
                                                        })
                                                    }
                                                </optgroup>
                                            })}
                                        </select>
                                        :
                                        <div className="fcal_calendar_not_found">
                                            <h2>{error ? 'Something went wrong!' : 'No Calendars found!'}</h2>
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
