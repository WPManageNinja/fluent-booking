import StandAlonePhoneField from './StandAlonePhoneField.svelte';
import './style.scss';

document.body.addEventListener('fcal_init_phone_field', function (e) {
    const elem = document.getElementById(e.detail.elementId);
    new StandAlonePhoneField({
        target: elem,
        props: {
            appData: {
                msg: 'OK'
            },
        }
    });
});
