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


const themeMode = window.fluentCalendarPublicVars.theme;
if (themeMode == 'system-default') {
    // System Mode
    const runColorMode = (fn) => {
        if (!window.matchMedia) {
            return;
        }
        const query = window.matchMedia('(prefers-color-scheme: dark)');
        fn(query.matches);
        query.addEventListener('change', (event) => fn(event.matches));
    }
    runColorMode((isDarkMode) => {
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            document.body.classList.remove('light-mode');
        } else {
            document.body.classList.add('light-mode');
            document.body.classList.remove('dark-mode');
        }
    })
} else if (themeMode == 'dark-mode') {
    document.body.classList.remove('light-mode');
    document.body.classList.add('dark-mode');
} else {
    document.body.classList.remove('dark-mode');
    document.body.classList.add('light-mode');
}
