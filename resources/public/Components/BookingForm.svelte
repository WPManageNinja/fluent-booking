<div class="fcal_booking_form_wrap">
    <div class="fcal_booking_form">
        {#each formFields as field}
            <div class="fcal_form_item">
                <label class="fcal_input_content">
                    <div class="fcal_input_label">
                        {field.label}
                        {#if field.required}<span>*</span>{/if}
                    </div>
                    {#if field.type === 'text'}
                        <input disabled="{field.disabled}" class="{field.input_class}" type="text"
                               placeholder="{field.placeholder}" bind:value={form[field.name]}/>
                    {:else if field.type === 'email'}
                        <input disabled="{field.disabled}" class="{field.input_class}" type="email"
                                   placeholder="{field.placeholder}" bind:value={form[field.name]}/>
                    {:else if field.type === 'number'}
                        <input disabled="{field.disabled}" class="{field.input_class}" type="number"
                               placeholder="{field.placeholder}" bind:value={form[field.name]}/>
                    {:else if field.type === 'tel'}
                        <input disabled="{field.disabled}" class="{field.input_class}" type="tel"
                               placeholder="{field.placeholder}" bind:value={form[field.name]}/>
                    {:else if field.type === 'textarea'}
                        <textarea  placeholder="{field.placeholder}"  disabled="{field.disabled}" class="{field.input_class}" bind:value={form[field.name]}/>
                    {/if}
                </label>
            </div>
        {/each}
        <div class="fcal_form_item fcal_submit">
            <button disabled="{submitting}" on:click={submitForm}
                    class="fcal_btn_submit { submitting ? 'fcal_btn_submitting' : '' }">
                Schedule Meeting
            </button>
        </div>
    </div>
</div>

<script>
    import {util} from '../util.js';
    import {createEventDispatcher} from 'svelte';

    export let timezone;
    export let formFields;
    export let spot;
    export let slot;

    const form = window.fluentCalendarPublicVars.current_person;

    let submitting = false;

    let dispatch = createEventDispatcher();

    function submitForm() {

        const postdata = {
            ...form,
            timezone,
            start_date: spot.start,
            slot_id: slot.id
        }

        if (submitting) return;

        submitting = true;

        util.$post(`slots/${slot.id}/schedule`, postdata)
            .then(res => {
                dispatch('bookingConfirmed', res);
            })
            .catch(err => {
                console.log(err);
            })
            .finally(() => {
                submitting = false;
            });
    }

    function getFieldType(field) {
        return 'text';
    }
</script>
