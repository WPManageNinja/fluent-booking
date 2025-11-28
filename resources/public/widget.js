import BookingApp from './BookingApp.svelte';
import './styles.scss';
import './saas.scss';
import {util, getErrorText} from './util.js';

const calendarApps = document.querySelectorAll('.fluent_booking_app');

if (calendarApps.length) {
    calendarApps.forEach((item, index) => {
        const elem = calendarApps[index];
        let calendarUrl = elem.dataset.url;
        if (elem.dataset.app_booted) {
            console.log('App already booted');
            return;
        }
        if (calendarUrl) {
            let ajaxUrl = 'https://convertleap.con/app/';
            if(elem.dataset.server) {
                ajaxUrl = elem.dataset.server;
            }
            util.$post(ajaxUrl, {
                booking_url: calendarUrl,
                action: 'fcal_get_widget_vars'
            })
                .then((response) => {
                    window.fluentCalendarPublicVars = response.global_vars;
                    elem.innerHTML = '';
                    new BookingApp({
                        target: elem,
                        props: {
                            appData: response.app_vars,
                            handleBack: false
                        }
                    });
                    elem.dataset.app_booted = true;
                    // set the height of the elem as auto
                    elem.style.height = 'auto';
                })
                .catch((error) => {
                    elem.innerHTML = getErrorText(error.response);
                    elem.style.height = 'auto';
                });
        }
    });
}
