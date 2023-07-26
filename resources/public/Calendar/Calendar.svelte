<div class="calendar">
    {#each headers as header}
        <span class="day-name">{header}</span>
    {/each}
    {#each days as day}
        {#if day.enabled}
            <span aria-label="Select Day {day.name}" class="day day-enabled { (selectedDate === day.date) ? 'day_is_selected' : ''}" on:keypress={()=>daySelected(day)} on:click={()=>daySelected(day)}>
                <span>{day.name}</span>
            </span>
        {:else}
            <span class="day day-disabled">
                <span>{day.name}</span>
            </span>
        {/if}
    {/each}
</div>

<script>
    import {createEventDispatcher} from 'svelte';

    export var headers = [];
    export let days = [];
    export let selectedDate = null;

    let dispatch = createEventDispatcher();

    function daySelected(day) {
        selectedDate = day.date;
        dispatch('dayClick', day);
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
    .day {
        text-align: center;
        padding: 10px;
        letter-spacing: 1px;
        font-size: 14px;
        box-sizing: border-box;
        color: #98a0a6;
        position: relative;
        z-index: 1;
    }
    .day:nth-of-type(7n + 7) {
        border-right: 0;
    }
    .day:nth-of-type(n + 1):nth-of-type(-n + 7) {
        grid-row: 1;
    }
    .day:nth-of-type(n + 8):nth-of-type(-n + 14) {
        grid-row: 2;
    }
    .day:nth-of-type(n + 15):nth-of-type(-n + 21) {
        grid-row: 3;
    }
    .day:nth-of-type(n + 22):nth-of-type(-n + 28) {
        grid-row: 4;
    }
    .day:nth-of-type(n + 29):nth-of-type(-n + 35) {
        grid-row: 5;
    }
    .day:nth-of-type(n + 36):nth-of-type(-n + 42) {
        grid-row: 6;
    }
    .day:nth-of-type(7n + 1) {
        grid-column: 1/1;
    }
    .day:nth-of-type(7n + 2) {
        grid-column: 2/2;
    }
    .day:nth-of-type(7n + 3) {
        grid-column: 3/3;
    }
    .day:nth-of-type(7n + 4) {
        grid-column: 4/4;
    }
    .day:nth-of-type(7n + 5) {
        grid-column: 5/5;
    }
    .day:nth-of-type(7n + 6) {
        grid-column: 6/6;
    }
    .day:nth-of-type(7n + 7) {
        grid-column: 7/7;
    }
    .day-name {
        font-size: 12px;
        text-transform: uppercase;
        text-align: center;
        line-height: 30px;
        font-weight: 500;
    }
    .day-disabled {
        color: rgb(152 160 166);
        background-color: #ffffff;
        cursor: not-allowed;
    }
    span.day.day-enabled {
        cursor: pointer;
    }

    span.day.day-enabled:hover span {
        background-color: rgba(0,105,255,0.15);
        color: #0060d4;
    }

    span.day.day-enabled span {
        color: #0060e6;
        font-weight: 700;
        display: block;
        width: 30px;
        height: 30px;
        line-height: 30px;
        margin: 0 auto;
        border-radius: 50%;
        background-color: rgba(0,105,255,0.065);
    }

    span.day_is_selected.day.day-enabled span {
        background-color: #0169ff;
        color: white;
    }
</style>
