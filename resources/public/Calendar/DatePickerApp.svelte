<script>
    import { util, i18, getDateTimeStringI18, dateTimeI18 } from '../util';
    import Calendar from "./Calendar.svelte";
    import { Pulse } from 'svelte-loading-spinners';
    import TimeZoneSelector from "./TimezoneSelector.svelte";
    import { createEventDispatcher, onMount } from 'svelte';

    export let slot;
    export let timezone;
    export let duration;
    export let appData;
    export let selectedDate = '';
    export let selectedDateTime = {};
    export let selectedDateTimes = [];
    export let skipCalendar = false;
    export let isLoadingDates = false;

    const rescheduling = appData.rescheduling || 'no';
    const isFluentform = appData.is_fluentform;
    const isFFConversational = appData.isFFConversational;

    const id = appData.id;
    const isSingleGuest = slot.event_type == 'single';
    const isDisplaySpots = slot.is_display_spots || false;
    const isMultiBooking = slot.settings?.multiple_booking?.enabled || false;
    const isMultiBookingAllow = isSingleGuest && isMultiBooking;
    const isTimezoneDisabled = slot.settings?.lock_timezone?.enabled || false;

    const isRTL = appData.isRtl;
    let dispatch = createEventDispatcher();

    let monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

    //day start calculation
    let startDayIndex = 1;
    var dayNames = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"];
    const start_day = window.fluentCalendarPublicVars?.start_day;
    if (start_day) {
        startDayIndex = 8 - dayNames.indexOf(start_day);
        let firstThree = dayNames.splice(0, 8 - startDayIndex);
        dayNames = dayNames.concat(firstThree);
    }

    let headers = [];
    let now = new Date();
    let year = now.getFullYear();		//	this is the month & year displayed
    let month = now.getMonth();
    let availableDates = {};
    let daySlots = [];
    let noAvailability = false;
    let nextDisabled = false;
    let formatHours = appData.slot?.time_format;

    if (slot.min_bookable_date) {
        const minBookableDate = util.toTimezone(slot.min_bookable_date, timezone, 'YYYY-MM-DD');
        month = minBookableDate.split('-')[1] - 1;
        year = minBookableDate.split('-')[0];
    }

    if (slot.pre_selects?.month && slot.pre_selects?.year) {
        month = slot.pre_selects.month - 1;
        year = slot.pre_selects.year;
    }

    if (slot.pre_selects?.duration && slot.settings?.multi_duration?.enabled) {
        const availableDurations = slot.settings?.multi_duration?.available_durations || [];
        if (availableDurations.includes(slot.pre_selects.duration)) {
            duration = slot.pre_selects.duration;
        }
    }

    var days = [];	//	The days to display in each box

    $: month, year, availableDates, initContent(), maybeMaxDateDisabled();

    $: timezone, maybeTimeZoneChanged();

    $: duration, maybeDurationChanged();

    $: selectedDateTimes, maybeUpdateDaySlots();

    let lastTimeZone = timezone;

    let lastDuration = duration;

    let start_time;

    let primaryColor = 'var(--fcal_primary_color)';

    $: prevDisabled = (new Date(year, month, 1)).getTime() < (new Date(slot.min_lookup_date)).getTime();

    function maybeMaxDateDisabled() {
        let result = false;
        if (slot.max_lookup_date) {
            let nextMonth = month + 1;
            let nextYear = year;
            if (nextMonth == 12) {
                nextYear++;
                nextMonth = 0;
            }
            result = (new Date(nextYear, nextMonth, 0)).getTime() > (new Date(slot.max_lookup_date)).getTime();
        }
        nextDisabled = result;
    }

    function maybeTimeZoneChanged() {
        if (lastTimeZone != timezone) {
            lastTimeZone = timezone;
            selectedDate = '';
            selectedDateTime = {};
            loadAvailableDates();
            dispatch('timezoneChanged', timezone);
        }
    }

    function maybeDurationChanged() {
        if (lastDuration != duration) {
            lastDuration = duration;
            selectedDate = '';
            selectedDateTime = {};
            loadAvailableDates();
            dispatch('resetSelection');
        }
    }

    function maybeNoAvailability() {
        const dateKeys = Object.keys(availableDates);
        const hasReqMonthDate = dateKeys.some(date => month == date.substring(5, 7) - 1);
        noAvailability = !hasReqMonthDate;
    }

    function maybeUpdateDaySlots() {
        daySlots = [...daySlots];
    }

    function isSelectedDateTime(selectedDateTime, day) {
        if (isMultiBookingAllow) {
            return selectedDateTimes.some(time => time.start == day.start);
        }
        return selectedDateTime.start == day.start;
    }

    // choose what date/day gets displayed in each date box.
    function initContent() {
        headers = dayNames;
        initMonth();
    }

    let error = false;
    let errorText = '';
    let firstLoading = true;

    function loadAvailableDates() {
        isLoadingDates = true;
        availableDates = {};
        util.$get(window.fluentCalendarPublicVars.ajaxurl, {
            event_id: slot.id,
            timezone: timezone || '',
            duration: duration || '',
            rescheduling: rescheduling,
            action: 'fluent_cal_get_available_dates',
            start_date: util.dayjs(year + '-' + (month + 1) + '-', '01').format('YYYY-MM-DD'),
        })
            .then(response => {
                timezone = response.timezone;
                availableDates = response.available_slots;
                error = response.error;
                maybeNoAvailability();

                if (skipCalendar) {
                    gotoNextStep();
                } else if (firstLoading && slot.pre_selects?.day) {
                    selectedDate = generateDate(slot.pre_selects);
                    dayClick({ date: selectedDate });
                } else if (firstLoading && noAvailability && !nextDisabled) {
                    next();
                }
                 else {
                    selectedDate = '';
                    dispatch('dayClicked', '');
                }

            })
            .catch(errors => {
                error = true;
                errorText = errors?.response?.message;
                skipCalendar = false;
                console.log(errors);
            })
            .finally(() => {
                isLoadingDates = false;
                firstLoading = false;
            });
    }

    onMount(() => {
        loadAvailableDates();
    });

    function initMonth() {
        days = [];
        //	find the last Monday of the previous month
        const firstDay = new Date(year, month, startDayIndex).getDay();

        const daysInThisMonth = new Date(year, month + 1, 0).getDate();
        const daysInLastMonth = new Date(year, month, 0).getDate();
        const prevMonth = month === 0 ? 11 : month - 1;

        //	show the days before the start of this month (disabled) - always less than 7
        for (let i = daysInLastMonth - firstDay; i < daysInLastMonth; i++) {
            let d = new Date(prevMonth == 11 ? year - 1 : year, prevMonth, i + 1);
            days.push({name: '', enabled: false, date: d});
        }

        //	show the days in this month (enabled) - always 28 - 31
        for (let i = 0; i < daysInThisMonth; i++) {
            let d = new Date(year, month, i + 1);
            const date = util.dayjs(d).format('YYYY-MM-DD');
            const enabled = !!availableDates[date];
            days.push({name: '' + (i + 1), enabled: enabled, date: date});
        }
    }

    function generateDate(date) {
        return date.year + '-' + date.month + '-' + date.day;
    }

    function generateTime(date) {
        const parts = date.time.split(':');
        if (parts.length == 2) {
            return date.time + ':00';
        }
        return date.time;
    }

    function gotoNextStep() {
        if (noAvailability) {
            skipCalendar = false;
            return;
        }
        const preSelectTime = generateTime(slot.pre_selects);
        let preSelectDate = generateDate(slot.pre_selects);
        let preSelectDateTime = preSelectDate + ' ' + preSelectTime;
        if (slot.event_type == 'group_event') {
            preSelectDateTime = util.toTimezone(preSelectDateTime, timezone, 'YYYY-MM-DD HH:mm:ss');
            preSelectDate = preSelectDateTime.split(' ')[0];
        }
        const availableTime = availableDates[preSelectDate]?.find(slot => slot.start == preSelectDateTime) ?? false;
        if (availableTime) {
            selectedDate = preSelectDate;
            selectedDateTime = availableTime;
            daySlots = availableDates[preSelectDate];
            dispatch('dayClicked', selectedDate);
            slotSpotConfirmed();
            spotClicked(selectedDateTime);
            slotSpotForFluentForm(selectedDateTime);
        } else {
            skipCalendar = false;
            selectedDate = '';
            dispatch('dayClicked', '');
        }
    }

    function dayClick(day) {
        if (availableDates[day.date]) {
            daySlots = availableDates[day.date];
            selectedDate = day.date;
            if (daySlots.length == 1 && !daySlots[0].remaining) {
                selectedDateTime = daySlots[0];
            }
            dispatch('dayClicked', selectedDate);
        } else {
            daySlots = [];
            selectedDate = '';
        }
    }

    function next() {
        if (nextDisabled) {
            return;
        }
        month++;
        if (month == 12) {
            year++;
            month = 0;
        }
        loadAvailableDates();
    }

    function prev() {
        // create date from month and year
        if (prevDisabled) {
            return;
        }

        if (month == 0) {
            month = 11;
            year--;
        } else {
            month--;
        }
        loadAvailableDates();
    }

    function spotClicked(day) {
        selectedDateTime = day;
        slotSpotForFluentForm(day);
        dispatch('spotClicked', day);
    }

    function slotSpotConfirmed() {
        dispatch('spotSelected', selectedDateTime);
        dispatch('formatHours', formatHours);
        setTimeout(() => {
            selectedDateTime = {};
        }, 1000);
    }

    function slotSpotForFluentForm(day) {
        selectedDateTime = day;
        if (isFluentform) {
            dispatch('dateOnFluentForm', selectedDateTime);
        } else {
            return;
        }

        if (selectedDateTime) {
            start_time = selectedDateTime.start;
        }

        if (isFFConversational) {
            appData.element.dispatchEvent(new CustomEvent('value.update', {
                detail: {
                    value: JSON.stringify({id, timezone, duration, start_time})
                }
            }));
        }
    }

    function selectNewDate() {
        selectedDate = null;
        dispatch('selectNewDate');
    }

    function convertTime12to24(time12h, formatHr) {
        const [time, modifier] = time12h.split(' ');
        let [hours, minutes] = time.split(':');

        if (formatHr === '24' && hours === '12') {
            hours = '00';
        }

        if (modifier === 'PM' && formatHr === '24') {
            hours = parseInt(hours, 10) + 12;
        }

        const string = `${hours}:${minutes} ${formatHr === '12' ? `${i18(modifier)}` : ''}`;
        return getDateTimeStringI18(string, 'mNumber');
    }

</script>

<div class="fcal_day_picker">
    <div class="fcal_calendar_slot_wrap {selectedDate ? 'is_active' : ''}">
        {#if isLoadingDates}
            <div class="fcal_loading_dates">
                <div class="fcal_loading_dates_inner">
                    <Pulse color={primaryColor}/>
                </div>
            </div>
        {/if}
        <div class="calendar-container">
            <div class="calendar-header">
                <div class="calendar-month-year">
                    {#if isFluentform}
                        <h4>{getDateTimeStringI18(monthNames[month], 'month')}
                            <span>{getDateTimeStringI18(year, 'mNumber')}</span></h4>
                    {:else}
                        <h2>{getDateTimeStringI18(monthNames[month], 'month')}
                            <span>{getDateTimeStringI18(year, 'mNumber')}</span></h2>
                    {/if}
                </div>
                <div class="calendar_nav">
                    <button aria-label="Previous Month" type="button" tabindex={prevDisabled ? '-1' : '0'} class={prevDisabled ? 'fcal_btn_disabled' : 'fcal_nav_active'}
                            on:click={()=>prev()}>
                        {#if isRTL}
                            <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-029747aa="">
                                <path fill="currentColor"d="M338.752 104.704a64 64 0 0 0 0 90.496l316.8 316.8-316.8 316.8a64 64 0 0 0 90.496 90.496l362.048-362.048a64 64 0 0 0 0-90.496L429.248 104.704a64 64 0 0 0-90.496 0z"></path>
                            </svg>
                        {:else}
                            <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-029747aa="">
                                <path fill="currentColor" d="M685.248 104.704a64 64 0 0 1 0 90.496L368.448 512l316.8 316.8a64 64 0 0 1-90.496 90.496L232.704 557.248a64 64 0 0 1 0-90.496l362.048-362.048a64 64 0 0 1 90.496 0z"></path>
                            </svg>
                        {/if}
                    </button>
                    <button aria-label="Next Month" type="button" tabindex={nextDisabled ? '-1' : '0'} class="{nextDisabled ? 'fcal_btn_disabled' : 'fcal_nav_active'}"
                            on:click={()=>next()}>
                        {#if isRTL}
                            <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-029747aa="">
                                <path fill="currentColor" d="M685.248 104.704a64 64 0 0 1 0 90.496L368.448 512l316.8 316.8a64 64 0 0 1-90.496 90.496L232.704 557.248a64 64 0 0 1 0-90.496l362.048-362.048a64 64 0 0 1 90.496 0z"></path>
                            </svg>
                        {:else}
                            <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-029747aa="">
                                <path fill="currentColor"d="M338.752 104.704a64 64 0 0 0 0 90.496l316.8 316.8-316.8 316.8a64 64 0 0 0 90.496 90.496l362.048-362.048a64 64 0 0 0 0-90.496L429.248 104.704a64 64 0 0 0-90.496 0z"></path>
                            </svg>
                        {/if}
                    </button>
                </div>
            </div>
            <Calendar
                selectedDate="{selectedDate}"
                isMultiBookingAllow={isMultiBookingAllow}
                {selectedDateTimes}
                {headers}
                {days}
                on:dayClick={(e)=>dayClick(e.detail)}
            />
            <div class="fcal_timezone_select">
                <label aria-label="Select Timezone" for="fcal_timezone_selector">{i18('Timezone')}</label>
                <TimeZoneSelector bind:timezone={timezone} isDisabled={isTimezoneDisabled}/>
            </div>
            <slot/>
            {#if error && errorText}
                <div class="fcal_error_text">
                    <h3>{errorText}</h3>
                </div>
            {/if}
            {#if noAvailability && !isLoadingDates}
                <div class="fcal_no_availability">
                    <h3>{i18('No availability in')} {getDateTimeStringI18(monthNames[month], 'month')}</h3>
                    {#if !nextDisabled && !error}
                        <button type="button" tabindex="0" on:click={()=>next()}>
                            {i18('View next month')}
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="fcal_next_month">
                                <line x1="5" x2="19" y1="12" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    {:else if !prevDisabled && !error}
                        <button type="button" tabindex="0" on:click={()=>prev()}>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="fcal_prev_month">
                                <line x1="5" x2="19" y1="12" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                            {i18('View previous month')}
                        </button>
                    {/if}
                </div>
            {/if}
        </div>

        <div class="fcal_slot_picker { selectedDate ? 'is_active' : ''}">
            <div class="fcal_slot_picker_header">
                <div aria-label="Back to Date Selection" class="fcal_back" on:keypress="{(e) => {selectNewDate()}}"
                     on:click={() => selectNewDate()}>
                    <button type="button" class="fcal_svg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                             viewBox="0 0 24 24">
                            <path fill="none" d="M0 0h24v24H0V0z"/>
                            <path d="M19 11H7.83l4.88-4.88c.39-.39.39-1.03 0-1.42-.39-.39-1.02-.39-1.41 0l-6.59 6.59c-.39.39-.39 1.02 0 1.41l6.59 6.59c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L7.83 13H19c.55 0 1-.45 1-1s-.45-1-1-1z"/>
                        </svg>
                    </button>
                </div>
                <span class="fcal_slot_date_info">{ dateTimeI18(selectedDate, 'ddd') }
                    <span>{getDateTimeStringI18(dateTimeI18(selectedDate, 'DD'), 'mNumber')}</span></span>
                <div class="fcal_slot_picker_header_action">
                    <div class="format-hour">
                        <button aria-label="12th Hour Format" type="button" class="{formatHours === '12' ? 'active' : ''}" on:click={() => formatHours = '12'}>
                            {i18('12h')}
                        </button>
                    </div>
                    <div class="format-hour">
                        <button aria-label="24th Hour Format" type="button" class="{formatHours === '24' ? 'active' : ''}" on:click={() => formatHours = '24'}>
                            {i18('24h')}
                        </button>
                    </div>
                </div>
            </div>
            <div class="fcal_slot_items">
                <div class="fcal_spot_lists">
                    {#each daySlots as day}
                        <div
                            class="fcal_spot { isSelectedDateTime(selectedDateTime, day) ? 'fcal_spot_selected' : '' }">
                            <div role="button" tabindex="0" aria-label="Select Time"
                                on:click="{spotClicked(day)}"
                                on:keypress="{(e) => {spotClicked(day)}}"
                                class="fcal_spot_name">
                                <div class="{ day.remaining && selectedDateTime != day ? 'fcal_spot_time' : '' }">
                                    {convertTime12to24(util.dayjs(day.start).format('hh:mm A'), formatHours)}
                                </div>
                                {#if isDisplaySpots && day.remaining && selectedDateTime != day }
                                    <div class="fcal_spot_remaining">
                                        {getDateTimeStringI18(day.remaining)} {i18('spots left')}
                                    </div>
                                {/if}
                            </div>
                            {#if isSelectedDateTime(selectedDateTime, day)}
                                {#if isFluentform}
                                    <span on:click="{slotSpotForFluentForm(day)}" on:keypress={slotSpotForFluentForm(day)}
                                        role="button" aria-label="Confirm Time" tabindex="0" class="fcal_spot_confirm">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" width="60px" height="60px">
                                            <path d="M 26.980469 5.9902344 A 1.0001 1.0001 0 0 0 26.292969 6.2929688 L 11 21.585938 L 4.7070312 15.292969 A 1.0001 1.0001 0 1 0 3.2929688 16.707031 L 10.292969 23.707031 A 1.0001 1.0001 0 0 0 11.707031 23.707031 L 27.707031 7.7070312 A 1.0001 1.0001 0 0 0 26.980469 5.9902344 z"/>
                                        </svg>
                                    </span>
                                {:else }
                                    <button type="button" aria-label="Confirm Time" on:keypress="{(e) => {selectedDateTime = day}}"
                                         on:click={slotSpotConfirmed} class="fcal_spot_confirm"> {i18('Next')}
                                    </button>
                                {/if}
                            {/if}
                        </div>
                    {/each}
                </div>
            </div>
        </div>
    </div>
</div>
