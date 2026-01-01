<div class="fcal_payment_items_wrapper">
    <div class="fcal_payment_items_provider_script"></div>
    <div class="fcal_payment_items">
        <table class="fcal_payment_items_table">
            <thead>
                <tr>
                    <th>{i18('Item')}</th>
                    <th>{i18('Quantity')}</th>
                    <th>{i18('Price')}</th>
                </tr>
            </thead>
            <tbody>
                {#each paymentItems as item}
                    <tr>
                        <td>
                            {getItemTitle(item)}
                        </td>
                        <td>
                            {quantity || 1}
                        </td>
                        <td>
                            <span class="fcal_payment_amount">{@html getCurrencyFormat(getItemPrice(item) * (quantity || 1))}</span>
                        </td>
                    </tr>
                {/each}
            </tbody>
            <tfoot>
                {#if couponField}
                    <tr>
                        <th>{i18('Subtotal:')}</th>
                        <th></th>
                        <th>
                            <span class="fcal_payment_amount">
                                {@html getCurrencyFormat(subTotal)}
                            </span>
                        </th>
                    </tr>
                    {#each Object.values(appliedCoupons || {}) as coupon}
                        <tr class="fcal_applied_coupon">
                            <th>
                                <div class="fcal_coupon_badge">
                                    {coupon.code}
                                    {#if coupon.type == 'percentage'}
                                        ({coupon.rate}%)
                                    {/if}
                                </div>
                                <span class="fcal_inline_remove"
                                    on:click={() => removeCoupon(coupon.code)}
                                    on:keypress={(e) => { removeCoupon(coupon.code) }}>
                                    +
                                </span>
                            </th>
                            <th></th>
                            <th>
                               - {@html getCurrencyFormat(coupon.amount)}
                            </th>
                        </tr>
                    {/each}
                    <tr class="fcal_payment_coupon">
                        <td colspan="2">
                            <div class="fcal_coupon_label"
                                on:click={() => showCouponInput = !showCouponInput}
                                on:keypress={() => showCouponInput = !showCouponInput}>
                                {couponField.label}
                            </div>
                            {#if showCouponInput}
                                <div class="fcal_coupon_input_wrapper">
                                    <CouponIcon />
                                    <input type="text" id="fcal_coupon_input" bind:value={couponCode} placeholder={couponField.placeholder} />
                                    <button type="button" class="fcal_coupon_btn" on:click={applyCoupon} disabled={submitting}>{couponField.apply_button}</button>
                                </div>
                                {#if couponError}
                                    <div class="fcal_validation_error">
                                        <ErrorIcon/>
                                        <p>{couponError}</p>
                                    </div>
                                {/if}
                            {/if}
                        </td>
                    </tr>
                {/if}
                <tr class="fcal_payment_total">
                    <th>{i18('Total:')}</th>
                    <th></th>
                    <th>
                        <span class="fcal_payment_amount">
                            {@html getCurrencyFormat(Math.max(0, total))}
                        </span>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
    {#if totalMethod > 1 || firstPaymentName == 'offline'}
        <div class="fcal_payment_methods">
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
    import { i18, getCurrencyFormat, getErrorText, util } from '../util.js';
    import { onMount } from "svelte";
    import CouponIcon from './Icons/CouponIcon.svelte';
    import ErrorIcon from './Icons/ErrorIcon.svelte';

    export let field;
    export let form;
    export let slotId;
    export let duration;
    export let quantity;
    export let discount;

    let couponCode = '';
    let couponError = '';
    let submitting = false;
    let showCouponInput = false;
    let appliedCoupons = {};

    $: subTotal = getSubTotal() * (quantity || 1);
    $: total = subTotal - discount;

    const multiPayments = !!field.multi_payment_items;
    const paymentItems = multiPayments ? [field.multi_payment_items] : field.payment_items || [];
    const paymentMethods = Object.values(field?.payment_methods || {});
    const totalMethod = Object.keys(field?.payment_methods || {}).length;
    const firstPaymentName = paymentMethods[0]?.name;
    const couponField = field.coupon;

    form[field.name] = form[field.name] || (totalMethod !=0 && firstPaymentName);

    function maybeApplyCoupon() {
        if (!couponField) {
            return;
        }

        const urlParams = new URLSearchParams(window.location.search);
        const coupons = urlParams.get('booking_coupons');
        if (coupons) {
            const couponList = [];
            coupons.split(',').forEach(coupon => {
                couponList.push(coupon.trim());
            });
            applyBulkCoupons(couponList);
        }
    }

    function resetCouponInput() {
        couponCode = '';
        couponError = '';
        showCouponInput = false;
    }

    function resetAppliedCoupons() {
        discount = 0;
        appliedCoupons = {};
        form[couponField.name] = [];
    }

    function addCoupon(coupon) {
        const amount = Number(coupon?.discount_amount) || 0;
        const code = coupon?.coupon_code;
        if (!amount || !code) {
            return;
        }

        form[couponField.name] = form[couponField.name] || [];
        if (form[couponField.name].includes(code)) {
            return;
        }

        form[couponField.name].push(code);
        appliedCoupons[code] = {
            'amount': amount,
            'code': coupon.coupon_code,
            'type': coupon.discount_type,
            'rate': coupon.discount
        };
        discount += amount;
        resetCouponInput();
    }

    function removeCoupon(code) {
        if (!appliedCoupons[code]) {
            return;
        }

        const amount = parseFloat(appliedCoupons[code].amount) || 0;
        discount -= amount;
        delete appliedCoupons[code];
        appliedCoupons = { ...appliedCoupons };
        form[couponField.name] = form[couponField.name].filter(c => c !== code);
        applyBulkCoupons(form[couponField.name]);
    }

    function getItemTitle(item) {
        if (multiPayments) {
            return item[duration].title;
        }
        return item.title;
    }

    function getItemPrice(item) {
        if (multiPayments) {
            return item[duration].value;
        }
        return item.value;
    }

    function getSubTotal() {
        return (paymentItems || []).reduce(
            (sum, item) => sum + parseFloat(getItemPrice(item) || 0),
            0
        );
    }

    function applyCoupon() {
        if (submitting) {
            return;
        }

        submitting = true;
        util.$post(window.fluentCalendarPublicVars.ajaxurl, {
            event_id: slotId,
            quantity: quantity || 1,
            coupon_code: couponCode,
            other_coupons: form[couponField.name] || [],
            action: 'fluent_booking_apply_coupon'
        })
            .then(res => {
                addCoupon(res.coupon);
            })
            .catch(err => {
                couponError = getErrorText(err?.response, 'Invalid coupon code');
            })
            .finally(() => {
                submitting = false;
            });
    }

    function applyBulkCoupons(couponList) {
        submitting = true;
        util.$post(window.fluentCalendarPublicVars.ajaxurl, {
            event_id: slotId,
            quantity: quantity || 1,
            coupon_codes: couponList,
            action: 'fluent_booking_apply_bulk_coupons'
        })
            .then(res => {
                resetAppliedCoupons();
                (res.coupons || []).forEach(coupon => {
                    addCoupon(coupon);
                });
            })
            .catch(err => {
                console.log(err);
            })
            .finally(() => {
                submitting = false;
            });
    }

    onMount(() => {
        maybeApplyCoupon();
    });
</script>