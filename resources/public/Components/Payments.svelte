<div class="fcal_payment_items_wrapper">
    <div class="fcal_payment_items_provider_script"></div>
    <div class="fcal_payment_items">
            {@html field?.payment_items?.template}
    </div>
</div>
<script>
    export let field = {};

    let getSubTotal = (items) => {
        let subtotal = 0;
        for (let item of items) {
            subtotal += parseFloat(item.value);
        }
        return subtotal;
    }

</script>