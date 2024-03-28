<div class="fcal_payment_items_wrapper">
    <div class="fcal_payment_items_provider_script"></div>
    {#if multiPayments}
        <div class="fcal_payment_items">
            <p class="fcal_payment_item_single">{multiPayments[duration]?.title}
                <span class="amount"> {@html field.currency_sign}{multiPayments[duration]?.value}</span>
            </p>
        </div>
    {:else}
        <div class="fcal_payment_items">
            {@html field?.payment_items?.template}
        </div>
    {/if}

    {#if field?.payment_methods?.length > 1}
        <div class="fcal_payment_methods">
            <div class="fcal_input_label">
                {i18('Payment Method')}
                <span>*</span>
            </div>
            <div class="fcal_payment_radio">
                {#each field.payment_methods as method}
                    <label class="fcal_radio_group" for={field.name+'_'+method} aria-label={method}>
                        <input type="radio" bind:group={form[field.name]}
                            id={field.name+'_'+method} value={method}> {method}
                            <span class="fcal_radio_icon"></span>
                    </label>
                {/each}
            </div>
        </div>
    {:else if !field?.payment_methods}
        <div class="fcal_validation_error">
            <p>{i18('No_payment_method_description')}</p>
        </div>
    {/if}
</div>
<script>
    import { i18 } from '../util.js';

    export let field;
    export let form;
    export let duration;

    const multiPayments = field.multi_payment_items;

    form[field.name] = form[field.name] || (field?.payment_methods && field.payment_methods[0]);
</script>