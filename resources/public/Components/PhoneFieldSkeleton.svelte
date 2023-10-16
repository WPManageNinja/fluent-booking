<div>
    <div>
        <div id="{currentFieldId}" class="fcal_custom_phone_field"></div>
    </div>
</div>

<script>
    import { onMount } from 'svelte';
    // Crate a unique id for this field
    let currentFieldId = 'fcal_phone_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);

    // callback on load the html of this svelte component
    onMount(() => {
        // Send a custom event to body to trigger the field creation
        document.body.dispatchEvent(new CustomEvent('fcal_init_phone_field', {
            detail: {
                elementId: currentFieldId,
            }
        }));
    });


</script>

