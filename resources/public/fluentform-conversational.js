import BookingApp from './BookingApp.svelte';
import './styles.scss';
import './saas.scss';
import '../scss/_fluentform_classic.scss';

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
                handleBack: false
            },
        });

        element.classList.remove('fcal_loading');
        element.dataset.app_booted = true;

        handleFormScroll();
    }
});

function handleFormScroll() {
    let fluentFormDom = document.getElementsByClassName(
        'ff_conv_app_' + window.fluent_forms_global_var.form.id
    );

    if (fluentFormDom) {
        fluentFormDom = fluentFormDom[0];
        function preventScroll(event) {
            fluentFormDom.addEventListener(event, function (event) {
                if (event.target.className.includes('fcal')) {
                    event.stopImmediatePropagation();
                }
            }, true);
        }

        preventScroll('wheel');
        preventScroll('swiped');
    }
}
