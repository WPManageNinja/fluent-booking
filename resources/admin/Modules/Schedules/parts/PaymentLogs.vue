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
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ booking.order?.payment_method }}</td>
                        <td>{{ booking.order?.status }}</td>
                        <td><span class="currency">{{ booking.order?.currency }}</span> {{ Math.floor(booking.order?.total_amount) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th>Total:</th>
                        <td><span class="currency">{{ booking.order?.currency }}</span> {{ Math.floor(booking.order?.total_amount) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    name: "PaymentLogs",
    props: ['booking'],
    data() {
        return {
            tableData: [
                {
                    date: '2016-05-03',
                    name: 'Tom',
                    address: 'No. 189, Grove St, Los Angeles',
                },
                {
                    date: '2016-05-02',
                    name: 'Tom',
                    address: 'No. 189, Grove St, Los Angeles',
                },
                {
                    date: '2016-05-04',
                    name: 'Tom',
                    address: 'No. 189, Grove St, Los Angeles',
                },
                {
                    date: '2016-05-01',
                    name: 'Tom',
                    address: 'No. 189, Grove St, Los Angeles',
                },
            ]
        }
    }
}
</script>

<style scoped>

</style>