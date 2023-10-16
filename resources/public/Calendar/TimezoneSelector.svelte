<script>
    import Select from 'svelte-select';
    
    export let timezone;
    
    const optionIdentifier = 'value';
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

<Select on:input={handleTimeZoneChange} {groupBy}
        id="fcal_timezone_selector"
    {floatingConfig}
    {optionIdentifier}
    clearable={false}
    value={timezone}
    items={timezones}
/>
