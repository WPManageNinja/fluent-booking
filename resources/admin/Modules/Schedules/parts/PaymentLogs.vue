<template>
    <div class="fcal_schedule_event_infos" :class="booking.event_type == 'group' ? 'fcal_schedule_group_payment_log' : ''">
        <div class="fcal_schedule_event_infos_body">
            <div v-if="booking.event_type == 'single'" class="fcal_schedule_details_header">
                <h1 class="fcal_header_title">
                    Payment History
                </h1>
            </div>
            <div v-if="booking.event_type == 'single'" class="fcal_schedule_details_event">
                <div class="fcal_schedule_details_event_item">
                    <h3>Name</h3>
                    <p>{{ booking.first_name }} {{ booking.last_name }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <h3>Email</h3>
                    <p>{{ booking.email }}</p>
                </div>
            </div>


            <table class="fcal_payment_history_table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ booking.order_info?.item_name }}</td>
                        <td>{{ booking.order_info?.quantity }}</td>
                        <td>{{ Math.floor(booking.order_info?.item_price) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th>Total:</th>
                        <td>{{ Math.floor(booking.order_info?.item_total) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    name: "PaymentLogs",
    props: ['booking']
}
</script>
