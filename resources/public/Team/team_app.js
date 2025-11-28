import './style.scss';
import './team-style-rtl.scss';

function handleEventBlockClick(link) {
    const elem = link;

    // get calendar id and event id from data attributes
    let calendarId = elem.dataset.calendar_id;
    let event_id = elem.dataset.event_id;
    let eventSlug = elem.dataset.event_slug;


    const html = '<div class="fluent_booking_app fcal_loading" data-calendar_id="' + calendarId + '" data-event_id="' + event_id + '"><h3>Loading</h3></div>';

    const wrap = elem.closest('.fcal_calendar_wrap');

    // append the html to .fcal_calendar_wrap element do not replace it
    wrap.insertAdjacentHTML('beforeend', html);

    const bookingWrap = wrap.querySelector('.fluent_booking_wrap');
    // hide .fluent_booking_wrap
    bookingWrap.style.marginLeft = '-100%';
    bookingWrap.style.height = '0';
    bookingWrap.style.overflow = 'hidden';

    setTimeout(() => {
        const targetElement = wrap.querySelector('.fluent_booking_app');
        if (targetElement) {
            const scrollTo = targetElement.getBoundingClientRect().top + window.pageYOffset - 55;
            window.scrollTo({
                top: scrollTo,
                behavior: 'smooth'
            });
        }
    }, 50);

    // get the element of the inserted html
    const elemItem = wrap.lastElementChild;

    const app = window.fluentCalBootApp(elemItem, true);

    app.$on('handleBack', function () {
        app.$destroy();
        elemItem.remove();
        bookingWrap.style.height = 'auto';
        const parentTeam = elem.closest('.fcal_teams');
        if (parentTeam) {
            bookingWrap.style.marginLeft = '0';
        } else {
            bookingWrap.style.marginLeft = 'auto';
        }
        if (window.history.pushState && window.fcal_landing_page) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.delete('event');

            let ext = urlParams.toString();
            if (ext) {
                ext = '?' + ext;
            }

            window.history.pushState({}, '', window.fluentCalendarPublicVars.base_url + ext);
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

function faCalOpenBookingPage(item, event) {
    event.preventDefault();
    handleEventBlockClick(item);
}

window.faCalOpenBookingPage = faCalOpenBookingPage;

const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

document.cookie = "fluent_booking_user_timezone=" + timeZone + "; path=/";

// Now you can append teamViewHtml to the DOM or do whatever you need with it
// For example, if you want to append it to the body:

window.fcalBackToTeam = function (item) {
    const parentTeam = item.closest('.fcal_teams');
    if (parentTeam) {
        parentTeam.querySelector('.fluent_booking_team_view').innerHTML = '';
        parentTeam.querySelector('.fcal_teams_wrap').classList.remove('hide');
        parentTeam.querySelector('.fcal_teams_wrap').style.marginLeft = '0';
    }
};

document.querySelectorAll('.fcal_teams').forEach(function (teams) {

    let currentState = 'view_members';

    let teamViewHtml = document.createElement('div');
    teamViewHtml.className = 'fluent_booking_team_view';

    teams.querySelector('.fcal_teams_wrap').style.display = 'block';
    teams.querySelector('.fcal_team_loading').remove();
    // find all the buttons in teams with class name fcal_each_member
    const buttons = teams.querySelectorAll('.fcal_each_member');

    const teamInner = teams.querySelectorAll('.fcal_teams_inner')
    teamInner.forEach(function (inner) {
        inner.appendChild(teamViewHtml);
    });

    const teamVars = window[teams.id];
    if (!teamVars) {
        return;
    }
    buttons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            const calenderId = button.dataset.calendar_id;
            const hostVars = teamVars['fcal_host_' + calenderId];

            if (!hostVars) {
                console.error('Team var could not be found');
                return;
            }

            if (hostVars.host_html) {
                teamViewHtml.innerHTML = hostVars.host_html;
                teams.querySelector('.fcal_teams_wrap').style.marginLeft = '-100%';
                teams.querySelector('.fcal_teams_wrap').classList.add('hide');
                currentState = 'view_member';

                if (hostVars.target_event_id) {
                    // find the dom a with data-event_id=hostVars.target_event_id
                    const eventBlock = teamViewHtml.querySelector('a[data-event_id="' + hostVars.target_event_id + '"]');
                    if (eventBlock) {
                        handleEventBlockClick(eventBlock);
                    }
                }
            }
        });
    });
});
