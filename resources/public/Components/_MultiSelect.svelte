<div class="fcal_input_content">
    {#if field.label}
        <div class="fcal_input_label">
            {field.label}
            {#if field.required}
                <span>*</span>
            {/if}
        </div>
    {/if}
    <div class="fcal_input_wrap fcal_input_multi_select_wrap">
        <Select
            :placeholder="i18('Please Select')"
            multiple={true}
            clearable={false}
            items={field.options}
            bind:value={form[field.name]}
        />
    </div>
    {#if hasError && errorText}
        <div class="fcal_validation_error">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 ltr:mr-2 rtl:ml-2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" x2="12" y1="16" y2="12"></line>
                <line x1="12" x2="12.01" y1="8" y2="8"></line>
            </svg>
            <p>{errorText}</p>
        </div>
    {/if}
</div>

<script>
    import Select from 'svelte-select';
    import { i18 } from '../util';

    export let field;
    export let form;
    export let validating;
    export let hasError;

    let errorText = false;

    $: if (validating || !validating) {
        updateValidationError();
    }

    function updateValidationError() {
        errorText = false;
        if (field.required && !form[field.name]) {
            errorText = i18('This field is required.');
        }
    }

</script>