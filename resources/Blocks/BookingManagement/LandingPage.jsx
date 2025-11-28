/*eslint-disable*/
const { Fragment, useEffect, useState } = wp.element;
const { RichText } = wp.blockEditor;
const {Spinner} = wp.components;
const {__} = wp.i18n;

import './fcal-booking-management-block.scss';

export const LandingPage = props => {
    let {
        attributes: {
            title,
            calendarIds,
            showFilter,
            showPagination,
            period,
            perPage,
            noBookingsMessage,
        }, setAttributes
    } = props;

    const [loading, setLoading] = useState(false);
    const [bookings, setBookings] = useState([]);
    const [total, setTotal] = useState(0);

    const apiFetch = wp.apiFetch;
    const { addQueryArgs } = wp.url;

    useEffect(() => {
        getBookings();
    }, [period, perPage, calendarIds]);

    const getBookings = () => {
        setLoading(true);
        apiFetch({
            path: addQueryArgs('fluent-booking/v2/bookings', {
                period,
                per_page: perPage,
                calendar_ids: calendarIds
            })
        })
        .then((response) => {
            setBookings(response.bookings);
            setTotal(response.total);
        })
        .catch(error => {
            console.log(error);
        })
        .finally(() => {
            setLoading(false);
        });
    };

    const radioOptions = [
        { label: 'Upcoming', value: 'upcoming' },
        { label: 'Completed', value: 'completed' },
        { label: 'Cancelled', value: 'cancelled' },
        { label: 'Pending', value: 'pending' },
        { label: 'All', value: 'all' }
    ];

    return [
        <Fragment>
            <div className="fcal_booking_management_block_wrap">
                <div className="fcal_booking_management_header">
                    <RichText
                        tagName="h2"
                        value={title}
                        onChange={title => setAttributes({ title })}
                        placeholder={__('Title')}
                    />
                    {showFilter && 
                        <div className="fcal_booking_header_action">
                            {radioOptions.map((option, index) => (
                                <div className="fcal_booking_radio_btn">
                                    <Fragment key={index}>
                                        <input
                                            type="radio"
                                            name="booking_period"
                                            id={`fcal_period_${index}`}
                                            value={option.value}
                                            checked={period === option.value}
                                            onChange={() => {}}
                                        />
                                        <label htmlFor={`fcal_period_${index}`}>{option.label}</label>
                                    </Fragment>
                                </div>
                            ))}
                        </div>
                    }
                </div>
                <div className="fcal_all_bookings">
                    <div className="fcal_bookings">
                        <div className="fcal_bookings_wrapper">
                            {loading ? 
                                <h2>
                                    {__('Loading...')}
                                    <Spinner/>
                                </h2> : (total > 0) ? 
                                    <div>
                                        {bookings.length && bookings.map((booking) => (
                                            <div className="fcal_booking" key={booking.id}>
                                                <div className={`fcal_spot_wrapper fcal_spot_status_${booking.status}`}>
                                                    <div className="fcal_spot_line">
                                                        <div className="fcal_spot_timing">
                                                            <p className="fcal_booking_date">
                                                                {booking.booking_date}
                                                            </p>
                                                            <p className="fcal_booking_time">
                                                                {booking.booking_time}
                                                            </p>
                                                            <p className="fcal_booking_timezone">
                                                                {booking.person_time_zone}
                                                            </p>
                                                        </div>
                                                        <div className="fcal_spot_desc">
                                                            <h3 className="fcal_spot_title"
                                                                dangerouslySetInnerHTML={{ __html: booking.booking_title }}>
                                                            </h3>
                                                            <div className="fcal_spot_desc_sub_info">
                                                                <span className="fcal_spot_period_status">
                                                                    {booking.status}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div className="fcal_spot_actions">
                                                            <button className="fcal_plain_btn">
                                                                {__('View')}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div> : <div className='fcal_no_bookings'>
                                        <p>{noBookingsMessage}</p>
                                    </div>
                            }
                        </div>
                    </div>
                </div>
                { showPagination &&
                    <ul className="fcal_booking_pagination">
                        <span>{__('Total')} {total}</span>
                        <select className="fcal_booking_per_page">
                            <option selected>
                                { perPage }/{__('page')}
                            </option>
                        </select>
                        <ul className="fcal_booking_pager">
                            <li class="active"><span>1</span></li>
                            <li><span>2</span></li>
                            <li><span>3</span></li>
                        </ul>
                    </ul>
                }
            </div>
        </Fragment>
    ]
}
