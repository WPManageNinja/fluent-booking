import BookingApp from './BookingApp.svelte';
import './styles.scss';
import './saas.scss';

const calendarApps = document.querySelectorAll('.fluentform_calendar_app');

if (calendarApps.length) {
    calendarApps.forEach((item) => {
        const elem = item;
        const formId = elem.dataset.form_id;
        if (elem.dataset.app_booted) {
            return;
        }
        if (formId) {
            elem.innerHTML = '';
            const appData = window['fcal_public_vars_' + formId];
            appData.is_fluentform = true;
            appData.id = formId;

            new BookingApp({
                target: elem,
                props: {
                    appData: appData,
                }
            });

            elem.classList.remove('fcal_loading');
            elem.dataset.app_booted = true;
        }
    });
}
