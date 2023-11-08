import BookingApp from './BookingApp.svelte';
import './styles.scss';
import './saas.scss';

// get month get parameter from url
let preSelects = null;

const urlParams = new URLSearchParams(window.location.search);
const monthYear = urlParams.get('month');
const fullDate = urlParams.get('date');

if (fullDate && fullDate.length >= 10) {
    preSelects = {
        year: fullDate.substr(0, 4),
        month: fullDate.substr(5, 2),
        day: fullDate.substr(8, 2),
    }
} else if (monthYear && monthYear.length >= 7) {
    preSelects = {
        year: monthYear.substr(0, 4),
        month: monthYear.substr(5, 2),
    }
}

window.fluentCalBootApp = function (elem, handleBack = false) {
    let calendarId = elem.dataset.calendar_id;
    let event_id = elem.dataset.event_id;
    if (elem.dataset.app_booted) {
        console.log('App already booted');
        return;
    }

    if (!calendarId || !event_id) {
        console.log('App could not be booted');
        return;
    }

    elem.classList.add('fcal_loading');

    elem.innerHTML = '';
    const appData = window['fcal_public_vars_' + calendarId + '_' + event_id];

    if (!appData) {
        return;
    }

    if (preSelects) {
        appData.slot.pre_selects = preSelects;
    }

    if(appData.lazy_js_files) {
        // load js files, appData.lazy_js_files is an object
        for (const fileKey in appData.lazy_js_files) {
            // check if script is already loaded
            if(document.getElementById(fileKey)) {
                continue;
            }

            const script = document.createElement('script');
            script.src = appData.lazy_js_files[fileKey];
            script.async = true;
            script.id = fileKey;
            document.body.appendChild(script);
        }
    }

    const app = new BookingApp({
        target: elem,
        props: {
            appData: appData,
            handleBack: handleBack
        }
    });

    // remove css class fcal_loading from elem
    elem.classList.remove('fcal_loading');

    elem.dataset.app_booted = true;

    return app;
};

const calendarApps = document.querySelectorAll('.fluent_booking_app');
if (calendarApps.length) {
    calendarApps.forEach((item, index) => {
        window.fluentCalBootApp(calendarApps[index]);
    });
}

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function () {
    // Select all <a> tags with the class 'fcal_event_card'
    var links = document.querySelectorAll('a.fcal_event_card');

    // Add a click event listener to each link
    links.forEach(function (link) {
        link.addEventListener('click', function (event) {
            // Prevent the default action (navigation)
            event.preventDefault();
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
                if (window.history.pushState) {
                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.delete('event');

                    let ext = urlParams.toString();
                    if(ext) {
                        ext = '?' + ext;
                    }

                    window.history.pushState({}, '', window.fluentCalendarPublicVars.base_url + ext );
                }
            });

            if (window.history.pushState) {
                if (window.fluentCalendarPublicVars.is_pretty_url) {
                    window.history.pushState({}, '', `${window.fluentCalendarPublicVars.base_url}/${eventSlug}`);
                } else {
                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.set('event', eventSlug);
                    window.history.pushState({}, '', `${window.fluentCalendarPublicVars.base_url}?${urlParams.toString()}`);
                }
            }
        });
    });
});


