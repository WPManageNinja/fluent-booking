<script>
    import { util, i18, getCurrencyFormat } from './util';
    import { onMount, createEventDispatcher } from "svelte";
    import DayPickerApp from "./Calendar/DatePickerApp.svelte";
    import BookingForm from "./Components/BookingForm.svelte";
    import FcalSkeleton from './Components/FcalSkeleton.svelte';
    import Summary from "./Fluentform/Summary.svelte";

    window['fcal_translate'] = i18;

    let wrapDom;
    let wrapperClass = '';

    export let appData;
    export let handleBack;
    export let duration = appData.slot.duration;

    const slot = appData.slot;
    const author = appData.author_profile;
    const titleTag = appData.title_tag || 'h1';
    const teamMembers = appData.team_member_profiles;
    const isFluentform = appData.is_fluentform;
    const dateFormatter = appData.date_formatter;
    const isRescheduling = appData.rescheduling == 'yes';
    const isSingleGuest = slot.event_type === 'single';
    const isDisplaySpots = slot.is_display_spots || false;
    const isMultiBooking = slot.settings?.multiple_booking?.enabled || false;
    const isMultiBookingAllow = isMultiBooking && isSingleGuest && !isRescheduling;
    const multiBookingLimit = slot.settings?.multiple_booking?.limit || 5;
    const availableDurations = slot.settings?.multi_duration?.available_durations || [];

    let form = getPreSelectsFormData();

    let appReady = false;
    let component = null;
    let isBookingDone = false;
    let bookingConfirmationHtml = '';
    let limitReachedError = '';
    let timeFormat = getTimeFormat(slot.time_format);

    let skipCalendar = !!slot.pre_selects?.time;
    let selectedDate = false;
    let selectedDateTime = {};
    let selectedDateTimes = [];
    let isLoadingDates = false;

    let showingPayments = false;
    let timezone = '';
    let quantity = 1;
    let wrapperWidth = 800;

    let fluentFormDateTimeSelected = {};

    const dispatch = createEventDispatcher();

    function handleBackClick() {
        dispatch('handleBack');
    }

    function getPreSelectsFormData() {
        const currentPerson = window.fluentCalendarPublicVars.current_person || {};
        const utm_data = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        const urlParams = new URLSearchParams(window.location.search);

        const formFields = {};
        appData.form_fields.forEach((field) => {
            const fieldName = field.name?.replace(/custom_/i, '').replace(/-/g, '_');
            if (fieldName) {
                formFields[fieldName] = field;
            }
        })
        for (const [key, value] of urlParams.entries()) {
            if (key.startsWith('invitee_')) {
                const fieldKey = key.replace(/invitee_/i, '');
                if (formFields[fieldKey]) {
                    const field = formFields[fieldKey];
                    if (!currentPerson[field.name]) {
                        currentPerson[field.name] = value;
                    }
                }
            } else if (utm_data.includes(key)) {
                currentPerson[key] = value;
            }
        }
        return currentPerson;
    }

    function checkDevice() {
        let timeout = 0;
        let isMultiLayout = document.querySelector('.ff_conv_media_holder');
        let conversationalPage = document.getElementsByClassName('ff_conversation_page_body');
        if (isFluentform && !conversationalPage) {
            timeout = 2000;
        }
        setTimeout(() => {
            wrapperWidth = wrapDom?.parentNode?.offsetWidth || 800;
            if (wrapperWidth >= 900) {
                wrapperClass = 'fcal_on_lg';
            } else if (wrapperWidth >= 800) {
                wrapperClass = 'fcal_on_md';
            } else if (wrapperWidth >= 600) {
                wrapperClass = 'fcal_on_sm';
                if (conversationalPage.length && appData.disable_author) {
                    wrapperClass = 'fcal_on_md';
                }
            } else if (wrapperWidth && wrapperWidth < 600) {
                wrapperClass = 'fcal_on_xs fcal_mobile';
            }

            if (conversationalPage && ((window.innerWidth && window.innerWidth < 600) || isMultiLayout)) {
                wrapperClass = 'fcal_on_xs fcal_mobile';
            }
        }, timeout)
    }

    onMount(() => {
        const guessTimezone = util.dayjs.tz.guess();
        if (slot.settings?.lock_timezone?.enabled) {
            timezone = slot.settings.lock_timezone.timezone || guessTimezone;
        } else {
            timezone = guessTimezone;
        }
        appReady = true;
        checkDevice();
    });

    window.onresize = function () {
        checkDevice();
    };

    function onPaymentsVisibilityChanged(visibility) {
        showingPayments = visibility;
    }

    function spotSelected(spot) {
        selectedDateTime = spot;
    }
 
    function spotClicked(spot) {
        limitReachedError = '';
        if (!spot) {
            selectedDateTimes = [];
            return;
        }
        if (!isMultiBookingAllow) {
            selectedDateTimes = [spot];
            return;
        }
        const indx = selectedDateTimes.findIndex(item => item.start === spot.start);
        if (indx > -1) {
            selectedDateTimes.splice(indx, 1);
        } else if (selectedDateTimes.length < multiBookingLimit) {
            selectedDateTimes.push(spot);
        } else if (selectedDateTimes.length >= multiBookingLimit) {
            limitReachedError = i18(`You can only book up to ${multiBookingLimit} slots at a time`);
        }

        quantity = selectedDateTimes.length || 1;
        selectedDateTimes = [...selectedDateTimes];
    }

    function durationSelected(value) {
        duration = value;
        maybeUpdatePayment(value);
    }

    function getDuration(duration) {
        const durationLookup = appData.duration_lookup;
        return durationLookup[duration] ?? formatDuration(duration);
    }

    function getMultiDuration(duration) {
        const durationLookup = appData.multi_duration_lookup;
        return durationLookup[duration] ?? formatDuration(duration);
    }

    function formatDuration(duration) {
        const days = Math.floor(duration / 1440);
        const hours = Math.floor((duration % 1440) / 60);
        const minutes = duration % 60;

        let formattedDuration = [];
        if (days > 0) {
            const unit = days > 1 ? i18('Days') : i18('Day');
            formattedDuration.push(days + ' ' + unit);
        }
        if (hours > 0) {
            const unit = hours > 1 ? i18('Hours') : i18('Hour');
            formattedDuration.push(hours + ' ' + unit);
        }
        if (minutes > 0 || formattedDuration.length === 0) {
            const unit = minutes > 1 ? i18('Minutes') : i18('Minute');
            formattedDuration.push(minutes + ' ' + unit);
        }
        return formattedDuration.join(' ');
    }

    function getTimeFormat(format) {
        if (format === '24') {
            return 'HH:mm';
        }
        return 'hh:mma';
    }

    function formatHours(e) {
        slot.time_format = e;
        timeFormat = getTimeFormat(e);
    }

    function maybeDisplayDate(date) {
        const startDate = util.dateTimeI18(date.start, dateFormatter);
        const endData = util.dateTimeI18(date.end, dateFormatter);
        if (startDate != endData) {
            return ', ' + startDate;
        }
        return '';
    }
    function maybeUpdatePayment(duration) {
        if (appData.multi_payment_items) {
            slot.total_payment = appData.multi_payment_items[duration]?.value;
        }
        if (appData.multi_payment_woo_ids) {
            slot.total_payment = appData.multi_payment_woo_ids[duration]?.value;
        }
        if (appData.multi_payment_cart_ids) {
            slot.total_payment = appData.multi_payment_cart_ids[duration]?.value;
        }
    }

    function handleBookingConfirmation(confirmation) {
        // Check if there is custom redirect url
        if (confirmation.redirect_url) {
            window.location.href = confirmation.redirect_url;
            return;
        }

        bookingConfirmationHtml = confirmation.response_html;
        // remove height css to .fcal_calendar_inner class
        const calendar = document.getElementsByClassName("fcal_calendar_inner")[0];
        // check if pushState is supported
        if (window.history.pushState) {
            // add url params to the current url with browser history api
            const urlParams = new URLSearchParams();
            urlParams.set('fluent-booking', 'booking');
            urlParams.set('type', 'confirmation');
            urlParams.set('meeting_hash', confirmation.booking_hash);
            window.history.pushState({}, '', `${window.location.pathname}?${urlParams.toString()}`);
        }
        calendar.style.height = 'auto';
        isBookingDone = true;
    }

    function dayClicked(day) {
        selectedDate = day;
        checkDevice();
    }

    function getAdditionalLabel(qty) {
        if (qty > 1) {
            return i18('per guest');
        }
        return '';
    }

    function selectNewDate() {
        selectedDate = null;
    }

    function resetSelection() {
        if (wrapperWidth < 800) {
            selectedDate = null;
        }
        limitReachedError = '';
        quantity = 1;
        selectedDateTime = {};
        selectedDateTimes = [];
        component.style.height = 'auto';
        summaryDetailsHeightHandle();
    }

    function fluentFormDateHandle(e) {
        selectedDateTime = e.detail;
        fluentFormDateTimeSelected = e.detail;
        summaryDetailsHeightHandle();
    }

    function summaryDetailsHeightHandle() {
        setTimeout(() => {
            const eventDetails = document.querySelector('.fcal_calendar_inner.fcal_spot_selected.fcal_on_xs .fcal_date_wrapper.is_active .fcal_day_picker_wrap');
            const hasFluentform = isFluentform && document.querySelector('.fcal_calendar_inner.fcal_on_xs');
            const hasSelectedDateTime = selectedDateTime.start !== undefined;

            if (hasFluentform && hasSelectedDateTime) {
                eventDetails.style.position = 'absolute';
            } else if (hasFluentform) {
                document.querySelector('.fcal_calendar_inner.fcal_on_xs .fcal_date_wrapper .fcal_day_picker_wrap').style.position = 'relative';
            }
        }, 100)
    }


    let fluentFormInput = {};

    // detect change on form.location_config
    // and update the appData
    $: if (appData.is_fluentform && (form.location_config || selectedDateTime.start || !selectedDateTime.start)) {
        fluentFormInput = {
            id: appData.id,
            form: form,
            timezone: timezone,
            duration: duration,
            start_time: selectedDateTime.start
        };

        if (appData.isFFConversational) {
            appData.element.dispatchEvent(new CustomEvent('value.update', {
                detail: {
                    value: JSON.stringify(fluentFormInput)
                }
            }));
        }
    }

</script>
<div class="fcal_wrap">
    <div bind:this={wrapDom} class="fcal_holder" id={appData.id}>
        <div data-width="{wrapperWidth}px" bind:this={component}
            class="fcal_calendar_inner { isFluentform ? 'fcal_form_calendar' : ''} {selectedDate ? 'fcal_day_selected' : ''} { selectedDateTime.start ? 'fcal_spot_selected' : '' } {wrapperClass}">
            {#if isBookingDone}
                <div class="fcal_booking_confirmed">{@html bookingConfirmationHtml}</div>
            {:else }
                {#if !appData.disable_author}
                    <div class="fcal_side">
                        <div class="fcal_slot_wrapper">
                            {#if handleBack}
                                <div class="fcal_back">
                                    <div tabindex="0" on:click={handleBackClick} on:keypress={handleBackClick}
                                        class="fcal_back_btn" role="button"
                                        aria-label="{i18('Go to previous page')}">
                                        <svg height="512px" id="Layer_1" style="enable-background:new 0 0 512 512;"
                                            version="1.1" viewBox="0 0 512 512" width="512px" xml:space="preserve"
                                            xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"><polygon points="352,128.4 319.7,96 160,256 160,256 160,256 319.7,416 352,383.6 224.7,256 "/></svg>
                                    </div>
                                </div>
                            {/if}
                            {#if teamMembers}
                                <div class="fcal_author_wrapper">
                                    <div class="fcal_author_list">
                                        {#each teamMembers as member}
                                            <div class="fcal_author">
                                                <div class="fcal_author_avatar">
                                                    <img src="{member.avatar}" alt="{member.name}">
                                                    <div class="fcal_author_tooltip">
                                                        <span>{member.name}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        {/each}
                                    </div>
                                    <div class="fcal_author_name">
                                        {author.name}
                                    </div>
                                </div>
                            {:else}
                                <div class="fcal_author">
                                    <div class="fcal_author_avatar">
                                        <img src="{author.avatar}" alt="{author.name}">
                                    </div>
                                    <div class="fcal_author_name">
                                        {author.name}
                                    </div>
                                </div>
                            {/if}
                            {#if skipCalendar && isLoadingDates}
                                <FcalSkeleton rows={5}/>
                            {:else}
                                <div class="fcal_slot_info">
                                    {#if titleTag == 'h2'}
                                        <h2 aria-level="2" class="fcal_slot_heading">{slot.title}</h2>
                                    {:else if titleTag == 'h3' || isFluentform}
                                        <h3 aria-level="3" class="fcal_slot_heading">{slot.title}</h3>
                                    {:else if titleTag == 'h4'}
                                        <h4 aria-level="4" class="fcal_slot_heading">{slot.title}</h4>
                                    {:else}
                                        <h1 aria-level="1" class="fcal_slot_heading">{slot.title}</h1>
                                    {/if}
                                    {#if slot.settings?.requires_confirmation?.enabled}
                                        <div class="fcal_requires_confirmation fcal_icon_item">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                                                <path d="m9 12 2 2 4-4"></path>
                                            </svg>
                                            <span>{i18('Requires Confirmation')}</span>
                                        </div>
                                    {/if}
                                    <div class="slot_timing fcal_icon_item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                            <path d="M16.5 9C16.5 13.14 13.14 16.5 9 16.5C4.86 16.5 1.5 13.14 1.5 9C1.5 4.86 4.86 1.5 9 1.5C13.14 1.5 16.5 4.86 16.5 9Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M11.7825 11.3849L9.45753 9.99745C9.05253 9.75745 8.72253 9.17995 8.72253 8.70745V5.63245" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {#if slot.settings?.multi_duration?.enabled}
                                            {#if selectedDateTime.start}
                                                <span class="fcal_duration_title">{ getMultiDuration(duration) }</span>
                                            {:else}
                                                <div class="fcal_multi_duration">
                                                    {#each availableDurations as value}
                                                        <span
                                                            on:keypress={()=>durationSelected(value)}
                                                            on:click={()=>durationSelected(value)}
                                                            role="button" tabindex="0" class="fcal_duration {duration == value ? 'is_selected' : ''}">
                                                            { getMultiDuration(value) }
                                                        </span>
                                                    {/each}
                                                </div>
                                            {/if}
                                        {:else}
                                            <span class="fcal_duration_title">{ getDuration(slot.duration) }</span>
                                        {/if}
                                    </div>

                                    {#if slot.location_settings.length > 1}
                                        {#if slot.location_settings }
                                            <div class="fcal_multi_locations">
                                                <div class="fcal_multi_location_title">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin">
                                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                        <circle cx="12" cy="10" r="3"/>
                                                    </svg> {slot.location_settings.length} {i18('location options')}

                                                    <ul class="fcal_location_tooltip">
                                                        <li class="title">{i18('Select on the Next Step')}</li>
                                                        <li>
                                                            {@html slot.location_icon_html}
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        {/if}
                                    {:else}
                                        {#if slot.location_icon_html }
                                            {@html slot.location_icon_html}
                                        {/if}
                                    {/if}

                                    {#if !isFluentform && slot.total_payment && slot.total_payment != 0 }
                                        <div class="fcal_slot_payment_item">
                                            <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-ea893728="">
                                                <path fill="currentColor" d="M256 640v192h640V384H768v-64h150.976c14.272 0 19.456 1.472 24.64 4.288a29.056 29.056 0 0 1 12.16 12.096c2.752 5.184 4.224 10.368 4.224 24.64v493.952c0 14.272-1.472 19.456-4.288 24.64a29.056 29.056 0 0 1-12.096 12.16c-5.184 2.752-10.368 4.224-24.64 4.224H233.024c-14.272 0-19.456-1.472-24.64-4.288a29.056 29.056 0 0 1-12.16-12.096c-2.688-5.184-4.224-10.368-4.224-24.576V640h64z"></path><path fill="currentColor" d="M768 192H128v448h640V192zm64-22.976v493.952c0 14.272-1.472 19.456-4.288 24.64a29.056 29.056 0 0 1-12.096 12.16c-5.184 2.752-10.368 4.224-24.64 4.224H105.024c-14.272 0-19.456-1.472-24.64-4.288a29.056 29.056 0 0 1-12.16-12.096C65.536 682.432 64 677.248 64 663.04V169.024c0-14.272 1.472-19.456 4.288-24.64a29.056 29.056 0 0 1 12.096-12.16C85.568 129.536 90.752 128 104.96 128h685.952c14.272 0 19.456 1.472 24.64 4.288a29.056 29.056 0 0 1 12.16 12.096c2.752 5.184 4.224 10.368 4.224 24.64z"></path><path fill="currentColor" d="M448 576a160 160 0 1 1 0-320 160 160 0 0 1 0 320zm0-64a96 96 0 1 0 0-192 96 96 0 0 0 0 192z"></path>
                                            </svg> {@html ['woo', 'cart'].includes(slot.type) ? slot.total_payment : getCurrencyFormat(slot.total_payment)} {getAdditionalLabel(quantity)}
                                        </div>
                                    {/if}
                                    {#if selectedDateTimes.length > 0}
                                        {#each selectedDateTimes as selectedTime, indx}
                                            <div class="slot_time_range fcal_icon_item">
                                                {#if indx == 0}
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                        <path d="M6 1.5V3.75" stroke="#445164" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 1.5V3.75" stroke="#445164" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M2.625 6.8175H15.375" stroke="#445164" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.75 6.375V12.75C15.75 15 14.625 16.5 12 16.5H6C3.375 16.5 2.25 15 2.25 12.75V6.375C2.25 4.125 3.375 2.625 6 2.625H12C14.625 2.625 15.75 4.125 15.75 6.375Z" stroke="#445164" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.771 10.275H11.7778" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.771 12.525H11.7778" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.99661 10.275H9.00335" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.99661 12.525H9.00335" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.22073 10.275H6.22747" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.22073 12.525H6.22747" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                {/if}
                                                <span class="{indx > 0 ? 'fcal_multi_time' : ''}">
                                                    {util.dateTimeI18(selectedTime.start, timeFormat)}{maybeDisplayDate(selectedTime)}
                                                    - {util.dateTimeI18(selectedTime.end, timeFormat)}, {util.dateTimeI18(selectedTime.end, dateFormatter)}
                                                    {#if selectedDateTimes.length > 1}
                                                        <span class="fcal_inline_remove"
                                                            on:click={() => spotClicked(selectedTime)}
                                                            on:keypress={(e) => { spotClicked(selectedTime) }}>
                                                            +
                                                        </span>
                                                    {/if}
                                                </span>
                                            </div>
                                        {/each}
                                        {#if limitReachedError}
                                            <div class="fcal_time_limit_error fcal_icon_item">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 ltr:mr-2 rtl:ml-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" x2="12" y1="16" y2="12"></line><line x1="12" x2="12.01" y1="8" y2="8"></line></svg>
                                                <span>{limitReachedError}</span>
                                            </div>
                                        {/if}
                                        <div class="slot_time_range slot_timezone fcal_icon_item">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                <path d="M9 16.5C13.1421 16.5 16.5 13.1421 16.5 9C16.5 4.85786 13.1421 1.5 9 1.5C4.85786 1.5 1.5 4.85786 1.5 9C1.5 13.1421 4.85786 16.5 9 16.5Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.99995 2.25H6.74995C5.28745 6.63 5.28745 11.37 6.74995 15.75H5.99995" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.25 2.25C12.7125 6.63 12.7125 11.37 11.25 15.75" stroke="#445164" stroke-linecap="round" stroke-linejoin="round"/><path d="M2.25 12V11.25C6.63 12.7125 11.37 12.7125 15.75 11.25V12" stroke="#445164" stroke-linecap="round" stroke-linejoin="round"/><path d="M2.25 6.74995C6.63 5.28745 11.37 5.28745 15.75 6.74995" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>{timezone}</span>
                                        </div>
                                        {#if isDisplaySpots && selectedDateTime.remaining}
                                            <div class="fcal_remaining_spot fcal_icon_item">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" data-v-6fbb019e="">
                                                    <path fill="currentColor" d="M192 128v768h640V128zm-32-64h704a32 32 0 0 1 32 32v832a32 32 0 0 1-32 32H160a32 32 0 0 1-32-32V96a32 32 0 0 1 32-32m160 448h384v64H320zm0-192h192v64H320zm0 384h384v64H320z"></path>
                                                </svg>
                                                <span>{selectedDateTime.remaining} {i18('spots remaining')}</span>   
                                            </div>
                                        {/if}
                                    {/if}
                                </div>
                                {#if skipCalendar || (!selectedDateTime.start && !fluentFormDateTimeSelected.start)}
                                    <div class="fcal_slot_description">
                                        {@html slot.description || ''}
                                    </div>
                                {/if}
                            {/if}
                        </div>
                    </div>
                {/if}
                <div class="fcal_date_wrapper {selectedDateTime.start ? 'is_active' : ''}">
                    {#if appReady}
                        <div class="fcal_day_picker_wrap {skipCalendar ? 'skip_calendar' : ''}" id="fcal_day_picker_wrap">
                            <DayPickerApp
                                {appData}
                                {slot}
                                {selectedDate}
                                {selectedDateTime}
                                {selectedDateTimes}
                                bind:duration={duration}
                                bind:timezone={timezone}
                                bind:skipCalendar={skipCalendar}
                                bind:isLoadingDates={isLoadingDates}
                                on:dayClicked={(e) => {dayClicked(e.detail)}}
                                on:spotSelected={(e) => {spotSelected(e.detail)}}
                                on:spotClicked={(e) => {spotClicked(e.detail)}}
                                on:dateOnFluentForm={(e) => {fluentFormDateHandle(e)}}
                                on:formatHours={(e) => {formatHours(e.detail)}}
                                on:timezoneChanged={(e) => {resetSelection()}}
                                on:resetSelection={(e) => {resetSelection()}}
                                on:selectNewDate={(e) => {selectNewDate()}}
                            />
                        </div>
                        {#if skipCalendar && isLoadingDates}
                            <FcalSkeleton rows={6}/>
                        {/if}
                        <div class="fcal_date_event_details"
                            class:skip_calendar={skipCalendar}
                            class:is_payment={showingPayments}
                            class:is_active={selectedDateTime.start}>
                            <div class="fcal_date_event_details_header">
                                <h3>
                                    {#if showingPayments}
                                        {i18('Payment Details')}
                                    {:else}
                                        <div aria-label="Back to Date Selection" class="fcal_back {skipCalendar ? 'skip_calendar' : ''}"
                                            on:click={(e) => {resetSelection()}}
                                            on:keypress={(e) => { resetSelection() }}>
                                            <button type="button" class="fcal_svg">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                                    viewBox="0 0 24 24">
                                                    <path fill="none" d="M0 0h24v24H0V0z"/>
                                                    <path d="M19 11H7.83l4.88-4.88c.39-.39.39-1.03 0-1.42-.39-.39-1.02-.39-1.41 0l-6.59 6.59c-.39.39-.39 1.02 0 1.41l6.59 6.59c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L7.83 13H19c.55 0 1-.45 1-1s-.45-1-1-1z"/>
                                                </svg>
                                            </button>
                                        </div>
                                        {#if isFluentform}
                                            {i18('Summary')}
                                        {:else}
                                            {i18('Enter Details')}
                                        {/if}
                                    {/if}
                                </h3>
                            </div>
                            {#if selectedDateTime.start}
                                <BookingForm
                                    {slot}
                                    {appData}
                                    {timezone}
                                    {duration}
                                    bind:form={form}
                                    bind:quantity={quantity}
                                    bind:spot={selectedDateTime}
                                    bind:spots={selectedDateTimes}
                                    bind:formFields={appData.form_fields}
                                    on:bookingConfirmed={(e) => { handleBookingConfirmation(e.detail) }}
                                    on:onPaymentsVisibilityChanged={(e) => {onPaymentsVisibilityChanged(e.detail)}}
                                >
                                    <div slot="before_form">
                                        {#if isFluentform}
                                            <Summary
                                                {slot}
                                                {timezone}
                                                {dateFormatter}
                                                {selectedDateTime}
                                            />
                                        {/if}
                                    </div>
                                </BookingForm>
                            {/if}
                        </div>
                    {/if}
                </div>
            {/if}
        </div>
    </div>
    {#if appData.is_fluentform && !appData.isFFConversational}
        <input type="hidden" name={appData.name} value={JSON.stringify(fluentFormInput)}/>
    {/if}
</div>
