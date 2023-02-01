<script>
    import {util} from '../util';
    import { Pulse } from 'svelte-loading-spinners';
    import TimeZoneSelector from "./TimezoneSelector.svelte";

    export let slot;
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
    let eventText = "";
    let isLoadingDates = false;
    let availableDates = {};
    let daySlots = [];
    let selectedDate = '';
    let selectedDateTime = {};

    var days = [];	//	The days to display in each box

    $: month, year, availableDates, initContent();

    $: timezone, maybeTimeZoneChanged();

    let lastTimeZone = timezone;

    function maybeTimeZoneChanged() {
        if(lastTimeZone != timezone) {
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

    function loadAvailableDates() {
        isLoadingDates = true;
        availableDates = {};
        util.$get('slots/' + slot.id, {
            timezone: timezone,
            start_date: util.dayjs(year +'-'+ (month + 1) + '-', '01').format('YYYY-MM-DD'),
        })
            .then(response => {
                timezone = response.timezone;
                availableDates = response.available_slots;
            })
            .catch(errors => {
                console.log(errors);
            })
            .finally(() => {
                isLoadingDates = false;
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
            let d = new Date( prevMonth == 11 ? year - 1 : year, prevMonth, i + 1);
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
        if(availableDates[day.date]) {
            daySlots = availableDates[day.date];
            selectedDate = day.date;
            dispatch('dayClicked', selectedDate);
        } else {
            daySlots = [];
            selectedDate = '';
        }
    }

    function next() {
        month++;
        if (month == 12) {
            year++;
            month = 0;
        }
        loadAvailableDates();
    }

    function prev() {
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
        <h3>Select a Date & Time</h3>
    </div>
    <div class="fcal_time_picker_head fcal_sec_heading">
        <div aria-label="Back to Date Selection" on:click={(e) => { resetSelection() }} on:keypress={(e) => { resetSelection() }} class="fcal_back fcal_go_back">
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
                    <Pulse color="#0060e6" />
                </div>
            </div>
        {/if}
        <div class="calendar-container">
            <div class="calendar-header">
                <div class="calendar-month-year">
                    <h3>{monthNames[month]} {year}</h3>
                </div>
                <div class="calendar_nav">
                    <button on:click={()=>prev()}>&lt;</button>
                    <button on:click={()=>next()}>&gt;</button>
                </div>
            </div>
            <Calendar
                isLoadingDates="{true}"
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
                        <div class="fcal_spot { selectedDateTime && selectedDateTime.start == day.start ? 'fcal_spot_selected' : '' }">
                            <div aria-label="Select Time" on:click="{(e) => {selectedDateTime = day}}" on:keypress="{(e) => {selectedDateTime = day}}" class="fcal_spot_name">{util.dayjs(day.start).format('hh:mm A')}</div>
                            {#if selectedDateTime && selectedDateTime.start == day.start}
                                <div aria-label="Confirm Time" on:keypress="{(e) => {selectedDateTime = day}}" on:click={slotSpotConfirmed} class="fcal_spot_confirm">Confirm</div>
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
        <TimeZoneSelector placeholder="Select Timezone" bind:timezone={timezone} />
    </div>
</div>

<style>
  .calendar-container {
    width: fit-content;
    overflow: auto;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    background: #fff;
    max-width: 1200px;
  }

  .calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 15px;
    background: #eef;
    border-bottom: 1px solid rgba(166, 168, 179, 0.12);
  }

  .calendar-header button {
      background: #eef;
      border: 1px;
      padding: 6px;
      color: rgba(81, 86, 93, 0.7);
      cursor: pointer;
      outline: 0;
  }

  .calendar-header h3 {
      margin: 0;
      font-size: 18px;
  }
</style>
