import BookingApp from './BookingApp.svelte';
import './styles.scss';

const calendarApps = document.querySelectorAll('.fluent_calendar_app');

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

            new BookingApp({
                target: elem,
                props: {
                    appData: window['fcal_public_vars_' + calendarId + '_' + slot_id],
                }
            });

            elem.dataset.app_booted = true;
        }
    });
}
