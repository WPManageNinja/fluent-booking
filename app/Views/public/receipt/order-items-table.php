<?php
if (!$order->items) {
    return '';
}
$currencySign = \FluentBooking\App\Services\Integrations\PaymentMethods\CurrenciesHelper::getGlobalCurrencySign();
$currencySetting = [
        'currency_sign' => $currencySign,
];

?>
    <table class="table fluent_booking_order_items_table fluent_booking_table table_bordered">
        <thead>
        <th><?php _e('Item', 'fluent-booking'); ?></th>
        <th><?php _e('Quantity', 'fluent-booking'); ?></th>
        <th><?php _e('Price', 'fluent-booking'); ?></th>
        <th><?php _e('Line Total', 'fluent-booking'); ?></th>
        </thead>
        <tbody>
        <?php $subTotal = 0; ?>
        <?php foreach ($order->items->toArray() as $order_item) {
           if (is_array($order_item)) {
               if ($order_item['item_total']) :?>
                   <tr>
                       <td><?php echo esc_html($order_item['item_name']); ?></td>
                       <td><?php echo esc_html($order_item['quantity']); ?></td>
                       <td><?php echo fcalFormattedAmount($order_item['item_price'], $currencySetting); ?></td>
                       <td><?php echo fcalFormattedAmount($order_item['item_total'], $currencySetting); ?></td>
                   </tr>
                   <?php
                   $subTotal += $order_item['item_total'];
               endif;
           } else {
               if ($order_item->item_total) :?>
                   <tr>
                       <td><?php echo esc_html($order_item->item_name); ?></td>
                       <td><?php echo esc_html($order_item->quantity); ?></td>
                       <td><?php echo fcalFormattedAmount($order_item->item_price, $currencySetting); ?></td>
                       <td><?php echo fcalFormattedAmount($order_item->item_total, $currencySetting); ?></td>
                   </tr>
                   <?php
                   $subTotal += $order_item->item_total;
               endif;
           }

        };
        ?>
        </tbody>
        <tfoot>
        <?php $discountTotal = 0;
        if (isset($order->discounts['applied']) && count($order->discounts['applied'])) : ?>
            <tr class="fluent_booking_total_row">
                <th style="text-align: right" colspan="3"><?php _e('Sub-Total', 'fluent-booking'); ?></th>
                <td><?php echo fcalFormattedAmount($subTotal, $currencySetting); ?></td>
            </tr>
            <?php
            foreach ($order->discounts['applied'] as $discount) :
                $discountTotal += intval($discount->item_total);
                ?>
                <tr class="fluent_booking_discount_row">
                    <th style="text-align: right"
                        colspan="3"><?php echo 'Discounts (' . $discount->item_name . ' )'; ?></th>
                    <td><?php echo '-' . fcalFormattedAmount($discount->item_total, $currencySetting); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        <tr class="fluent_booking_total_payment_row">
            <th style="text-align: right" colspan="3"><?php _e('Total', 'fluent-booking'); ?></th>
            <td>
                <?php if (isset($hasSubscription) && $hasSubscription) : ?> 
                    <?php echo fcalFormattedAmount(intval($order->total_amount), $currencySetting); ?>
                <?php else:  ?> 
                    <?php echo fcalFormattedAmount(intval($order->total_amount - $discountTotal), $currencySetting); ?>
                <?php endif; ?>
            </td>
        </tr>
        </tfoot>
    </table>
