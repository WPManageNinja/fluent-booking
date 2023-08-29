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

</script>

<Select on:input={handleTimeZoneChange} {groupBy}
    {floatingConfig}
    {optionIdentifier}
    clearable={false}
    value={timezone}
    items={timezones}
/>
