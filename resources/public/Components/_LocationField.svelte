<div class="fcal_input_wrap fcal_input_location_wrap">
    {#each field.options as option}
        <label class="fcal_location_radio_list">
            {i18(option.title)}
            <input type="radio" on:change={onChangeDriver} checked={form.location_config.driver === option.type}
                   name={field.name} value={option.type}/>
            <span class="fcal_radio_icon"></span>
        </label>
    {/each}
    {#if form.location_config.driver == 'phone_guest' }
        <PhoneFieldSkeleton field={ { name: 'user_location_input'} } form={form.location_config}/>
    {:else if form.location_config.driver == 'in_person_guest'}
        <div class="fcal_input_wrap address">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round"
                 class="feather feather-map-pin">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                <circle cx="12" cy="10" r="3"/>
            </svg>
            <textarea style="padding-left: 32px;" disabled="{field.disabled}"
                      bind:value={form.location_config.user_location_input} class="fcal_input"
                      placeholder="{i18('Your address')}"></textarea>
        </div>
    {/if}
</div>

<script>
    import PhoneFieldSkeleton from "./PhoneFieldSkeleton.svelte";
    import {i18} from '../util.js';

    export let field;
    export let form;

    if (!form.location_config) {
        form.location_config = {
            driver: '',
            user_location_input: ''
        };
    }

    if (!form.location_config.driver) {
        // find the first option from field.options
        if (field.options && field.options.length > 0) {
            form.location_config.driver = field.options[0].type;
        }
    }

    function onChangeDriver(event) {
        form.location_config.driver = event.currentTarget.value;
        form.location_config.user_location_input = '';
        console.log(form);
    }

</script>
