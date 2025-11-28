/*eslint-disable*/
const {__} = wp.i18n;

const {Fragment, useEffect, useState} = wp.element;

export const DatePicker = props => {
    const {
        attributes: {
            slotId
        }, setAttributes,
    } = props;

    let monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    let headers = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"];
    let now     = new Date();
    let year    = now.getFullYear();//	this is the month & year displayed
    let month   = now.getMonth();

    const [days, setDays] = useState([]);
    const [availableDates, setAvailableDates] = useState({});
    const [timezone, setTimezone] = useState('');

    const initMonth = () => {
        const currentDate = new Date();
        const year        = currentDate.getFullYear();
        const month       = currentDate.getMonth();

        const daysArray       = [];
        const firstDay        = new Date(year, month, 1).getDay();
        const daysInThisMonth = new Date(year, month + 1, 0).getDate();
        const daysInLastMonth = new Date(year, month, 0).getDate();
        const prevMonth       = month === 0 ? 11 : month - 1;

        for (let i = daysInLastMonth - firstDay; i < daysInLastMonth; i++) {
            let d = new Date(prevMonth === 11 ? year - 1 : year, prevMonth, i + 1);
            daysArray.push({ name: '', enabled: false, date: d });
        }

        for (let i = 0; i < daysInThisMonth; i++) {
            let d = new Date(year, month, i + 1);
            const date = d.toLocaleDateString('en-CA', { year: 'numeric', month: '2-digit', day: '2-digit' });
            // Replace the 'YYYY-MM-DD' with the actual format you need
            const enabled = !!availableDates[date];
            daysArray.push({ name: `${i + 1}`, enabled: enabled, date: date });
        }

        setDays(daysArray);
    };

    const loadAvailableDates = () => {
        const year  = new Date().getFullYear();
        const month = new Date().getMonth();

        const requestData = {
            event_id: slotId,
            action: 'fluent_cal_get_available_dates',
            start_date: `${year}-${month + 1}-01`,
        };

        // Constructing the query string from requestData
        const params = new URLSearchParams(requestData).toString();

        fetch(`${window.fluentCalendarGutenbergVars.ajaxurl}?${params}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then((data) => {
                setAvailableDates(data?.available_slots);
                setTimezone(data?.timezone);
            })
            .catch((error) => {
                console.error('There was an error!', error);
            });

    };

    useEffect(() => {
        loadAvailableDates();
    }, [slotId]);

    useEffect(() => {
        initMonth();
    }, [availableDates]);

    let currentDate = new Date();
    function formatDate(date) {
        const year  = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day   = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    return [
        <Fragment>
            <div className="fcal_block_calendar_container">
                <div className="fcal_block_calendar_header">
                    <div className="fcal_block_calendar_month_year">
                        <h3>{monthNames[month]} <span>{year}</span></h3>
                    </div>
                </div>

                <div className="fcal_block_calendar">
                    {headers.map((header, index) => (
                        <span className="day-name" key={index}>
                            {header}
                        </span>
                    ))}
                    {days.map((day, index) => (
                        <Fragment key={index}>
                            {day.enabled ? (
                                <span
                                    role="button"
                                    tabIndex="0"
                                    aria-label={`Select Day ${day.name}`}
                                    className={`day day-enabled`}
                                >
                                  <span className={formatDate(currentDate) === day.date ? 'is-today' : ''}>
                                    {day.name}
                                  </span>
                                </span>
                            ) : (
                                <span className="day day-disabled">
                                  <span className={formatDate(currentDate) === day.date ? 'is-today' : ''}>
                                    {day.name}
                                  </span>
                                </span>
                            )}
                        </Fragment>
                    ))}
                </div>

                {timezone ?
                    <div className="fcal_block_timezone">
                        <h3>{__('Timezone')}</h3>
                        <span className="timezone">
                            {timezone}
                        </span>
                    </div>
                : ''}

            </div>
        </Fragment>
    ]
}

