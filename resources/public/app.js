import BookingApp from './BookingApp.svelte';
import './styles.scss';
import './saas.scss';

// get month get parameter from url
let preSelects = {};

const urlParams = new URLSearchParams(window.location.search);
const monthYear = urlParams.get('month');
const fullDate = urlParams.get('date');
const duration = urlParams.get('duration');
const time = urlParams.get('time');

function setPreSelectDate(fullDate, monthYear = null) {
    if (fullDate && fullDate.length >= 10) {
        return {
            year: fullDate.substr(0, 4),
            month: fullDate.substr(5, 2),
            day: fullDate.substr(8, 2),
        };
    } else if (monthYear && monthYear.length >= 7) {
        return {
            year: monthYear.substr(0, 4),
            month: monthYear.substr(5, 2),
        };
    }
    return {};
}

preSelects = setPreSelectDate(fullDate, monthYear);

if (time) {
    preSelects.time = time;
}

if (duration) {
    preSelects.duration = duration;
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

    if (appData.theme == 'system-default') {
        const gutenBlockMode = document.querySelector('.fcal_cal_wrap');
        runColorMode((isDarkMode) => {
            applyModeClasses(gutenBlockMode, isDarkMode);
        });
    }

    const preDate = appData.slot?.pre_selects?.date;
    if (preDate) {
        appData.slot.pre_selects.year = preDate.substr(0, 4),
        appData.slot.pre_selects.month = preDate.substr(5, 2),
        appData.slot.pre_selects.day = preDate.substr(8, 2)
    }
    if (preSelects) {
        appData.slot.pre_selects = {
            ...appData.slot.pre_selects,
            ...preSelects
        };
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
const calwrap   = document.querySelector('.calendar_wrap');
const fcalLanding_page  = document.querySelector('.fcal_calendar_wrap');

function runColorMode(fn) {
    if (!window.matchMedia) {
        return;
    }

    const query = window.matchMedia('(prefers-color-scheme: dark)');
    fn(query.matches);
    query.addEventListener('change', (event) => fn(event.matches));
}

function applyModeClasses(element, darkMode) {
    if (element) {
        const darkClass  = 'fcal-dark-mode';
        const lightClass = 'fcal-light-mode';

        if (darkMode) {
            element.classList.add(darkClass);
            element.classList.remove(lightClass);
        } else {
            element.classList.add(lightClass);
            element.classList.remove(darkClass);
        }
    }
}

if (themeMode === 'system-default') {
    runColorMode((isDarkMode) => {
        const elementsToApplyClasses = [calwrap, fcalLanding_page];
        elementsToApplyClasses.forEach((element) => applyModeClasses(element, isDarkMode));
    });
} else if (themeMode === 'dark-mode') {
    const elementsToApplyClasses = [calwrap, fcalLanding_page];
    elementsToApplyClasses.forEach((element) => applyModeClasses(element, true));
} else {
    const elementsToApplyClasses = [calwrap, fcalLanding_page];
    elementsToApplyClasses.forEach((element) => applyModeClasses(element, false));
}

