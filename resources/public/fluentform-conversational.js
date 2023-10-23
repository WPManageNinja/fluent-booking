import BookingApp from './BookingApp.svelte';
import './styles.scss';
import './saas.scss';

document.body.addEventListener('ffc_init_custom_field', function (e) {
    if ('fcal_booking' !== e.detail?.question?.ff_input_type) {
        return;
    }
    
    let element = e.detail.element;

    if (element.dataset.app_booted) {
        return;
    }

    if (e.detail.elementId) {
        const appData = window['fcal_public_vars_' + e.detail.elementId];

        element.innerHTML = '';
        appData.is_fluentform = true;
        appData.isFFConversational = true;
        appData.id = e.detail.elementId;
        appData.element = element;

        new BookingApp({
            target: element,
            props: {
                appData: appData,
            },
        });

        element.classList.remove('fcal_loading');
        element.dataset.app_booted = true;
    }
});
