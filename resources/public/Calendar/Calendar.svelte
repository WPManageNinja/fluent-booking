<div class="calendar">
    {#each headers as header}
        <span class="day-name">{getDateTimeStringI18(header, 'day')}</span>
    {/each}
    {#each days as day}
        {#if day.enabled}
            <span role="button" tabindex="0" aria-label="Select Day {day.name}"
                class="day day-enabled { (isSelectedDay(selectedDate, day)) ? 'day_is_selected' : ''}"
                on:keypress={()=>daySelected(day)}
                on:click={()=>daySelected(day)}>
                <span class={formatDate(currentDate) == day.date ? 'is-today' : ''}>{getDateTimeStringI18(day.name, 'mNumber')}</span>
            </span>
        {:else}
            <span class="day day-disabled">
                <span class={formatDate(currentDate) == day.date ? 'is-today' : ''}>{getDateTimeStringI18(day.name, 'mNumber')}</span>
            </span>
        {/if}
    {/each}
</div>

<script>
    import { getDateTimeStringI18 } from '../util';
    import { createEventDispatcher } from 'svelte';

    export var headers = [];
    export let days = [];
    export let selectedDate = '';
    export let selectedDateTimes = [];
    export let isMultiBookingAllow;

    let dispatch = createEventDispatcher();

    function daySelected(day) {
        selectedDate = day.date;
        dispatch('dayClick', day);
    }

    function isSelectedDay(selectedDate, day) {
        if (selectedDate == day.date) {
            return true;
        }
        if (isMultiBookingAllow) {
            return selectedDateTimes.some(dateTime => {
                const date = dateTime.start.split(' ')[0];
                return date == day.date;
            });
        }
        return false;
    }

    let currentDate = new Date();
    function formatDate(date) {
        const year  = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day   = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

</script>

<style>
    .calendar {
        display: grid;
        width: 100%;
        gap: 0.6rem;
        grid-template-columns: repeat(7, minmax(0, 3rem));
        overflow: auto;
        align-items: center;
    }
</style>
