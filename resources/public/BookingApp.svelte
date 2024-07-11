<script>
    import { util, i18 } from './util';
    import { onMount } from "svelte";
    import DayPickerApp from "./Calendar/DatePickerApp.svelte";
    import BookingForm from "./Components/BookingForm.svelte";
    import Summary from "./Fluentform/Summary.svelte";
    import { createEventDispatcher } from 'svelte';
    import FcalSkeleton from './Components/FcalSkeleton.svelte';

    window['fcal_translate'] = i18;

    let wrapDom;
    let wrapperClass = '';

    export let appData;
    export let handleBack;
    export let duration = appData.slot.duration;

    const slot = appData.slot;
    const author = appData.author_profile;
    const teamMembers = appData.team_member_profiles;
    const isFluentform = appData.is_fluentform;
    const eventType = slot.event_type;
    const availableDurations = slot.settings?.multi_duration?.available_durations || [];
    let form = window.fluentCalendarPublicVars.current_person || {};

    let appReady = false;
    let bookingConfirmationHtml = '';
    let component = null;
    let isBookingDone = false;

    let selectedDate = false;
    let selectedDateTime = {};
    let isLoadingDates = false;

    let showingPayments = false;
    let timezone = '';
    let wrapperWidth = 800;

    let fluentFormDateTimeSelected = {};

    const dispatch = createEventDispatcher();

    function handleBackClick() {
        dispatch('handleBack');
    }

    function checkDevice() {
        let timeout = 0;
        let conversationalPage = document.getElementsByClassName('ff_conversation_page_body');
        if (isFluentform && !conversationalPage) {
            timeout = 2000;
        }
        setTimeout(() => {
            wrapperWidth = wrapDom.parentNode.offsetWidth;
            if (wrapperWidth >= 900) {
                wrapperClass = 'fcal_on_lg';
            } else if (wrapperWidth >= 800) {
                wrapperClass = 'fcal_on_md';
            } else if (wrapperWidth >= 600) {
                wrapperClass = 'fcal_on_sm';
                if (conversationalPage.length && appData.disable_author) {
                    wrapperClass = 'fcal_on_md';
                }
            } else {
                wrapperClass = 'fcal_on_xs fcal_mobile';
            }
        }, timeout)
    }

    onMount(() => {
        if (slot.settings?.lock_timezone?.enabled) {
            timezone = slot.settings.lock_timezone.timezone || util.dayjs.tz.guess();
        } else {
            timezone = util.dayjs.tz.guess();
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

    function durationSelected(value) {
        duration = value;
        maybeUpdatePayment(value)
    }

    function getDuration(duration) {
        const durationLookup = appData.duration_lookup;
        return durationLookup[duration] ?? duration + ' ' + i18('Minutes');
    }

    function getMultiDuration(duration) {
        const durationLookup = appData.multi_duration_lookup;
        return durationLookup[duration];
    }

    function formatHours(e) {
        slot.time_format = e;
    }

    function maybeUpdatePayment(duration) {
        if (appData.multi_payment_items) {
            slot.total_payment = appData.multi_payment_items[duration]?.value;
        }

        if (appData.multi_payment_woo_ids) {
            slot.total_payment = appData.multi_payment_woo_ids[duration]?.value;
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

    function resetSelection() {
        if (wrapperWidth < 800) {
            selectedDate = null;
        }
        selectedDateTime = {};
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
    $: if (appData.is_fluentform && (form.location_config || selectedDateTime.start )) {
        fluentFormInput = {
            id: appData.id,
            form: form,
            timezone: timezone,
            duration: duration,
            start_time: selectedDateTime.start
        }

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
                            <div class="fcal_slot_info">
                                <h1 aria-level="1" class="fcal_slot_heading">{slot.title}</h1>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                         viewBox="0 0 18 18"
                                         fill="none">
                                        <path
                                            d="M16.5 9C16.5 13.14 13.14 16.5 9 16.5C4.86 16.5 1.5 13.14 1.5 9C1.5 4.86 4.86 1.5 9 1.5C13.14 1.5 16.5 4.86 16.5 9Z"
                                            stroke="#445164" stroke-width="1.25" stroke-linecap="round"
                                            stroke-linejoin="round"/>
                                        <path
                                            d="M11.7825 11.3849L9.45753 9.99745C9.05253 9.75745 8.72253 9.17995 8.72253 8.70745V5.63245"
                                            stroke="#445164" stroke-width="1.25" stroke-linecap="round"
                                            stroke-linejoin="round"/>
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
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                     class="feather feather-map-pin">
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
                                            <path fill="currentColor" d="M256 640v192h640V384H768v-64h150.976c14.272 0 19.456 1.472 24.64 4.288a29.056 29.056 0 0 1 12.16 12.096c2.752 5.184 4.224 10.368 4.224 24.64v493.952c0 14.272-1.472 19.456-4.288 24.64a29.056 29.056 0 0 1-12.096 12.16c-5.184 2.752-10.368 4.224-24.64 4.224H233.024c-14.272 0-19.456-1.472-24.64-4.288a29.056 29.056 0 0 1-12.16-12.096c-2.688-5.184-4.224-10.368-4.224-24.576V640h64z"></path>
                                            <path fill="currentColor" d="M768 192H128v448h640V192zm64-22.976v493.952c0 14.272-1.472 19.456-4.288 24.64a29.056 29.056 0 0 1-12.096 12.16c-5.184 2.752-10.368 4.224-24.64 4.224H105.024c-14.272 0-19.456-1.472-24.64-4.288a29.056 29.056 0 0 1-12.16-12.096C65.536 682.432 64 677.248 64 663.04V169.024c0-14.272 1.472-19.456 4.288-24.64a29.056 29.056 0 0 1 12.096-12.16C85.568 129.536 90.752 128 104.96 128h685.952c14.272 0 19.456 1.472 24.64 4.288a29.056 29.056 0 0 1 12.16 12.096c2.752 5.184 4.224 10.368 4.224 24.64z"></path>
                                            <path fill="currentColor" d="M448 576a160 160 0 1 1 0-320 160 160 0 0 1 0 320zm0-64a96 96 0 1 0 0-192 96 96 0 0 0 0 192z"></path>
                                        </svg> {@html slot.currency}{slot.total_payment}
                                    </div>
                                {/if}
                                {#if selectedDateTime.start}
                                    <div class="slot_time_range fcal_icon_item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                            <path d="M6 1.5V3.75" stroke="#445164" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M12 1.5V3.75" stroke="#445164" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M2.625 6.8175H15.375" stroke="#445164" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M15.75 6.375V12.75C15.75 15 14.625 16.5 12 16.5H6C3.375 16.5 2.25 15 2.25 12.75V6.375C2.25 4.125 3.375 2.625 6 2.625H12C14.625 2.625 15.75 4.125 15.75 6.375Z" stroke="#445164" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M11.771 10.275H11.7778" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M11.771 12.525H11.7778" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8.99661 10.275H9.00335" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8.99661 12.525H9.00335" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M6.22073 10.275H6.22747" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M6.22073 12.525H6.22747" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>

                                        <span>
                                            {#if slot.time_format == '24' }
                                                {util.dateTimeI18(selectedDateTime.start, 'HH:mm')}
                                                - {util.dateTimeI18(selectedDateTime.end, 'HH:mm')},
                                           {:else}
                                                {util.dateTimeI18(selectedDateTime.start, 'hh:mma')}
                                                - {util.dateTimeI18(selectedDateTime.end, 'hh:mma')},
                                           {/if}
                                            {util.dateTimeI18(selectedDateTime.start, 'dddd, MMM DD, YYYY')}
                                        </span>
                                    </div>
                                    <div class="slot_time_range slot_timezone fcal_icon_item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                             viewBox="0 0 18 18" fill="none">
                                            <path d="M9 16.5C13.1421 16.5 16.5 13.1421 16.5 9C16.5 4.85786 13.1421 1.5 9 1.5C4.85786 1.5 1.5 4.85786 1.5 9C1.5 13.1421 4.85786 16.5 9 16.5Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M5.99995 2.25H6.74995C5.28745 6.63 5.28745 11.37 6.74995 15.75H5.99995" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M11.25 2.25C12.7125 6.63 12.7125 11.37 11.25 15.75" stroke="#445164" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M2.25 12V11.25C6.63 12.7125 11.37 12.7125 15.75 11.25V12" stroke="#445164" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M2.25 6.74995C6.63 5.28745 11.37 5.28745 15.75 6.74995" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span>{timezone}</span>
                                    </div>
                                {/if}
                            </div>
                            {#if !selectedDateTime.start && !fluentFormDateTimeSelected.start}
                                <div class="fcal_slot_description">
                                    {@html slot.description || ''}
                                </div>
                            {/if}
                        </div>
                    </div>
                {/if}
                <div class="fcal_date_wrapper {selectedDateTime.start ? 'is_active' : ''}">
                    {#if appReady}
                        <div class="fcal_day_picker_wrap {eventType}" id="fcal_day_picker_wrap">
                            <DayPickerApp
                                {appData}
                                {slot}
                                {selectedDate}
                                {selectedDateTime}
                                bind:duration={duration}
                                bind:timezone={timezone}
                                bind:isLoadingDates={isLoadingDates}
                                on:dayClicked={(e) => {dayClicked(e.detail)}}
                                on:spotSelected={(e) => {spotSelected(e.detail)}}
                                on:dateOnFluentForm={(e) => {fluentFormDateHandle(e)}}
                                on:formatHours={(e) => {formatHours(e.detail)}}
                                on:timezoneChanged={(e) => {resetSelection()}}
                                on:resetSelection={(e) => {resetSelection()}}
                            />
                        </div>
                        {#if isLoadingDates}
                            <FcalSkeleton items={6}/>
                        {/if}
                        <div class="fcal_date_event_details {eventType} {showingPayments ? 'is_payment' : ''} { selectedDateTime.start ? 'is_active' : ''}">
                            <div class="fcal_date_event_details_header">
                                <h3>
                                    {#if showingPayments}
                                        {i18('Payment Details')}
                                    {:else}
                                        <div aria-label="Back to Date Selection" class="fcal_back {eventType}"
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
                                    {appData}
                                    {slot}
                                    {timezone}
                                    {duration}
                                    bind:form={form}
                                    on:onPaymentsVisibilityChanged={(e) => {onPaymentsVisibilityChanged(e.detail)}}
                                    bind:spot={selectedDateTime}
                                    bind:formFields={appData.form_fields}
                                    on:bookingConfirmed={(e) => { handleBookingConfirmation(e.detail) }}
                                >
                                    <div slot="before_form">
                                        {#if isFluentform}
                                            <Summary
                                                {slot}
                                                {timezone}
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
