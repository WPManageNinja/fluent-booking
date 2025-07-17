<div class="fcal_payment_items_wrapper">
    <div class="fcal_payment_items_provider_script"></div>
    {#if multiPayments}
        <div class="fcal_payment_items">
            <p class="fcal_payment_item_single">{multiPayments[duration]?.title}
                {@html getCurrencyFormat(multiPayments[duration]?.value)}
            </p>
        </div>
    {:else}
        <div class="fcal_payment_items">
            {@html field?.payment_items?.template}
        </div>
    {/if}

    {#if totalMethod > 1 || firstPaymentName == 'offline'}
        <div class="fcal_payment_methods">
            <div class="fcal_input_label">
                {i18('Payment Method')}
                <span>*</span>
            </div>
            <div class="fcal_payment_radio">
                {#each paymentMethods as method}
                    <label class="fcal_radio_group fcal_payment_label" for={field.name+'_'+method.name} aria-label={method.name}>
                        <input type="radio" bind:group={form[field.name]} id={field.name+'_'+method.name} value={method.name}>
                        {#if method.use_icon}
                            <img src={method.icon} alt={method.name} />
                            <span class="fcal_radio_icon"></span>
                        {:else}
                            {method.label} <span class="fcal_radio_icon"></span>
                        {/if}
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
    import { i18, getCurrencyFormat } from '../util.js';
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
    const firstPaymentName = paymentMethods[0]?.name;

    form[field.name] = form[field.name] || (totalMethod !=0 && firstPaymentName);

    function maybeUpdateQuantity() {
        const paymentElements = document.querySelectorAll('.fcal_payment_amount');
        paymentElements.forEach((el, i) => {
            let val = initialValues[i];
            if (!val) return;
            if (quantity > 1) {
                let num = parseFloat(val.replace(/[^0-9.\-]/g, ''));
                let sign = val.replace(/[\d\.\-,\s]/g, '');
                let total = (num * quantity).toFixed(2);
                el.textContent = val.trim().startsWith(sign) ? `${sign} ${total}` : `${total}${sign ? ' ' + sign : ''}`;
            } else {
                el.textContent = val;
            }
        });
    }

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