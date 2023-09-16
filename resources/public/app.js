import BookingApp from './BookingApp.svelte';
import './styles.scss';
import './saas.scss';

const calendarApps = document.querySelectorAll('.fluent_booking_app');

// get month get parameter from url
let preSelects = null;

const urlParams = new URLSearchParams(window.location.search);
const monthYear = urlParams.get('month');
const fullDate = urlParams.get('date');

if(fullDate && fullDate.length >= 10) {
    preSelects = {
        year: fullDate.substr(0, 4),
        month: fullDate.substr(5, 2),
        day: fullDate.substr(8, 2),
    }
} else if(monthYear && monthYear.length >= 7) {
    preSelects = {
        year: monthYear.substr(0, 4),
        month: monthYear.substr(5, 2),
    }
}

if (calendarApps.length) {
    calendarApps.forEach((item, index) => {
        const elem = calendarApps[index];
        let calendarId = elem.dataset.calendar_id;
        let slot_id = elem.dataset.slot_id;
        if (elem.dataset.app_booted) {
            console.log('App already booted');
            return;
        }
        if (calendarId && slot_id) {
            elem.innerHTML = '';
            const appData = window['fcal_public_vars_' + calendarId + '_' + slot_id];

            if(preSelects) {
                appData.slot.pre_selects = preSelects;
            }

            new BookingApp({
                target: elem,
                props: {
                    appData: appData,
                }
            });

            // remove css class fcal_loading from elem
            elem.classList.remove('fcal_loading');

            elem.dataset.app_booted = true;
        }
    });
}
