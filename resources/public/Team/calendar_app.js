import './style.scss';

function handleEventBlockClick(link) {
    const elem = link;

    let calendarId = elem.dataset.calendar_id;
    let event_id = elem.dataset.event_id;
    let eventSlug = elem.dataset.event_slug;

    const html = '<div class="fluent_booking_app fcal_loading" data-calendar_id="' + calendarId + '" data-event_id="' + event_id + '"><h3>Loading</h3></div>';

    const wrap = elem.closest('.fcal_calendar_wrap_block');

    wrap.insertAdjacentHTML('beforeend', html);

    const bookingWrap = wrap.querySelector('.fluent_booking_wrap');

    bookingWrap.style.marginLeft = '-100%';
    bookingWrap.style.height = '0';
    bookingWrap.style.overflow = 'hidden';

    const targetElement = wrap.querySelector('.fluent_booking_app');
    if (targetElement) {
        targetElement.scrollIntoView({
            behavior: 'smooth'
        });
    }


    const elemItem = wrap.lastElementChild;

    const app = window.fluentCalBootApp(elemItem, true);

    app.$on('handleBack', function () {
        app.$destroy();
        elemItem.remove();
        bookingWrap.style.height = 'auto';

        const parentTeam = elem.closest('.fcal_calendar_wrapper');
        if (parentTeam) {
            bookingWrap.style.marginLeft = '0';
        } else {
            bookingWrap.style.marginLeft = 'auto';
        }

        if (window.history.pushState && window.fcal_landing_page) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.delete('event');

            let ext = urlParams.toString();
            ext = ext ? '?' + ext : '';

            window.history.pushState({}, '', window.fluentCalendarPublicVars.base_url + ext );
        }
    });

    if (window.history.pushState && window.fcal_landing_page) {
        if (window.fluentCalendarPublicVars.is_pretty_url) {
            window.history.pushState({}, '', `${window.fluentCalendarPublicVars.base_url}/${eventSlug}`);
        } else {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('event', eventSlug);
            window.history.pushState({}, '', `${window.fluentCalendarPublicVars.base_url}?${urlParams.toString()}`);
        }
    }
}

function faCalOpenBookingPageBlock(item, event) {
    event.preventDefault();
    handleEventBlockClick(item);
}

window.faCalOpenBookingPageBlock = faCalOpenBookingPageBlock;

const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

document.cookie = "fluent_booking_user_timezone=" + timeZone + "; path=/";

document.querySelectorAll('.fcal_calendars').forEach(function (calendars) {

    const calendar = calendars.closest('.fcal_calendar_wrapper');

    const calendarVars = window[calendar?.id];

    if (calendarVars) {
        const calVars = calendarVars['fcal_host_calendar'];

        let calendarViewHtml = document.createElement('div');

        calendarViewHtml.className = 'fluent_booking_calendar_view';

        calendarViewHtml.innerHTML = calVars?.calendar_html;

        calendar.querySelector('.fcal_cals_wrap').style.display = 'block';

        calendar.querySelector('.fcal_calendar_loading').remove();

        calendar.querySelector('.fcal_calendar_block_inner').appendChild(calendarViewHtml);
    }
});