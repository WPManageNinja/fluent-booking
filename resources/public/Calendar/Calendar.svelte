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
    export let selectedDate = '';

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
</style>
