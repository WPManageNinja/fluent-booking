import StandAlonePhoneField from './StandAlonePhoneField.svelte';
import './style.scss';

document.body.addEventListener('fcal_init_phone_field', function (e) {
    new StandAlonePhoneField({
        target: e.detail.elem,
        props: {
            appData: {
                elementId: e.detail.elementId,
                elem: e.detail.elem,
                formValue: e.detail.formValue
            },
        }
    });
});

document.body.addEventListener('fcal_destroy_phone_field', function (e) {

});
