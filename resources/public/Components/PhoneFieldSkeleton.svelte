<div bind:this={nodeRef}>
    <div id="{currentFieldId}" class="fcal_custom_phone_field"></div>
</div>

<script>
    import { onMount, onDestroy } from 'svelte';
    let nodeRef;
    export let field;
    export let form;
    // Crate a unique id for this field
    let currentFieldId = 'fcal_phone_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);

    let elem;

    // callback on a load the html of this svelte component
    onMount(() => {
        elem = document.getElementById(currentFieldId);

        elem.addEventListener('value_changed', (e) => {
            if (e.detail.value) {
                form[field.name] = e.detail.value;
            }
        });

        document.body.dispatchEvent(new CustomEvent('fcal_init_phone_field', {
            detail: {
                elementId: currentFieldId,
                elem: elem,
                formValue: form[field.name]
            }
        }));
    });

    onDestroy(() => {
        nodeRef.parentNode.removeChild(nodeRef)
        document.body.dispatchEvent(new CustomEvent('fcal_destroy_phone_field', {
            detail: {
                elementId: currentFieldId,
                elem: elem
            }
        }));
    });

</script>

