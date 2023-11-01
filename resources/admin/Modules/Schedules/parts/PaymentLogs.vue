<template>
    <div class="fcal_schedule_event_infos" :class="booking.event_type == 'group' ? 'fcal_schedule_group_payment_log' : ''">
        <div class="fcal_schedule_event_infos_body">
            <div v-if="booking.event_type == 'single'" class="fcal_schedule_details_header">
                <h1 class="fcal_header_title">
                    {{ $t('Payment History') }}
                </h1>
            </div>
            <div v-if="booking.event_type == 'single'" class="fcal_schedule_details_event">
                <div class="fcal_schedule_details_event_item">
                    <h3>{{ $t('Name') }}</h3>
                    <p>{{ booking.first_name }} {{ booking.last_name }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <h3>{{ $t('Email') }}</h3>
                    <p>{{ booking.email }}</p>
                </div>
            </div>

            <table class="fcal_payment_history_table">
                <thead>
                    <tr>
                        <th>{{ $t('Name') }}</th>
                        <th>{{ $t('Quantity') }}</th>
                        <th>{{ $t('Price') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in booking.payment_order.items" :key="item.id">
                        <td>{{ item.item_name }}</td>
                        <td>{{ item.quantity }}</td>
                        <td><span v-html="currencySign"></span>{{ Math.floor(item.item_price / 100) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th>{{ $t('Total:') }}</th>
                        <td><span v-html="currencySign"></span>{{ Math.floor(booking.payment_order.total_amount / 100) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div v-if="booking.payment_order.transaction && booking.payment_order.transaction.id" class="fcal_payment_transaction_lists">
                <h2>{{ $t('Transaction Details') }}</h2>
                <div class="fcal_schedule_details_event">
                    <div class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Payment Method') }}</h3>
                        <p class="payment_method">{{ booking.payment_order.transaction.payment_method }}</p>
                    </div>
                    <div v-if="booking.payment_order.transaction.card_last_4" class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Card Last 4') }}</h3>
                        <p class="card_last_4">
                            <span>{{ booking.payment_order.transaction.card_brand}}</span>...{{ booking.payment_order.transaction.card_last_4 }}
                        </p>
                    </div>
                    <div class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Payment Total') }}</h3>
                        <p>
                            <span v-html="currencySign"></span>{{ Math.floor(booking.payment_order.transaction.total / 100) }}
                        </p>
                    </div>
                    <div class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Payment Status') }}</h3>
                        <p class="payment_status" :class="booking.payment_order.transaction.status">
                            {{ $t(booking.payment_order.transaction.status) }}
                        </p>
                    </div>
                    <div v-if="booking.payment_order.transaction.vendor_charge_id" class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Transaction ID') }}</h3>
                        <p :class="booking.payment_order.transaction.vendor_charge_id">
                            <a :href="'https://dashboard.stripe.com/payments/'+booking.payment_order.transaction.vendor_charge_id" target="_blank">{{booking.payment_order.transaction.vendor_charge_id}}</a>

                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "PaymentLogs",
    props: ['booking'],
    data() {
        return {
            currencySign: window.fluentFrameworkAdmin?.currency_sign
        }
    }
}
</script>
