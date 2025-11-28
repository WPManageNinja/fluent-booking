<div class="fcal_input_content">
    {#if field.label}
        <div class="fcal_input_label">
            {field.label}
            {#if field.required}
                <span>*</span>
            {/if}
        </div>
    {/if}
    <div class="fcal_input_wrap fcal_input_location_wrap">
        {#each field.options as option}
            <label class="fcal_radio_group" aria-label={option}>
                {i18(option.title)}
                <input type="radio" required on:change={onChangeDriver} data-driver={option.type}
                       checked={form.location_config.driver == option.slug}
                       name={field.name} value={option.slug}/>
                <span class="fcal_radio_icon"></span>
            </label>
        {/each}
        {#if form.location_config.driverType == 'phone_guest' }
            <PhoneFieldSkeleton field={{ name: 'user_location_input'}} bind:form={form.location_config}/>
        {:else if form.location_config.driverType == 'in_person_guest'}
            <div class="fcal_input_wrap address">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round"
                     class="feather feather-map-pin">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                <textarea style="padding-left: 32px;" class="fcal_input" disabled={field.disabled}
                    aria-required={field.required} aria-invalid={!form.location_config.user_location_input}
                    bind:value={form.location_config.user_location_input}
                    placeholder="{i18('Your address')}"></textarea>
            </div>
        {/if}
        {#if hasError && errorText && (form.location_config.driverType == 'phone_guest' || form.location_config.driverType == 'in_person_guest')}
            <div class="fcal_validation_error">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 ltr:mr-2 rtl:ml-2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" x2="12" y1="16" y2="12"></line>
                    <line x1="12" x2="12.01" y1="8" y2="8"></line>
                </svg>
                <p>{errorText}</p>
            </div>
        {/if}
        {#if appData.is_fluentform}
            <input type="hidden" name="{appData.name + '__location'}"
                   value={JSON.stringify(form.location_config)}/>
        {/if}
    </div>
</div>

<script>
    import PhoneFieldSkeleton from "./PhoneFieldSkeleton.svelte";
    import { i18 } from '../util.js';

    export let appData;
    export let form;
    export let field;
    export let hasError;
    export let validating;

    let errorText = false;

    $: if (validating || !validating) {
        updateValidationError();
    }

    if (!form.location_config) {
        form.location_config = {
            driver: '',
            user_location_input: '',
            driverType: ''
        };
    }

    if (!form.location_config.driver) {
        // find the first option from field.options
        if (field.options && field.options.length > 0) {
            form.location_config.driver = field.options[0].slug;
            form.location_config.driverType = field.options[0].type;
        }
    }

    function onChangeDriver(event) {
        form.location_config.driver = event.currentTarget.value;
        form.location_config.driverType = event.currentTarget.dataset.driver;
        form.location_config.user_location_input = '';
    }

    function updateValidationError() {
        errorText = false;
        const driver = form.location_config.driverType;
        const userInput  = form.location_config.user_location_input;
        if (((driver == 'in_person_guest' || driver == 'phone_guest') && !userInput)) {
            errorText = i18('This field is required.');
        }
    }

</script>
