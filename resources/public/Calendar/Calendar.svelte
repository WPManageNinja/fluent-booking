<div class="calendar">
    {#each headers as header}
        <span class="day-name">{getDateTimeStringI18(header, 'day')}</span>
    {/each}
    {#each days as day}
        {#if day.enabled}
            <span role="button" tabindex="0" aria-label="Select Day {day.name}" class="day day-enabled { (selectedDate === day.date) ? 'day_is_selected' : ''}" on:keypress={()=>daySelected(day)} on:click={()=>daySelected(day)}>
                <span class={formatDate(currentDate) == day.date ? 'is-today' : ''}>{day.name}</span>
            </span>
        {:else}
            <span class="day day-disabled">
                <span class={formatDate(currentDate) == day.date ? 'is-today' : ''}>{day.name}</span>
            </span>
        {/if}
    {/each}
</div>

<script>
    import {i18, getDateTimeStringI18} from '../util';
    import {createEventDispatcher} from 'svelte';

    export var headers = [];
    export let days = [];
    export let selectedDate = '';

    let dispatch = createEventDispatcher();

    function daySelected(day) {
        selectedDate = day.date;
        dispatch('dayClick', day);
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
        grid-template-columns: repeat(7, minmax(50px, 6px));
        grid-template-rows: 50px;
        grid-auto-rows: 50px;
        overflow: auto;
        align-items: center;
    }
</style>
