<div class="fcal_booking_form_wrap" id="fcal_booking_form_wrap">
    <div class="fcal_booking_form">
        {#if submitting}
            <div class="fcal_loading_dates">
                <div class="fcal_loading_dates_inner">
                    <Pulse color="#0060e6"/>
                </div>
            </div>
        {/if}
        <slot name="before_form"></slot>
        <form on:submit|preventDefault={submitForm}>
            {#each formFields as field}
                {#if field.enabled}
                    <div class="fcal_form_item">
                        {#if field.name === 'location'}
                            <div class="fcal_input_content">
                                {#if field.label}
                                    <div class="fcal_input_label">
                                        {field.label}
                                        {#if field.required}<span>*</span>{/if}
                                    </div>
                                {/if}
                                <LocationField {appData} field={field} bind:form="{form}"/>
                            </div>
                        {:else if field.type == 'multi-guests'}
                            <div class="fcal_input_content">
                                {#if field.label}
                                    <div class="fcal_input_label">
                                        {field.label}
                                        {#if field.required}<span>*</span>{/if}
                                    </div>
                                {/if}
                                <MultiGuests {appData} field={field} bind:form="{form}" />
                            </div>

                        {:else}
                            <label class="fcal_input_content">
                                {#if field.label}
                                    <div class="fcal_input_label">
                                        {#if !( field.type === 'checkbox' || (field.type === 'payment' && appData?.slot?.type === 'free'))}
                                            {field.label}
                                            {#if field.required}<span>*</span>{/if}
                                        {/if}
                                    </div>
                                {/if}
                                {#if field.type === 'text'}
                                    <div class={'fcal_input_wrap fcal_field_name_'+field.name}>
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
                                {:else if field.type === 'phone'}
                                    <PhoneFieldSkeleton field={field} form="{form}"/>
                                {:else if field.type === 'textarea'}
                                    <textarea placeholder="{field.placeholder}" disabled="{field.disabled}"
                                              class="fcal_input" bind:value={form[field.name]}/>
                                {:else if field.type === 'checkbox'}
                                    <label class="fcal_custom_checkbox">
                                        <input type="checkbox" bind:checked={form[field.name]} />
                                        <span>{field.label}</span>
                                        <span class="checkbox_mark"></span>
                                    </label>
                                {:else if field.type === 'radio'}
                                    {#each field.options as option}
                                        <label class="fcal_radio_group">
                                            {option}
                                            <input type="radio" bind:group={form[field.name]} value={option}>
                                            <span class="fcal_radio_icon"></span>
                                        </label>
                                    {/each}
                                {:else if field.type === 'dropdown'}
                                    <select bind:value={form[field.name]}>
                                        <option value="" disabled selected>{field.placeholder}</option>
                                        {#each field.options as option (option)}
                                            <option value={option}>{option}</option>
                                        {/each}
                                    </select>
                                {:else if field.type === 'checkbox-group'}
                                    {#each field.options as option (option)}
                                        <label class="fcal_checkbox_group fcal_custom_checkbox">
                                            <input type="checkbox" bind:group={form[field.name]} value={option}/>{option}
                                            <span class="checkbox_mark"></span>
                                        </label>
                                    {/each}
                                {:else if field.type === 'payment' && appData?.slot?.type === 'paid'}
                                    <Payments field={field}/>
                                {:else if field.type === 'hidden' }
                                    <input type="hidden" bind:value={form[field.name]}/>
                                {/if}
                            </label>
                        {/if}
                    </div>
                {/if}
            {/each}

            {#if !appData.is_fluentform}
                <!--{/if}-->
                {#if hasPaymentItem() && appData.payment_items}
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
                            {appData.i18n.Schedule_Meeting}
                        </button>
                    {:else}
                        <button disabled="{submitting}" type="submit"
                                class="fcal_btn_submit { submitting ? 'fcal_btn_submitting' : '' }">
                            {appData.i18n.Continue_to_Payments}
                        </button>
                    {/if}
                </div>
            {/if}
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
    import Payments from "./Payments.svelte";
    import LocationField from "./_LocationField.svelte";
    import MultiGuests from "./_MultiGuests.svelte";
    import PhoneFieldSkeleton from "./PhoneFieldSkeleton.svelte";

    export let timezone;
    export let duration;
    export let formFields;
    export let spot;
    export let slot;

    export let appData;

    export let form;

    let submitting = false;

    let dispatch = createEventDispatcher();

    let errors = '';

    const currentUrl = window.location.href;

    setTimeout(() => {
        const wrap    = document.querySelector(".fcal_date_event_details.is_active .fcal_booking_form_wrap");
        const sidebar = document.querySelector(".fcal_calendar_inner.fcal_day_selected.fcal_spot_selected .fcal_side");
        if(!wrap) {
            return;
        }
        const adjustHeight  = wrap.offsetHeight + 135;
        const calendarInner = document.querySelector(".fcal_calendar_inner.fcal_day_selected.fcal_spot_selected");

        if (sidebar && sidebar.offsetHeight > adjustHeight) {
            calendarInner.style.height = sidebar.offsetHeight + 'px';
        } else {
            calendarInner.style.height = adjustHeight + 'px';
        }
    }, 100)

    function hasPaymentItem() {
        return !!(slot.total_payment);
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
        const postdata = {
            ...form,
            timezone,
            duration,
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
                    if (res.data?.data?.payment_method) {
                        dispatch('onPaymentsVisibilityChanged', true);
                    }
                    if (res.data?.intent?.errors) {
                        errors = getErrorText(res.data?.intent?.errors);
                        return;
                    }
                    window.dispatchEvent(new CustomEvent('fluent_booking_payment_next_action_' + res.data.nextAction, {
                        detail: {
                            form: e.target,
                            response: res
                        }
                    }));
                    if (hasPaymentItem) {
                        const calendar = document.getElementsByClassName("fcal_calendar_inner")[0];
                        setTimeout(() => {
                            calendar.style.height = 'auto';
                        }, 100);
                    }
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
