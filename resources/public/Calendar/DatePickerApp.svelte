<script>
    import {util, i18, getDateTimeStringI18, dateTimeI18} from '../util';
    import Calendar from "./Calendar.svelte";
    import {Pulse} from 'svelte-loading-spinners';
    import TimeZoneSelector from "./TimezoneSelector.svelte";
    import {createEventDispatcher, onMount} from 'svelte';

    export let slot;
    export let timezone;
    export let appData;
    
    const isFluentform = appData.is_fluentform;
    const isFFConversational = appData.isFFConversational;

    const id = appData.id;

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
    let isLoadingDates = false;
    let availableDates = {};
    let daySlots = [];
    let selectedDate = '';
    let selectedDateTime = {};
    let nextDisabled = false;
    let formatHours = appData.slot?.time_format;

    if (slot.pre_selects) {
        month = slot.pre_selects.month - 1;
        year = slot.pre_selects.year;
    }

    var days = [];	//	The days to display in each box

    $: month, year, availableDates, initContent(), maybeMaxDateDisabled();

    $: timezone, maybeTimeZoneChanged();

    let lastTimeZone = timezone;

    let start_time;

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
            event_id: slot.id,
            timezone: timezone || '',
            action: 'fluent_cal_get_available_dates',
            start_date: util.dayjs(year + '-' + (month + 1) + '-', '01').format('YYYY-MM-DD'),
        })
            .then(response => {
                timezone = response.timezone;
                availableDates = response.available_slots;

                if (firstLoading && slot.pre_selects && slot.pre_selects.day) {
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

    function dayClick(day) {
        if (availableDates[day.date]) {
            daySlots = availableDates[day.date];
            selectedDate = day.date;

            if (daySlots.length == 1) {
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


    function slotSpotConfirmed() {
        dispatch('spotSelected', selectedDateTime);
        setTimeout(() => {
            selectedDateTime = {};
        }, 1000);
    }

    function slotSpotForFluentForm(day) {
        selectedDateTime = day;
        if (!isFluentform) {
            return;
        }
        if (selectedDateTime) {
            start_time = selectedDateTime.start;
        }

        if (isFFConversational) {
            appData.element.dispatchEvent(new CustomEvent('value.update', {
                detail: {
                    value: JSON.stringify({ id, timezone, start_time })
                }
            }));
        }

    }

    function resetSelection() {
        selectedDate = '';
        selectedDateTime = {};
        dispatch('resetSelection');
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
        return `${getDateTimeStringI18(hours, 'mNumber')}:${getDateTimeStringI18(minutes, 'mNumber')} ${formatHr === '12' ? `${i18(modifier)}` : ''}`;
    }


</script>

<div class="fcal_day_picker">
    <div class="fcal_calendar_slot_wrap {selectedDate ? 'is_active' : ''}">
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
                    <h3>{getDateTimeStringI18(monthNames[month], 'month')} <span>{year}</span></h3>
                </div>
                <div class="calendar_nav">
                    <button aria-label="Previous Month" type="button" class:fcal_nav_active={!prevDisabled} on:click={()=>prev()}>
                        <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-029747aa="">
                            <path fill="currentColor"
                                  d="M685.248 104.704a64 64 0 0 1 0 90.496L368.448 512l316.8 316.8a64 64 0 0 1-90.496 90.496L232.704 557.248a64 64 0 0 1 0-90.496l362.048-362.048a64 64 0 0 1 90.496 0z"></path>
                        </svg>
                    </button>
                    <button aria-label="Next Month" type="button" class:fcal_nav_active={!nextDisabled} on:click={()=>next()}>
                        <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-029747aa="">
                            <path fill="currentColor"
                                  d="M338.752 104.704a64 64 0 0 0 0 90.496l316.8 316.8-316.8 316.8a64 64 0 0 0 90.496 90.496l362.048-362.048a64 64 0 0 0 0-90.496L429.248 104.704a64 64 0 0 0-90.496 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <Calendar
                selectedDate="{selectedDate}"
                {headers}
                {days}
                on:dayClick={(e)=>dayClick(e.detail)}
            />

            <div class="fcal_timezone_select">
                <label for="fcal_timezone_selector">{i18('Timezone')}</label>
                <TimeZoneSelector bind:timezone={timezone}/>
            </div>
        </div>

        <div class="fcal_slot_picker { selectedDate ? 'is_active' : ''}">
            <div class="fcal_slot_picker_header">
                { dateTimeI18(selectedDate, 'dddd, MMM') } {getDateTimeStringI18(dateTimeI18(selectedDate, 'DD'), 'mNumber')}

                <div class="fcal_slot_picker_header_action">
                    <div class="format-hour">
                        <input type="radio" id="12_hours_selector" bind:group={formatHours} value="12"/>
                        <label for="12_hours_selector">{i18('12h')}</label>
                    </div>
                    <div class="format-hour">
                        <input type="radio" id="24_hours_selector" bind:group={formatHours} value="24"/>
                        <label for="24_hours_selector">{i18('24h')}</label>
                    </div>
                </div>
            </div>
            <div class="fcal_slot_items">
                <div class="fcal_spot_lists">
                    {#each daySlots as day}
                        <div
                            class="fcal_spot { selectedDateTime && selectedDateTime.start == day.start ? 'fcal_spot_selected' : '' }">
                            <div role="button" tabindex="0" aria-label="Select Time" on:click="{slotSpotForFluentForm(day)}"
                                 on:keypress="{(e) => {selectedDateTime = day}}"
                                 class="fcal_spot_name">
                                <div class="{ day.remaining && selectedDateTime != day ? 'fcal_spot_time' : '' }">
                                    {convertTime12to24(util.dayjs(day.start).format('hh:mm A'), formatHours)}
                                    <!--{convertTime12to24(util.dateTimeI18(util.dayjs(day.start).format('hh:mm A')), formatHours)}-->
                                    <!--{util.dateTimeI18(util.dayjs(day.start).format('hh:mm'))}-->
                                    <!--{convertTime12to24(util.dateTimeI18(day.start, 'HH:mm A'), formatHours)}-->
                                    <!--{getDateTimeStringI18(convertTime12to24(util.dayjs(day.start).format('hh:mm A'), formatHours), 'mNumber')}-->
                                </div>
                                {#if day.remaining && selectedDateTime != day }
                                    <div class="fcal_spot_remaining">{day.remaining} {i18('spots left')}</div>
                                {/if}
                            </div>
                            {#if selectedDateTime && selectedDateTime.start == day.start}
                                {#if isFluentform}
                                        <span class="fcal_spot_confirm">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" width="60px"
                                                 height="60px"><path
                                                d="M 26.980469 5.9902344 A 1.0001 1.0001 0 0 0 26.292969 6.2929688 L 11 21.585938 L 4.7070312 15.292969 A 1.0001 1.0001 0 1 0 3.2929688 16.707031 L 10.292969 23.707031 A 1.0001 1.0001 0 0 0 11.707031 23.707031 L 27.707031 7.7070312 A 1.0001 1.0001 0 0 0 26.980469 5.9902344 z"/></svg>
                                        </span>
                                {:else }
                                    <div aria-label="Confirm Time" on:keypress="{(e) => {selectedDateTime = day}}"
                                         on:click={slotSpotConfirmed} class="fcal_spot_confirm"> {i18('Next')}
                                    </div>
                                {/if}
                            {/if}
                        </div>
                    {/each}
                </div>
            </div>
        </div>
    </div>
</div>
{#if isFluentform}
    <div>
        <input type="hidden" name={appData.name} value={JSON.stringify({ id, timezone, start_time })}/>
    </div>
{/if}
