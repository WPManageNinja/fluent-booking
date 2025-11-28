<script>
    import Select from 'svelte-select';
    
    export let timezone;

    export let isDisabled;
    
    const timezones = window.fluentCalendarPublicVars.timezones;

    const groupBy = (item) => item.group;

    function handleTimeZoneChange(e) {
        if(e.detail) {
            timezone = e.detail.value;
        } else {
            timezone = '';
        }
    }

    let floatingConfig = {
        strategy: 'fixed',
    }

    const inputAtts = {
        id: 'fcal_timezone_selector'
    }

</script>

<Select 
    id="fcal_timezone_selector"
    class="fcal_timezone_selector {isDisabled ? 'disabled' : ''}"
    on:input={handleTimeZoneChange}
    {groupBy}
    disabled={isDisabled}
    {floatingConfig}
    clearable={false}
    value={timezone}
    items={timezones}
/>
