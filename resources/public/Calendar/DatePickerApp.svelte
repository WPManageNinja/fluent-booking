<script>
    import {util} from '../util';
    import {Pulse} from 'svelte-loading-spinners';
    import TimeZoneSelector from "./TimezoneSelector.svelte";

    export let slot;
    export let settings;
    export let timezone;

    import Calendar from "./Calendar.svelte";
    import {createEventDispatcher, onMount} from 'svelte';

    let dispatch = createEventDispatcher();

    var dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    let monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

    let headers = [];
    let now = new Date();
    let year = now.getFullYear();		//	this is the month & year displayed
    let month = now.getMonth();
    let isLoadingDates = false;
    let availableDates = {};
    let daySlots = [];
    let selectedDate = '';
    let selectedDateTime = {};
    let nextDisabled = false;

    if (slot.pre_selects) {
        month = slot.pre_selects.month - 1;
        year = slot.pre_selects.year;
    }

    var days = [];	//	The days to display in each box

    $: month, year, availableDates, initContent(), maybeMaxDateDisabled();

    $: timezone, maybeTimeZoneChanged();

    let lastTimeZone = timezone;

    $: prevDisabled = (new Date(year, month, 1)).getTime() < (new Date()).getTime();

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

    // choose what date/day gets displayed in each date box.
    function initContent() {
        headers = dayNames;
        initMonth();
    }

    let firstLoading = true;

    function loadAvailableDates() {
        isLoadingDates = true;
        availableDates = {};
        util.$get(window.fluentCalendarPublicVars.ajaxurl, {
            slot_id: slot.id,
            timezone: timezone || '',
            action: 'fluent_cal_get_available_dates',
            start_date: util.dayjs(year + '-' + (month + 1) + '-', '01').format('YYYY-MM-DD'),
        })
            .then(response => {
                timezone = response.timezone;
                availableDates = response.available_slots;

                if(firstLoading && slot.pre_selects.day) {
                    selectedDate = slot.pre_selects.year + '-' + slot.pre_selects.month + '-' + slot.pre_selects.day;
                    dayClick({
                        date: slot.pre_selects.year + '-' + slot.pre_selects.month + '-' + slot.pre_selects.day
                    });
                } else {
                    selectedDate = '';
                }
            })
            .catch(errors => {
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
        const firstDay = new Date(year, month, 1).getDay();

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

    function dayClick(day) {
        if (availableDates[day.date]) {
            daySlots = availableDates[day.date];
            selectedDate = day.date;
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

    function slotSpotConfirmed() {
        dispatch('spotSelected', selectedDateTime);
        setTimeout(() => {
            selectedDateTime = {};
        }, 1000);
    }

    function resetSelection() {
        selectedDate = '';
        selectedDateTime = {};
        dispatch('resetSelection');
    }


</script>

<div class="fcal_day_picker">
    <div class="fcal_day_picker_head fcal_sec_heading">
        {#if settings?.label != undefined}
            <h3 class="{settings.validation_rules?.required?.value ? 'fcal_label_required' : ''}">
                { settings.label }
            </h3>
        {/if}
    </div>
    <div class="fcal_time_picker_head fcal_sec_heading">
        <div aria-label="Back to Date Selection" on:click={(e) => { resetSelection() }}
             on:keypress={(e) => { resetSelection() }} class="fcal_back fcal_go_back">
            <i class="fcal_svg">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                    <path fill="none" d="M0 0h24v24H0V0z"/>
                    <path
                        d="M19 11H7.83l4.88-4.88c.39-.39.39-1.03 0-1.42-.39-.39-1.02-.39-1.41 0l-6.59 6.59c-.39.39-.39 1.02 0 1.41l6.59 6.59c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L7.83 13H19c.55 0 1-.45 1-1s-.45-1-1-1z"/>
                </svg>
            </i>
        </div>
        <h3>Select a Time</h3>
        <p>{slot.duration} minutes</p>
        <p>Timezone: {timezone}</p>
    </div>
    <div class="fcal_calendar_slot_wrap">
        {#if isLoadingDates}
            <div class="fcal_loading_dates">
                <div class="fcal_loading_dates_inner">
                    <Pulse color="#0060e6"/>
                </div>
            </div>
        {/if}
        <div class="calendar-container">
            <div class="calendar-header">
                <div class="calendar-month-year">
                    <h3>{monthNames[month]} <span>{year}</span></h3>
                </div>
                <div class="calendar_nav">
                    <button type="button" class:fcal_nav_active={!prevDisabled} on:click={()=>prev()}>
                        <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-029747aa="">
                            <path fill="currentColor"
                                  d="M685.248 104.704a64 64 0 0 1 0 90.496L368.448 512l316.8 316.8a64 64 0 0 1-90.496 90.496L232.704 557.248a64 64 0 0 1 0-90.496l362.048-362.048a64 64 0 0 1 90.496 0z"></path>
                        </svg>
                    </button>
                    <button type="button" class:fcal_nav_active={!nextDisabled} on:click={()=>next()}>
                        <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-029747aa="">
                            <path fill="currentColor"
                                  d="M338.752 104.704a64 64 0 0 0 0 90.496l316.8 316.8-316.8 316.8a64 64 0 0 0 90.496 90.496l362.048-362.048a64 64 0 0 0 0-90.496L429.248 104.704a64 64 0 0 0-90.496 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <Calendar
                isLoadingDates="{true}"
                selectedDate="{selectedDate}"
                {headers}
                {days}
                on:dayClick={(e)=>dayClick(e.detail)}
            />
        </div>
        {#if selectedDate}
            <div class="fcal_slot_picker">
                <div class="fcal_slot_picker_header">
                    { util.dayjs(selectedDate).format('dddd, MMMM DD') }
                </div>
                <div class="fcal_slot_items">
                    <div class="fcal_spot_lists">
                        {#each daySlots as day}
                            <div
                                class="fcal_spot { selectedDateTime && selectedDateTime.start == day.start ? 'fcal_spot_selected' : '' }">
                                <div aria-label="Select Time" on:click="{(e) => {selectedDateTime = day}}"
                                     on:keypress="{(e) => {selectedDateTime = day}}"
                                     class="fcal_spot_name">
                                     <div class="{ day.remaining && selectedDateTime != day ? 'fcal_spot_time' : '' }">
                                        {util.dayjs(day.start).format('hh:mm A')}
                                    </div>
                                    {#if day.remaining && selectedDateTime != day }
                                        <div class="fcal_spot_remaining">{day.remaining} spots left</div>
                                    {/if}
                                </div>
                                {#if selectedDateTime && selectedDateTime.start == day.start}
                                    <div aria-label="Confirm Time" on:keypress="{(e) => {selectedDateTime = day}}"
                                        on:click={slotSpotConfirmed} class="fcal_spot_confirm">Confirm
                                    </div>
                                {/if}
                            </div>
                        {/each}
                    </div>
                </div>
            </div>
        {/if}
    </div>
    <div class="fcal_timezone_select">
        <label for="fcal_timezone_selector">Timezone</label>
        <TimeZoneSelector placeholder="Select Timezone" bind:timezone={timezone}/>
    </div>
</div>

