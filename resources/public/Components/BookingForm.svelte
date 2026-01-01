<div class="fcal_booking_form_wrap" id="fcal_booking_form_wrap">
    <div class="fcal_booking_form">
        {#if submitting}
            <div class="fcal_loading_dates">
                <div class="fcal_loading_dates_inner">
                    <Pulse color={primaryColor}/>
                </div>
            </div>
        {/if}
        <slot name="before_form"></slot>
        <form on:submit|preventDefault={submitForm}>
            {#each formFields as field}
                {#if field.enabled}
                    <div class="fcal_form_item">
                        {#if field.name === 'location'}
                            <LocationField {appData} {field} {validating} {hasError} bind:form={form}/>
                        {:else if field.type == 'multi-guests'}
                            <MultiGuests {slot} {spot} {field} {validating} {hasError} bind:quantity={quantity} bind:form={form} />
                        {:else if field.type === 'multi-select' }
                            <MultiSelect {field} {validating} {hasError} bind:form={form} />
                        {:else if field.type === 'file'}
                            <FileInput {slot} {field} {validating} {hasError} bind:form={form} />
                        {:else if field.type === 'hidden'}
                            {#if !form[field.name]}
                                {form[field.name] = field.default_value}
                            {/if}
                            <input name={field.name} type="hidden" bind:value={form[field.name]}/>
                        {:else}
                            <label class="fcal_input_content" aria-label={field?.label} id="{field.name}-label">
                                {#if field.label}
                                    <div class="fcal_input_label">
                                        {#if shouldRenderLabel(field)}
                                            {field.label}
                                            {#if field.required && field.type != 'payment'}
                                                <span>*</span>
                                            {/if}
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
                                        <input disabled={field.disabled} class="fcal_input" id={'fcalInputID'+field.name} type="text" placeholder={field.placeholder}
                                            aria-required={field.required} aria-invalid={field.required && !form[field.name]} bind:value={form[field.name]}/>
                                    </div>
                                {:else if field.type === 'email'}
                                    <input disabled={field.disabled} class="fcal_input" type="email" placeholder={field.placeholder}
                                        aria-required={field.required} aria-invalid={field.required && !form[field.name]} bind:value={form[field.name]}
                                    />
                                {:else if field.type === 'number'}
                                    <input disabled={field.disabled} class="fcal_input" type="number" placeholder={field.placeholder}
                                        aria-required={field.required} aria-invalid={field.required && !form[field.name]} bind:value={form[field.name]}/>
                                {:else if field.type === 'phone'}
                                    <PhoneFieldSkeleton field={field} bind:form={form}/>
                                {:else if field.type === 'textarea'}
                                    <textarea class="fcal_input" placeholder={field.placeholder} disabled={field.disabled}
                                        aria-required={field.required} aria-invalid={field.required && !form[field.name]} bind:value={form[field.name]}/>
                                {:else if field.type === 'checkbox'}
                                    <label class="fcal_custom_checkbox" aria-label={field.label}>
                                        <input type="checkbox"
                                            checked={form[field.name] == "Yes"}
                                            on:change={() => form[field.name] = form[field.name] == "Yes" ? "No" : "Yes"}/>
                                        <span>{field.label}</span>
                                        <span class="checkbox_mark"></span>
                                    </label>
                                {:else if field.type === 'terms-and-conditions'}
                                <label class="fcal_custom_checkbox fcal_terms_conditions" aria-label={field.label}>
                                    <input type="checkbox"
                                        checked={form[field.name] == "Accepted"}
                                        on:change={() => form[field.name] = form[field.name] == "Accepted" ? "Not Accepted" : "Accepted"}/>
                                    <span>{@html field.terms_and_conditions}</span>
                                    <span class="checkbox_mark"></span>
                                </label>
                                {:else if field.type === 'radio'}
                                    <div role="radiogroup" aria-labelledby="{field.name}-label">
                                        {#each field.options as option}
                                            <label class="fcal_radio_group" for={field.name+option} aria-label={option}>
                                                <input type="radio" bind:group={form[field.name]}
                                                    id={field.name+option} value={option}>
                                                {option}<span class="fcal_radio_icon"></span>
                                            </label>
                                        {/each}
                                    </div>
                                {:else if field.type === 'dropdown'}
                                    <select aria-required={field.required} bind:value={form[field.name]}>
                                        <option value="" disabled selected>{field.placeholder}</option>
                                        {#each field.options as option (option)}
                                            <option value={option}>{option}</option>
                                        {/each}
                                    </select>
                                {:else if field.type === 'checkbox-group'}
                                    {#each field.options as option}
                                        <label class="fcal_checkbox_group fcal_custom_checkbox"
                                            for={field.name+option} aria-label={option}>
                                            <input type="checkbox" bind:group={form[field.name]}
                                                id={field.name+option} value={option}/>
                                                {option}
                                            <span class="checkbox_mark"></span>
                                        </label>
                                    {/each}
                                {:else if field.type === 'date' }
                                    <div class="fcal_date_field">
                                        <DateInput
                                            format={getDateFormat(field.date_format)}
                                            min={new Date(field.min_date || '1900-01-01')}
                                            max={field.max_date ? new Date(field.max_date) : undefined}
                                            dynamicPositioning={true}
                                            closeOnSelection={true}
                                            placeholder={field.placeholder}
                                            on:select={(e) => handleDateChange(e, field)}
                                            locale={{
                                                months: Object.values(dateTimei18?.months),
                                                weekdays: Object.values(dateTimei18?.weekdaysShort),
                                                weekStartsOn: startDayIndex
                                            }}
                                        />
                                        <svg class="calendar_date_icon" width="100%" height="100%" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M21 10H3M16 2V6M8 2V6M7.8 22H16.2C17.8802 22 18.7202 22 19.362 21.673C19.9265 21.3854 20.3854 20.9265 20.673 20.362C21 19.7202 21 18.8802 21 17.2V8.8C21 7.11984 21 6.27976 20.673 5.63803C20.3854 5.07354 19.9265 4.6146 19.362 4.32698C18.7202 4 17.8802 4 16.2 4H7.8C6.11984 4 5.27976 4 4.63803 4.32698C4.07354 4.6146 3.6146 5.07354 3.32698 5.63803C3 6.27976 3 7.11984 3 8.8V17.2C3 18.8802 3 19.7202 3.32698 20.362C3.6146 20.9265 4.07354 21.3854 4.63803 21.673C5.27976 22 6.11984 22 7.8 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                {:else if field.type === 'payment' && slot.type === 'paid' && hasPaymentItem()}
                                    <Payments {field} bind:form={form} bind:discount={discount} slotId={slot.id} {duration} {quantity}/>
                                {/if}
                                {#if field.help_text}
                                    <p class="fcal_help_text">{field.help_text}</p>
                                {/if}
                                {#if hasError && field.error}
                                    <div class="fcal_validation_error">
                                        <ErrorIcon/>
                                        <p>{field.error}</p>
                                    </div>
                                {/if}
                            </label>
                        {/if}
                    </div>
                {/if}
            {/each}

            {#if !appData.is_fluentform}
                {#if hasPaymentItem()}
                    <div class="fluent_booking_payment_processor" style="display:none;">
                        <h3 class="label">{i18('Total Payment')}
                            : {@html getCurrencyFormat(getTotal(appData?.payment_items, quantity, discount))}</h3>
                        {#if appData?.payment_methods?.template}
                            <div class="fcal_form_payment_item">
                                {@html appData.payment_methods.template}
                            </div>
                        {/if}
                    </div>
                {/if}
                <div class="fcal_form_item fcal_submit">
                    {#if hasPaymentItem() && getTotal(appData?.payment_items, quantity, discount) && form['payment_method'] != 'offline'}
                        <button disabled={submitting} type="submit"
                                class="fcal_btn_submit { submitting ? 'fcal_btn_submitting' : '' }">
                            {appData.i18n.Continue_to_Payments}
                        </button>
                    {:else}
                        <button disabled={submitting} type="submit"
                                class="fcal_btn_submit { submitting ? 'fcal_btn_submitting' : '' }">
                            {slot.settings?.submit_button_text || appData.i18n.Schedule_Meeting}
                        </button>
                    {/if}
                </div>
            {/if}
        </form>
        {#if errors}
            <div class="fcal_errors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-900 dark:text-blue-200" data-testid="info" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" x2="12" y1="16" y2="12"></line>
                    <line x1="12" x2="12.01" y1="8" y2="8"></line>
                </svg>
                <div>
                    {@html errors}
                </div>
            </div>
        {/if}
    </div>
</div>
<script>
    import { Pulse } from 'svelte-loading-spinners';
    import { util, i18, getErrorText, getCurrencyFormat } from '../util.js';
    import { createEventDispatcher, onMount } from 'svelte';
    import { DateInput } from 'date-picker-svelte';
    import Payments from "./Payments.svelte";
    import LocationField from "./_LocationField.svelte";
    import MultiGuests from "./_MultiGuests.svelte";
    import MultiSelect from "./_MultiSelect.svelte";
    import PhoneFieldSkeleton from "./PhoneFieldSkeleton.svelte";
    import FileInput from "./_FileInput.svelte";
    import ErrorIcon from "./Icons/ErrorIcon.svelte";

    export let timezone;
    export let duration;
    export let quantity;
    export let formFields;
    export let spots;
    export let spot;
    export let slot;
    export let occurrence;

    export let appData;

    export let form;

    let errors = '';

    let hasError = false;

    let validating = false;

    let submitting = false;

    let discount = 0;

    let primaryColor = 'var(--fcal_primary_color)';

    const currentUrl = window.location.href;

    const dateFormatter = appData.date_formatter;

    const dateTimei18 = window.fluentCalendarPublicVars.i18?.date_time_config;

    const startDayIndex = window.fluentCalendarPublicVars.start_day == 'sun' ? 0 : 1;

    let dispatch = createEventDispatcher();

    if (!isReschedulingForm(formFields)) {
        let excludeNames = ['cancellation_reason', 'rescheduling_reason'];
        formFields = formFields.filter(field => !excludeNames.includes(field.name));
    }

    function isReschedulingForm(fields) {
        return fields.some(field => field.name === 'rescheduling_hash' && field.enabled);
    }

    function getDateFormat(dateFormat) {
        let format = dateFormat || dateFormatter;
        format = format.replace(/MMMM+/g, 'MM');
        format = format.replace(/MMM+/g, 'MM');
        format = format.replace(/mm/gi, 'mm');
        format = format.replace(/dd/gi, 'dd');
        format = format.replace(/(?<!m)m(?!m)/gi, 'MM');
        format = format.replace(/(?<!d)d(?!d)/gi, 'dd');
        format = format.replace(/y{4}/gi, 'yyyy');
        format = format.replace(/y{2}/gi, 'yy');
        return format;
    }

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
        if (!appData.payment_items || slot.total_payment == 0) {
            return false;
        }
        return !!(slot.total_payment);
    }

    function getStartDate() {
        if (spots && spots.length > 1) {
            return spots.map(spot => spot.start);
        }
        return spot.start;
    }

    let getTotal = (items, qty, dis) => {
        return getSubTotal(items, qty) - dis;
    }

    let getSubTotal = (items, qty) => {
        if (appData.multi_payment_items) {
            return parseFloat(slot.total_payment) * qty;
        }

        let subtotal = 0;
        for (let item of items) {
            subtotal += parseFloat(item.value);
        }
        return subtotal * qty;
    }

    function handleError(errors) {
        hasError = false;
        validating = !validating;
        formFields.forEach((field) => {
            field.error = null;
        })
        for (let error in errors) {
            const [fieldName, errorType] = error.split('.');
            if (fieldName === 'location_config') {
                hasError = true;
                continue;
            }
            const field = formFields.find((f) => f.name === fieldName);
            if (field && errorType === 'required') {
                hasError = true;
                field.error = i18('This field is required.');
            }
        };
    }

    function validateForm() {
        errors = '';
        hasError = false;
        let paymentError = false;
        validating = !validating;
        formFields.forEach((field) => {
            field.error = null;
            if (field.enabled && field.required) {
                const value = form[field.name];
                const isEmptyField = !value;
                const isCheckbox = field.type === 'checkbox';
                const isPayment = field.name === 'payment_method';
                const isTerms = field.type === 'terms-and-conditions';
                const isSpecialField = isPayment || field.name === 'location';

                if (isEmptyField) {
                    if (!isSpecialField) {
                        hasError = true;
                        field.error = i18('This field is required.');
                    }
                    if (isPayment && hasPaymentItem()) {
                        paymentError = true;
                    }
                } else if (isCheckbox && value != 'Yes') {
                    hasError = true;
                    field.error = i18('This field is required.');
                } else if (isTerms && value != 'Accepted') {
                    hasError = true;
                    field.error = i18('You must accept the terms and conditions.');
                }
            }
        });
        if (hasError) {
            errors = i18('Please fill up the required data');
        } else if (paymentError) {
            hasError = true;
            errors = i18('Please select a valid payment method');
        }
        return !hasError;
    };

    function submitForm(e) {
        if (submitting || !validateForm()) {
            return;
        }

        const postdata = {
            ...form,
            timezone,
            duration,
            start_date: getStartDate(),
            event_id: slot.id,
            source_url: currentUrl,
            action: 'fluent_cal_schedule_meeting'
        }

        if (slot.settings?.recurring_config?.enabled && occurrence) {
            postdata.recurring_count = occurrence;
        }

        errors = '';
        submitting = true;
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
                if (err.response?.errors) {
                    handleError(err.response.errors);
                }
                errors = getErrorText(err.response);
            })
            .finally(() => {
                submitting = false;
            });
    }

    function handleDateChange(e, field) {
        const selectedDate = e.detail;
        const dateFormat = field.date_format || dateFormatter;
        const formatterDate = util.dayjs(selectedDate).format(dateFormat);
        form[field.name] = formatterDate;
    }

    function shouldRenderLabel(field) {
        return !(['checkbox', 'terms-and-conditions'].includes(field.type) || (field.type === 'payment' && !hasPaymentItem()));
    }

    onMount(() => {
        if (!appData.is_fluentform) {
            setTimeout(() => {
                document.getElementById('fcalInputIDname').focus();
            }, 500);
        }

        setTimeout(() => {
            const allDateFields = document.querySelectorAll('.fcal_date_field .date-time-field > input');
            allDateFields.forEach(field => field.setAttribute('readonly', true));
        }, 500);
    });

</script>
