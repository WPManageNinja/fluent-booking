<div class="fcal_payment_items_wrapper">
    <div class="fcal_payment_items_provider_script"></div>
    <div class="fcal_payment_items">
        {@html field?.payment_items?.template}
    </div>
    {#if field.payment_methods.length > 1}
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
    {/if}
</div>
<script>
    import { i18 } from '../util.js';

    export let field;
    export let form;

    form[field.name] = form[field.name] || field.payment_methods[0];
</script>