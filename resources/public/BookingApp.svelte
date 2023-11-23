<script>
    import {util, i18, dateTimeI18} from './util';
    import {onMount} from "svelte";
    import DayPickerApp from "./Calendar/DatePickerApp.svelte";
    import BookingForm from "./Components/BookingForm.svelte";
    import {createEventDispatcher} from 'svelte'

    window['fcal_translate'] = i18;

    let wrapDom;
    let wrapperClass = '';

    export let appData;
    export let handleBack;

    const slot = appData.slot;
    const settings = appData.settings;
    const author = appData.author_profile;
    const isFluentform = appData.is_fluentform;
    let form = window.fluentCalendarPublicVars.current_person || {};

    let appReady = false;
    let bookingConfirmationHtml = '';
    let calendarHeight = '';
    let component = null;
    let isBookingDone = false;

    let selectedDate = false;
    let selectedDateTime = {};

    let showingPayments = false;
    let timezone = '';
    let wrapperWidth = 800;

    let fluentFormDateTimeSelected = {};

    const dispatch = createEventDispatcher();

    function handleBackClick() {
        dispatch('handleBack');
    }

    function checkDevice() {
        wrapperWidth = wrapDom.parentNode.offsetWidth;
        const conversationalPage = document.getElementsByClassName('ff_conversation_page_body');

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
    }

    onMount(() => {
        timezone = util.dayjs.tz.guess();
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

    function formatHours(e) {
        slot.time_format = e;
    }

    function handleBookingConfirmation(confirmation) {
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
    }

    function fluentFormDateHandle(e) {
        fluentFormDateTimeSelected = e.detail;
    }
</script>
<div class="fcal_wrap">
    <div bind:this={wrapDom} class="fcal_holder" id={appData.id}>
        {#if true}
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
                                <div class="fcal_author">
                                    <div class="fcal_author_avatar">
                                        <img src="{author.avatar}" alt="{author.name}">
                                    </div>
                                    <div class="fcal_author_name">
                                        {author.name}
                                    </div>
                                </div>
                                <div class="fcal_slot_info">
                                    <h2 class="fcal_slot_heading">{slot.title}</h2>
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
                                        <span>{slot.duration} {i18('minutes')}</span>
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

                                    {#if slot.total_payment }
                                        {@html slot.total_payment}
                                    {/if}
                                    {#if selectedDateTime.start}
                                        <div class="slot_time_range fcal_icon_item">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                 viewBox="0 0 18 18" fill="none">
                                                <path d="M6 1.5V3.75" stroke="#445164" stroke-width="1.25"
                                                      stroke-miterlimit="10" stroke-linecap="round"
                                                      stroke-linejoin="round"/>
                                                <path d="M12 1.5V3.75" stroke="#445164" stroke-width="1.25"
                                                      stroke-miterlimit="10" stroke-linecap="round"
                                                      stroke-linejoin="round"/>
                                                <path d="M2.625 6.8175H15.375" stroke="#445164" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                <path
                                                    d="M15.75 6.375V12.75C15.75 15 14.625 16.5 12 16.5H6C3.375 16.5 2.25 15 2.25 12.75V6.375C2.25 4.125 3.375 2.625 6 2.625H12C14.625 2.625 15.75 4.125 15.75 6.375Z"
                                                    stroke="#445164" stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path d="M11.771 10.275H11.7778" stroke="#445164" stroke-width="1.5"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M11.771 12.525H11.7778" stroke="#445164" stroke-width="1.5"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M8.99661 10.275H9.00335" stroke="#445164" stroke-width="1.5"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M8.99661 12.525H9.00335" stroke="#445164" stroke-width="1.5"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M6.22073 10.275H6.22747" stroke="#445164" stroke-width="1.5"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M6.22073 12.525H6.22747" stroke="#445164" stroke-width="1.5"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
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
                                                <path
                                                    d="M9 16.5C13.1421 16.5 16.5 13.1421 16.5 9C16.5 4.85786 13.1421 1.5 9 1.5C4.85786 1.5 1.5 4.85786 1.5 9C1.5 13.1421 4.85786 16.5 9 16.5Z"
                                                    stroke="#445164" stroke-width="1.25" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path
                                                    d="M5.99995 2.25H6.74995C5.28745 6.63 5.28745 11.37 6.74995 15.75H5.99995"
                                                    stroke="#445164" stroke-width="1.25" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path d="M11.25 2.25C12.7125 6.63 12.7125 11.37 11.25 15.75"
                                                      stroke="#445164" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M2.25 12V11.25C6.63 12.7125 11.37 12.7125 15.75 11.25V12"
                                                      stroke="#445164" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M2.25 6.74995C6.63 5.28745 11.37 5.28745 15.75 6.74995"
                                                      stroke="#445164" stroke-width="1.25" stroke-linecap="round"
                                                      stroke-linejoin="round"/>
                                            </svg>
                                            <span>{timezone}</span>
                                        </div>
                                    {/if}
                                </div>
                                {#if fluentFormDateTimeSelected.start}
                                    <div class="slot_time_range fcal_icon_item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                             viewBox="0 0 18 18" fill="none">
                                            <path d="M6 1.5V3.75" stroke="#445164" stroke-width="1.25"
                                                  stroke-miterlimit="10" stroke-linecap="round"
                                                  stroke-linejoin="round"/>
                                            <path d="M12 1.5V3.75" stroke="#445164" stroke-width="1.25"
                                                  stroke-miterlimit="10" stroke-linecap="round"
                                                  stroke-linejoin="round"/>
                                            <path d="M2.625 6.8175H15.375" stroke="#445164" stroke-miterlimit="10"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                            <path
                                                d="M15.75 6.375V12.75C15.75 15 14.625 16.5 12 16.5H6C3.375 16.5 2.25 15 2.25 12.75V6.375C2.25 4.125 3.375 2.625 6 2.625H12C14.625 2.625 15.75 4.125 15.75 6.375Z"
                                                stroke="#445164" stroke-miterlimit="10" stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                            <path d="M11.771 10.275H11.7778" stroke="#445164" stroke-width="1.5"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M11.771 12.525H11.7778" stroke="#445164" stroke-width="1.5"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8.99661 10.275H9.00335" stroke="#445164" stroke-width="1.5"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8.99661 12.525H9.00335" stroke="#445164" stroke-width="1.5"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M6.22073 10.275H6.22747" stroke="#445164" stroke-width="1.5"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M6.22073 12.525H6.22747" stroke="#445164" stroke-width="1.5"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>

                                        <span>
                                        {#if slot.time_format == '24' }
                                            {util.dateTimeI18(fluentFormDateTimeSelected.start, 'HH:mm')}
                                            - {util.dateTimeI18(fluentFormDateTimeSelected.end, 'HH:mm')},
                                       {:else}
                                            {util.dateTimeI18(fluentFormDateTimeSelected.start, 'hh:mma')}
                                            - {util.dateTimeI18(fluentFormDateTimeSelected.end, 'hh:mma')},
                                       {/if}
                                            {util.dateTimeI18(fluentFormDateTimeSelected.start, 'dddd, MMM DD, YYYY')}
                                    </span>
                                    </div>
                                {/if}
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
                            <div class="fcal_day_picker_wrap" id="fcal_day_picker_wrap">
                                <DayPickerApp
                                    {appData}
                                    {slot}
                                    {settings}
                                    {selectedDate}
                                    {selectedDateTime}
                                    bind:timezone={timezone}
                                    bind:form={form}
                                    on:dayClicked={(e) => {dayClicked(e.detail)}}
                                    on:spotSelected={(e) => {spotSelected(e.detail)}}
                                    on:dateOnFluentForm={(e) => {fluentFormDateHandle(e)}}
                                    on:formatHours={(e) => {formatHours(e.detail)}}
                                    on:timezoneChanged={(e) => {resetSelection()}}
                                    on:resetSelection={(e) => { resetSelection() }}
                                >
                                    {#if isFluentform && appData.form_fields.length > 0}
                                        <div class="fcal_ff_location">
                                            <BookingForm
                                                {appData}
                                                {slot}
                                                {timezone}
                                                bind:form={form}
                                                on:onPaymentsVisibilityChanged={(e) => {onPaymentsVisibilityChanged(e.detail)}}
                                                bind:spot={selectedDateTime}
                                                bind:formFields={appData.form_fields}
                                                on:bookingConfirmed={(e) => { handleBookingConfirmation(e.detail) }}
                                            />
                                        </div>
                                    {/if}
                                </DayPickerApp>
                            </div>
                            {#if !isFluentform }
                                <div
                                    class="fcal_date_event_details {showingPayments ? 'is_payment' : ''} { selectedDateTime.start ? 'is_active' : ''}">
                                    <div class="fcal_date_event_details_header">
                                        <h2>
                                            {#if showingPayments}
                                                {i18('Payment Details')}
                                            {:else}
                                                <div aria-label="Back to Date Selection" on:click={(e) => {
                                                resetSelection()
                                             }} on:keypress={(e) => { resetSelection() }} class="fcal_back">
                                                    <i class="fcal_svg">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                                             viewBox="0 0 24 24">
                                                            <path fill="none" d="M0 0h24v24H0V0z"/>
                                                            <path
                                                                d="M19 11H7.83l4.88-4.88c.39-.39.39-1.03 0-1.42-.39-.39-1.02-.39-1.41 0l-6.59 6.59c-.39.39-.39 1.02 0 1.41l6.59 6.59c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L7.83 13H19c.55 0 1-.45 1-1s-.45-1-1-1z"/>
                                                        </svg>
                                                    </i>
                                                </div>
                                                {i18('Enter Details')}
                                            {/if}
                                        </h2>
                                    </div>

                                    {#if selectedDateTime.start}
                                        <BookingForm
                                            {appData}
                                            {slot}
                                            {timezone}
                                            bind:form={form}
                                            on:onPaymentsVisibilityChanged={(e) => {onPaymentsVisibilityChanged(e.detail)}}
                                            bind:spot={selectedDateTime}
                                            bind:formFields={appData.form_fields}
                                            on:bookingConfirmed={(e) => { handleBookingConfirmation(e.detail) }}
                                        />
                                    {/if}
                                </div>
                            {/if}
                        {/if}
                    </div>
                {/if}
            </div>
        {/if}
    </div>
</div>
