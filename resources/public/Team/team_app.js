import './style.scss';

function handleEventBlockClick(link) {
    const elem = link;

    // get calendar id and event id from data attributes
    let calendarId = elem.dataset.calendar_id;
    let event_id = elem.dataset.event_id;
    let eventSlug = elem.dataset.event_slug;

    // crelate html like this
    // <div className="fluent_booking_app fcal_loading" data-calendar_id="1" data-event_id="1">

    const html = '<div class="fluent_booking_app fcal_loading" data-calendar_id="' + calendarId + '" data-event_id="' + event_id + '"><h3>Loading</h3></div>';

    // append the html to .fcal_calendar_wrap element do not replace it
    document.querySelector('.fcal_calendar_wrap').insertAdjacentHTML('beforeend', html);

    // hide .fluent_booking_wrap
    document.querySelector('.fluent_booking_wrap').style.display = 'none';

    // get the element of the inserted html
    const elemItem = document.querySelector('.fcal_calendar_wrap').lastElementChild;

    const app = window.fluentCalBootApp(elemItem, true);

    app.$on('handleBack', function () {
        app.$destroy();
        elemItem.remove();
        document.querySelector('.fluent_booking_wrap').style.display = 'block';

        const parentTeam = elem.closest('.fcal_teams');
        if(parentTeam) {
            parentTeam.classList.remove('fcal_showing_team_calendar');
            parentTeam.classList.add('fcal_showing_team_events');
        }

        if (window.history.pushState && window.fcal_landing_page) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.delete('event');

            let ext = urlParams.toString();
            if(ext) {
                ext = '?' + ext;
            }

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

function faCalOpenBookingPage(item, event) {
    event.preventDefault();
    const parentTeam = item.closest('.fcal_teams');
    if(parentTeam) {
        parentTeam.classList.add('fcal_showing_team_calendar');
        parentTeam.classList.remove('fcal_showing_team_events');
    }
    handleEventBlockClick(item);
}

window.faCalOpenBookingPage = faCalOpenBookingPage;

window.fcalBackToTeam = function (item) {
    const parentTeam = item.closest('.fcal_teams');
    if(parentTeam) {
        parentTeam.querySelector('.fluent_booking_team_view').remove();
        parentTeam.querySelector('.fcal_teams_wrap').style.display = 'block';
    }
};

document.querySelectorAll('.fcal_teams').forEach(function (teams) {

    let currentState = 'view_members';

    teams.querySelector('.fcal_teams_wrap').style.display = 'block';
    teams.querySelector('.fcal_team_loading').remove();
    // find all the buttons in teams with class name fcal_each_member
    const buttons = teams.querySelectorAll('.fcal_each_member');
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
                // create html from hostVars.host_html with wrapper fcal_team
                const html = '<div class="fluent_booking_team_view">'+hostVars.host_html+'</div>';
                // append the html to .fcal_calendar_wrap element do not replace it
                teams.insertAdjacentHTML('beforeend', html);
                // hide .fcal_teams_wrap
                teams.querySelector('.fcal_teams_wrap').style.display = 'none';
                currentState = 'view_member';
            }
        });
    });
});
