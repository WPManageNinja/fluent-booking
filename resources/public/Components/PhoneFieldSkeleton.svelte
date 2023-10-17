<div>
    <div id="{currentFieldId}" class="fcal_custom_phone_field"></div>
    <span>{form[field.name]}</span>
</div>

<script>
    import {onMount} from 'svelte';

    export let field;
    export let form;
    // Crate a unique id for this field
    let currentFieldId = 'fcal_phone_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);

    // callback on a load the html of this svelte component
    onMount(() => {
        const elem = document.getElementById(currentFieldId);

        elem.addEventListener('value_changed', (e) => {
            form[field.name] = e.detail.value;
        });

        document.body.dispatchEvent(new CustomEvent('fcal_init_phone_field', {
            detail: {
                elementId: currentFieldId,
                elem: elem,
                field: field,
                form: form
            }
        }));
    });

</script>

