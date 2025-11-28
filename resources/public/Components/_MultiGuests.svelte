<div class="fcal_input_content">
    {#if (field.label && form[field.name].length) || field.required}
        <div class="fcal_input_label">
            {field.label}
            {#if field.required}
                <span>*</span>
            {/if}
        </div>
    {/if}
    <div class="fcal_input_wrap fcal_input_multi_guests_wrap">
        {#each form[field.name] as guest, index}
            <div class="fcal_multi_guest_input">
                {#if isMultiGuests}
                    <div class="fcal_group_input">
                        <input type="text" placeholder={i18('Name')} 
                            aria-required={field.required} bind:value={guest.name}>
                        <input type="email" placeholder={i18('Email')} 
                            aria-required={field.required} bind:value={guest.email}>
                        <button type="button" on:click={(() => handleRemoveGuest(index))}>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48px" height="48px">
                                <path d="M 24 4 C 20.491685 4 17.570396 6.6214322 17.080078 10 L 10.238281 10 A 1.50015 1.50015 0 0 0 9.9804688 9.9785156 A 1.50015 1.50015 0 0 0 9.7578125 10 L 6.5 10 A 1.50015 1.50015 0 1 0 6.5 13 L 8.6386719 13 L 11.15625 39.029297 C 11.427329 41.835926 13.811782 44 16.630859 44 L 31.367188 44 C 34.186411 44 36.570826 41.836168 36.841797 39.029297 L 39.361328 13 L 41.5 13 A 1.50015 1.50015 0 1 0 41.5 10 L 38.244141 10 A 1.50015 1.50015 0 0 0 37.763672 10 L 30.919922 10 C 30.429604 6.6214322 27.508315 4 24 4 z M 24 7 C 25.879156 7 27.420767 8.2681608 27.861328 10 L 20.138672 10 C 20.579233 8.2681608 22.120844 7 24 7 z M 11.650391 13 L 36.347656 13 L 33.855469 38.740234 C 33.730439 40.035363 32.667963 41 31.367188 41 L 16.630859 41 C 15.331937 41 14.267499 40.033606 14.142578 38.740234 L 11.650391 13 z M 20.476562 17.978516 A 1.50015 1.50015 0 0 0 19 19.5 L 19 34.5 A 1.50015 1.50015 0 1 0 22 34.5 L 22 19.5 A 1.50015 1.50015 0 0 0 20.476562 17.978516 z M 27.476562 17.978516 A 1.50015 1.50015 0 0 0 26 19.5 L 26 34.5 A 1.50015 1.50015 0 1 0 29 34.5 L 29 19.5 A 1.50015 1.50015 0 0 0 27.476562 17.978516 z"/>
                            </svg>
                        </button>
                    </div>
                {:else}
                    <input name={form[field.name]} type="email" placeholder={i18('Email')} 
                        aria-invalid={field.required && !guest} aria-required={field.required} bind:value={guest}>
                    <button type="button" on:click={(() => handleRemoveGuest(index))}>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48px" height="48px">
                            <path d="M 24 4 C 20.491685 4 17.570396 6.6214322 17.080078 10 L 10.238281 10 A 1.50015 1.50015 0 0 0 9.9804688 9.9785156 A 1.50015 1.50015 0 0 0 9.7578125 10 L 6.5 10 A 1.50015 1.50015 0 1 0 6.5 13 L 8.6386719 13 L 11.15625 39.029297 C 11.427329 41.835926 13.811782 44 16.630859 44 L 31.367188 44 C 34.186411 44 36.570826 41.836168 36.841797 39.029297 L 39.361328 13 L 41.5 13 A 1.50015 1.50015 0 1 0 41.5 10 L 38.244141 10 A 1.50015 1.50015 0 0 0 37.763672 10 L 30.919922 10 C 30.429604 6.6214322 27.508315 4 24 4 z M 24 7 C 25.879156 7 27.420767 8.2681608 27.861328 10 L 20.138672 10 C 20.579233 8.2681608 22.120844 7 24 7 z M 11.650391 13 L 36.347656 13 L 33.855469 38.740234 C 33.730439 40.035363 32.667963 41 31.367188 41 L 16.630859 41 C 15.331937 41 14.267499 40.033606 14.142578 38.740234 L 11.650391 13 z M 20.476562 17.978516 A 1.50015 1.50015 0 0 0 19 19.5 L 19 34.5 A 1.50015 1.50015 0 1 0 22 34.5 L 22 19.5 A 1.50015 1.50015 0 0 0 20.476562 17.978516 z M 27.476562 17.978516 A 1.50015 1.50015 0 0 0 26 19.5 L 26 34.5 A 1.50015 1.50015 0 1 0 29 34.5 L 29 19.5 A 1.50015 1.50015 0 0 0 27.476562 17.978516 z"/>
                        </svg>
                    </button>
                {/if}
            </div>
        {/each}
        {#if form[field.name].length < getLimit() - 1}
            <button
                type="button"
                class="fcal_add_guest_btn"
                on:click={(() => handleAddGuest())}>
                {'+ ' + (form[field.name].length === 0 ? i18('Add guest') : i18('Add another'))}
            </button>
        {/if}
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
    import { i18 } from '../util.js';

    export let slot;
    export let spot;
    export let field;
    export let form;
    export let validating;
    export let hasError;
    export let quantity;

    let errorText = false;

    const isMultiGuests = ['group', 'group_event'].includes(slot.event_type);

    if (!form[field.name]) {
        form[field.name] = [];
    }

    $: form[field.name], maybeUpdateQuantity();

    $: if (validating || !validating) {
        updateValidationError();
    }

    function updateValidationError() {
        errorText = false;
        if (field.required && form[field.name].length == 0) {
            errorText = i18('This field is required.');
        }
    }

    function maybeUpdateQuantity() {
        if (isMultiGuests) {
            const totalBooking = form[field.name].filter(guest => guest.name && guest.email).length || 0;
            quantity = totalBooking + 1;
        }
    }

    function getLimit() {
        if (isMultiGuests) {
            return Math.min(field.limit, spot.remaining);
        }
        return field.limit;
    }

    function handleRemoveGuest(index) {
        form[field.name].splice(index, 1);
        form[field.name] = [...form[field.name]];
        reRenderHeight();
    }

    function handleAddGuest() {
        if (isMultiGuests) {
            form[field.name] = [...form[field.name], { name: '', email: '' }];
        } else {
            form[field.name] = [...form[field.name], ''];
        }
        reRenderHeight();
    }

    function reRenderHeight() {
        return setTimeout(() => {
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
        }, 400)
    }
</script>