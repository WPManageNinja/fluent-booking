<template>
    <div class="fcal_schedule_event_infos" :class="multiGuestEvent ? 'fcal_schedule_group_payment_log' : ''">
        <div class="fcal_schedule_event_infos_body">
            <div v-if="singleGuestEvent" class="fcal_schedule_details_header">
                <h1 class="fcal_header_title">
                    {{ $t('Payment History') }}
                </h1>
            </div>
            <div v-if="singleGuestEvent" class="fcal_schedule_details_event">
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
                    <tr v-for="item in payment_order.items" :key="item.id">
                        <td>{{ item.item_name }}</td>
                        <td>{{ item.quantity }}</td>
                        <td>{{ $currencyFormat(item.item_price, true) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr v-if="payment_order.subtotal">
                        <th></th>
                        <th>{{ $t('Subtotal:') }}</th>
                        <td>{{ $currencyFormat(payment_order.subtotal, true) }}</td>
                    </tr>
                    <tr v-for="discount in payment_order.discounts" :key="discount.id">
                        <th></th>
                        <th>{{ $t('Discount') }}({{ discount.item_name }}):</th>
                        <td>{{ '-' + $currencyFormat(discount.item_total, true) }}</td>
                    </tr>
                    <tr>
                        <th></th>
                        <th>{{ $t('Total:') }}</th>
                        <td>{{ $currencyFormat(payment_order.total_amount, true) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div v-if="transaction?.id" class="fcal_payment_transaction_lists">
                <div class="fcal_transaction_label">
                    <h2>{{ $t('Transaction Details') }}</h2>
                    <el-icon class="fcal_clickable" @click="editing = true"><EditPen /></el-icon>
                </div>
                <div class="fcal_schedule_details_event">
                    <div class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Payment Method') }}</h3>
                        <p class="payment_method">{{ transaction.payment_method }}</p>
                    </div>
                    <div v-if="transaction.card_last_4" class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Card Last 4') }}</h3>
                        <p class="card_last_4">
                            <span>{{ transaction.card_brand}}</span>...{{ transaction.card_last_4 }}
                        </p>
                    </div>
                    <div class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Payment Total') }}</h3>
                        <p>{{ $currencyFormat(transaction.total, true) }}</p>
                    </div>
                    <div class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Payment Status') }}</h3>
                        <p class="payment_status" :class="transaction.status">
                            {{ $t(transaction.status) }}
                        </p>
                    </div>
                    <div v-if="transaction.vendor_charge_id" class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Transaction ID') }}</h3>
                        <p :class="transaction.vendor_charge_id">
                            <a v-if="payment_order.payment_method === 'stripe'" :href="'https://dashboard.stripe.com/payments/'+transaction.vendor_charge_id" target="_blank">{{transaction.vendor_charge_id}}</a>
                            <span v-else>{{transaction.vendor_charge_id}}</span>
                        </p>
                    </div>
                    <div v-if="transaction?.meta?.billing_address" class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Billing Address') }}</h3>
                        <p>{{ transaction.meta.billing_address }}</p>
                    </div>
                    <div v-if="transaction?.meta?.shipping_address" class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Shipping Address') }}</h3>
                        <p>{{ transaction.meta.shipping_address }}</p>
                    </div>
                    <div v-if="transaction?.meta?.payment_note" class="fcal_schedule_details_event_item">
                        <h3>{{ $t('Payment Note') }}</h3>
                        <p>{{ transaction.meta.payment_note }}</p>
                    </div>
                </div>
            </div>
        </div>
        <EditTransactionModal
            v-if="editing"
            :show_modal="editing"
            :transaction="transaction"
            :booking_id="booking.id"
            @closeModal="editing = false"
        />
    </div>
</template>

<script>
import EditTransactionModal from './_EditTransactionModal.vue';

export default {
    name: "PaymentLogs",
    props: ['payment_order', 'booking'],
    data() {
        return {
            transaction: this.payment_order.transaction,
            editing: false
        }
    },
    components: {
        EditTransactionModal
    },
    watch: {
        payment_order: {
            handler() {
                this.transaction = this.payment_order.transaction;
            },
            deep: true
        }
    },
    computed: {
        multiGuestEvent() {
            return this.booking.event_type === 'group' || this.booking.event_type === 'group_event';
        },
        singleGuestEvent() {
            return !this.multiGuestEvent;
        }
    }
}
</script>
