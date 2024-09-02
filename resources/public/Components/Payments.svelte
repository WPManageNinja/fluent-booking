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

    {#if totalMethod > 1}
        <div class="fcal_payment_methods">
            <div class="fcal_input_label">
                {i18('Payment Method')}
                <span>*</span>
            </div>
            <div class="fcal_payment_radio">
                {#each paymentMethods as method}
                    <label class="fcal_radio_group fcal_payment_label" for={field.name+'_'+method.name} aria-label={method.name}>
                        <input type="radio" bind:group={form[field.name]} id={field.name+'_'+method.name} value={method.name}>
                            <img src={method.icon} alt={method.name} />
                            <span class="fcal_radio_icon"></span>
                    </label>
                {/each}
            </div>
        </div>
    {:else if totalMethod == 0}
        <div class="fcal_validation_error">
            <p>{i18('No_payment_method_description')}</p>
        </div>
    {/if}
</div>
<script>
    import { i18 } from '../util.js';
    import { onMount, afterUpdate } from "svelte";

    export let field;
    export let form;
    export let duration;
    export let quantity;

    let initialValues = [];

    $: quantity, maybeUpdateQuantity();

    const multiPayments = field.multi_payment_items;
    const paymentMethods = Object.values(field?.payment_methods || {});
    const totalMethod = Object.keys(field?.payment_methods || {}).length;

    form[field.name] = form[field.name] || (totalMethod !=0 && paymentMethods[0].name);

    function maybeUpdateQuantity() {
        const paymentElements = document.querySelectorAll('.fcal_payment_amount');
        paymentElements.forEach((element, index) => {
            if (initialValues[index] && quantity > 1) {
                element.textContent = initialValues[index] * quantity;
            }
        });
    };

    onMount(() => {
        const paymentElements = document.querySelectorAll('.fcal_payment_amount');
        paymentElements.forEach(element => {
            initialValues.push(element.textContent);
        });
    });

    afterUpdate(() => {
        maybeUpdateQuantity();
    });
</script>