import BookingApp from './BookingApp.svelte';
import './styles.scss';
import './saas.scss';
import '../scss/_fluentform_classic.scss';

const calendarApps = document.querySelectorAll('.fluentform_calendar_app');

if (calendarApps.length) {
    calendarApps.forEach((item) => {
        const elem = item;
        const elemId = elem.dataset.element_id;
        if (elem.dataset.app_booted) {
            return;
        }
        if (elemId) {
            elem.innerHTML = '';
            const appData = window['fcal_public_vars_' + elemId];
            appData.is_fluentform = true;
            appData.id = elemId;

            const preDate = appData.slot?.pre_selects?.date;
            if (preDate) {
                appData.slot.pre_selects.year = preDate.substr(0, 4),
                appData.slot.pre_selects.month = preDate.substr(5, 2),
                appData.slot.pre_selects.day = preDate.substr(8, 2)
            }

            new BookingApp({
                target: elem,
                props: {
                    appData: appData,
                    handleBack: false
                }
            });

            elem.classList.remove('fcal_loading');
            elem.dataset.app_booted = true;
        }
    });
}
