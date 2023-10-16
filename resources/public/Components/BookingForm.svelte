<div class="fcal_booking_form_wrap" id="fcal_booking_form_wrap">
    <div class="fcal_booking_form">
        {#if submitting}
            <div class="fcal_loading_dates">
                <div class="fcal_loading_dates_inner">
                    <Pulse color="#0060e6"/>
                </div>
            </div>
        {/if}
        <form on:submit|preventDefault={submitForm}>
            {#each formFields as field}
                {#if field.enabled}
                    <div class="fcal_form_item">
                        <label class="fcal_input_content">
                            {#if field.label}
                                <div class="fcal_input_label">
                                    {#if !(field.type === 'payment' && appData?.slot?.type === 'free')}
                                        {field.label}
                                    {/if}
                                    {#if field.required}<span>*</span>{/if}
                                </div>
                            {/if}
                            {#if field.type === 'text'}
                                <div class={'fcal_input_wrap '+field.name}>
                                    {#if field.name == 'address'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round"
                                             class="feather feather-map-pin">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                            <circle cx="12" cy="10" r="3"/>
                                        </svg>
                                    {/if}
                                    <input disabled="{field.disabled}" class="fcal_input" type="text"
                                           placeholder="{field.placeholder}" bind:value={form[field.name]}/>
                                </div>
                            {:else if field.type === 'email'}
                                <input disabled="{field.disabled}" class="fcal_input" type="email"
                                       placeholder="{field.placeholder}" bind:value={form[field.name]}/>
                            {:else if field.type === 'number'}
                                <input disabled="{field.disabled}" class="fcal_input" type="number"
                                       placeholder="{field.placeholder}" bind:value={form[field.name]}/>
                            {:else if field.name === 'location'}
                                <LocationField field={field} form="{form}"/>
                            {:else if field.type === 'phone'}
                                <input disabled="{field.disabled}" class="fcal_input" type="text"
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
                            {:else if field.type === 'payment' && appData?.slot?.type === 'paid'}
                                <Payments field={field}/>
                            {:else if field.type === 'hidden' }
                                <input type="hidden" bind:value={form[field.name]}/>
                            {/if}
                        </label>
                    </div>
                {/if}
            {/each}

            <!--{/if}-->
            {#if hasPaymentItem()}
                <div class="fluent_booking_payment_processor" style="display:none;">
                    <h3 class="label">{i18('Total Payment')}
                        : {@html appData?.currency_sign} {getSubTotal(appData?.payment_items)}</h3>
                    {#if appData?.payment_methods?.template}
                        <div class="fcal_form_payment_item">
                            {@html appData.payment_methods.template}
                        </div>
                    {/if}
                </div>
            {/if}
            <div class="fcal_form_item fcal_submit">
                {#if !hasPaymentItem()}
                    <button disabled="{submitting}" type="submit"
                            class="fcal_btn_submit { submitting ? 'fcal_btn_submitting' : '' }">
                        {i18('Schedule Meeting')}
                    </button>
                {:else}
                    <button disabled="{submitting}" type="submit" class="fcal_btn_submit { submitting ? 'fcal_btn_submitting' : '' }">
                        {i18('Continue to Payments')}
                    </button>
                {/if}
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
    import {Pulse} from 'svelte-loading-spinners';
    import {util, i18, getErrorText} from '../util.js';
    import {createEventDispatcher} from 'svelte';
    import {intros} from "svelte/internal";
    import Payments from "./Payments.svelte";
    import LocationField from "./_LocationField.svelte";

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

    function hasPaymentItem() {
        return !!(appData?.payment_items && appData?.payment_methods?.template);
    }

    let getSubTotal = (items) => {
        let subtotal = 0;
        for (let item of items) {
            subtotal += parseFloat(item.value);
        }
        return subtotal;
    }

    function submitForm(e) {
        const formFields = e.target.elements;
        const selectedMethod = (formFields?.stripe_payment_method?.value) ? formFields.stripe_payment_method.value : '';
        dispatch('onPaymentsVisibilityChanged', true);
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
                if (res.data?.redirect_to) {
                    window.location.href = res.data.redirect_to;
                    return;
                }

                if (res.data?.actionName === 'custom') {
                    window.dispatchEvent(new CustomEvent('fluent_booking_payment_next_action_' + res.data.nextAction, {
                        detail: {
                            form: e.target,
                            response: res
                        }
                    }));
                    return;
                }

                dispatch('bookingConfirmed', res);
            })
            .catch(err => {
                errors = getErrorText(err.response);
            })
            .finally(() => {
                submitting = false;
            });
    }
</script>
