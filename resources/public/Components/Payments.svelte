<div class="fcal_payment_items_wrapper">
    <div class="fcal_payment_items_provider_script"></div>
    <div class="fcal_payment_items">
        <table>
            <thead>
            <tr style="background: #e0e0e0;">
                <th>Item</th>
                <th>Price</th>
            </tr>
            </thead>
            <tbody>
            {#each field.payment_items as item}
                <tr>
                    <td>
                        <p>{item.title}</p>
                        <input type="hidden" disabled="true" value="{item.value}" class="fcal_input"/>
                    </td>
                    <td>
                        <p>
                            <span>{@html field.currency_sign}</span>
                            {item.value}
                        </p>
                    </td>
                </tr>
            {/each}
            <tr>
                <td>
                    <p>Total:</p>
                </td>
                <td>
                    <p>
                        <span>{@html field.currency_sign}</span>
                        {getSubTotal(field.payment_items)}
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
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