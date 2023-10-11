<div class="fcal_booking_form_wrap" id="fcal_booking_form_wrap">
    <div class="fcal_booking_form">
        <form on:submit|preventDefault={submitForm}>
            {#each formFields as field}
                {#if field.enabled}
                    <div class="fcal_form_item">
                        <label class="fcal_input_content">
                            <div class="fcal_input_label">
                                {field.label}
                                {#if field.required}<span>*</span>{/if}
                            </div>
                            {#if field.type === 'text'}
                                <input disabled="{field.disabled}" class="fcal_input" type="text"
                                    placeholder="{field.placeholder}" bind:value={form[field.name]}/>
                            {:else if field.type === 'email'}
                                <input disabled="{field.disabled}" class="fcal_input" type="email"
                                    placeholder="{field.placeholder}" bind:value={form[field.name]}/>
                            {:else if field.type === 'number'}
                                <input disabled="{field.disabled}" class="fcal_input" type="number"
                                    placeholder="{field.placeholder}" bind:value={form[field.name]}/>
                            {:else if field.type === 'phone'}
                                <input disabled="{field.disabled}" class="fcal_input" type="number"
                                    placeholder="{field.placeholder}" bind:value={form[field.name]}/>
                            {:else if field.type === 'textarea'}
                                <textarea placeholder="{field.placeholder}" disabled="{field.disabled}"
                                        class="fcal_input" bind:value={form[field.name]}/>
                            {:else if field.type === 'dropdown'}
                            <select bind:value={form[field.name]}>
                                <option value="" disabled selected>{field.placeholder}</option>
                                {#each field.options as option (option)}
                                    <option value={option}>{option}</option>
                                {/each}
                            </select>
                            {:else if field.type === 'payment'}
                                <div class="fcal_payment_items_wrapper">
                                {#each field.payment_items as item}
                                    <div class="fcal_payment_items">
                                        <input type="hidden" disabled="true" value="{item.value}" class="fcal_input"/>
                                        <p>{item.title}:</p>
                                        <p>
                                            <span>{@html field.currency_sign}</span>
                                                {item.value}
                                        </p>
                                    </div>
                                {/each}
                                </div>
                            {/if}
                        </label>
                    </div>
                {/if}
            {/each}
            <div class="fcal_form_item">
                {@html appData.payment_methods.template}
            </div>
            <div class="fcal_form_item fcal_submit">
                <button disabled="{submitting}" type="submit"
                        class="fcal_btn_submit { submitting ? 'fcal_btn_submitting' : '' }">
                    Schedule Meeting
                </button>
            </div>
        </form>
        {#if errors}
            <div class="fcal_errors">
                {@html errors}
            </div>
        {/if}
    </div>
</div>
<script>
    import {util, getErrorText} from '../util.js';
    import {createEventDispatcher} from 'svelte';

    export let timezone;
    export let formFields;
    export let spot;
    export let slot;

    export let appData;

    const form = window.fluentCalendarPublicVars.current_person;

    let submitting = false;

    let dispatch = createEventDispatcher();

    let errors = '';

    const currentUrl = window.location.href;

    function submitForm(e) {
        const formFields = e.target.elements;
        const selectedMethod = formFields?.stripe_payment_method?.value;

        const postdata = {
            ...form,
            timezone,
            start_date: spot.start,
            event_id: slot.id,
            source_url: currentUrl,
            payment_method: selectedMethod,
            action: 'fluent_cal_schedule_meeting'
        }

        if (submitting) return;

        submitting = true;

        errors = '';

        util.$post(window.fluentCalendarPublicVars.ajaxurl, postdata)
            .then(res => {
                dispatch('bookingConfirmed', res);
                if (res.data.redirect_to) {
                    window.location.href = res.data.redirect_to;
                }
            })
            .catch(err => {
                errors = getErrorText(err.response);
            })
            .finally(() => {
                submitting = false;
            });
    }
</script>
