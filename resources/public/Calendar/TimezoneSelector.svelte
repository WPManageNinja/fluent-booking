<script>
    import Select from 'svelte-select';
    const timezones = window.fluentCalendarPublicVars.timezones;

    export let timezone;

    const optionIdentifier = 'value';

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

<Select {floatingConfig} {optionIdentifier} clearable={false} on:input={handleTimeZoneChange} value={timezone} items={timezones} {groupBy} />
