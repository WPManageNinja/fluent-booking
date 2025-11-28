<script>
    export let appData;
    import {onMount} from 'svelte';
    import Select from 'svelte-select';
    import {TelInput, normalizedCountries} from 'svelte-tel-input';

    // E164 formatted value, usually you should store and use this.
    let value = appData.formValue || null;

    let country = null;
    let valid = false;
    if (value) {
        valid = true;
    } else {
        country = window.fluentCalendarPublicVars.user_country || null;
    }

    // Phone number details
    let detailedValue = null;

    let currentInput = '';

    let appMounted = null;

    onMount(() => {
        appMounted = true;
    });

    function handleValueChange(value) {
        let phoneNumber = '';
        if (value && value.isValid) {
            phoneNumber = value.phoneNumber;
            currentInput = value.phoneNumber
        } else if (value) {
            currentInput = value.phoneNumber;
        }
        appData.elem.dispatchEvent(new CustomEvent('value_changed', {
            detail: {
                value: phoneNumber
            }
        }));
    }

    $:handleValueChange(detailedValue);

    function onCountryChanged(country) {
        if (!appMounted) {
            return;
        }
        if (country && country.length > 0) {
            const input = inputRef['$$'].root.querySelector('input.basic-tel-input')
            setTimeout(() => {
                input.focus()
            }, 100)
        }
    }

    $:onCountryChanged(country);

    function handleChange(e) {
        country = e.detail.iso2;
    }

    let floatingConfig = {
        strategy: 'fixed',
    }

    const itemId = 'iso2';
    const label = 'label';
    let inputRef;

</script>

<div class="fcal_phone_wrapper {currentInput ? 'fcal_had_input' : ''}">
    <Select class={valid ? 'fcal_country_select' : 'fcal_country_select invalid'} on:input={handleChange}
            {itemId} {label}
            {floatingConfig}
            clearable={false}
            value={country}
            inputAttributes="{ {autocomplete: 'new-password'} }"
            placeholder="{window.fluentCalendarPublicVars.i18['Country'] || 'Country'}"
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
        bind:this={inputRef}
        class="basic-tel-input {!valid ? 'fcal_invalid' : ''}"
    />
</div>
