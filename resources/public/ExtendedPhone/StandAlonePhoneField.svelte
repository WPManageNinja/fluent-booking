<script>
    export let appData;
    import Select from 'svelte-select';
    import {TelInput, normalizedCountries} from 'svelte-tel-input';

    // E164 formatted value, usually you should store and use this.
    export let value = null;

    // Selected country
    export let country = null;

    // Validity
    export let valid = false;

    // Phone number details
    export let detailedValue = null;

    export let options = {};

    function handleValueChange(value) {
        appData.elem.dispatchEvent(new CustomEvent('value_changed', {
            detail: {
                value: value
            }
        }));
    }

    $:handleValueChange(value);

    function handleChange(e) {
        console.log(e.detail);
        country = e.detail.iso2;
    }

    let floatingConfig = {
    }

    const itemId = 'iso2';
    const label = 'label';

    console.log(normalizedCountries);

</script>

<div class="fcal_phone_wrapper">
    <Select class="fcal_country_select" on:input={handleChange}
            {itemId} {label}
            {floatingConfig}
            clearable={false}
            value={country}
            items={normalizedCountries}
    >
        <div slot="selection" let:selection>
            {#if selection}
                <span class="flag flag-{selection.iso2.toLowerCase()}"></span>
                <span class="fcal_country_code">+{selection.dialCode}</span>
            {/if}
        </div>
        <div slot="item" let:item>
            <span class="flag flag-{item.iso2.toLowerCase()}"></span>
            <span class="fcal_country_name">{item.label}</span>
        </div>
    </Select>
    <TelInput
        bind:country={country}
        bind:value
        bind:valid
        bind:detailedValue
        class="basic-tel-input {!valid ? 'fcal_invalid' : ''}"
    />
</div>
