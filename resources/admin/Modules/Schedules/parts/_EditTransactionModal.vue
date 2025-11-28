<template>
    <el-dialog
        v-model="modalVisible"
        width="50%"
        :title="$t('Edit Transaction')"
        class="fcal_modal fcal_location_modal">
        <el-form
            label-position="top"
            class="fcal_location_form">
            <el-form-item :label="$t('Transaction ID')">
                <el-input v-model="transaction.vendor_charge_id" type="text" :placeholder="$t('Transaction ID')"/>
            </el-form-item>
            <el-form-item :label="$t('Billing Address')">
                <el-input v-model="transaction.meta.billing_address" type="textarea" :rows="2" :placeholder="$t('Billing Address')"/>
            </el-form-item>
            <el-form-item :label="$t('Shipping Address')">
                <el-input v-model="transaction.meta.shipping_address" type="textarea" :rows="2" :placeholder="$t('Shipping Address')"/>
            </el-form-item>
            <el-form-item :label="$t('Payment Note')">
                <el-input v-model="transaction.meta.payment_note" type="textarea" :rows="2" :placeholder="$t('Payment Note')"/>
            </el-form-item>
            <el-form-item :label="$t('Payment Status')">
                <el-radio-group v-model="transaction.status">
                    <el-radio v-for="(label, key) in statuses" :key="key" :label="key">
                        {{ label }}
                    </el-radio>
                </el-radio-group>
            </el-form-item>
            
        </el-form>
        <template #footer>
              <span class="dialog-footer">
                <el-button class="fcal_plain_btn" @click="modalVisible = false">
                    {{ $t('Cancel') }}
                </el-button>
                <el-button class="fcal_primary_btn" :loading="updating" :disabled="updating" @click="updateTransaction">
                    {{ $t('Update') }}
                </el-button>
              </span>
        </template>
    </el-dialog>
</template>

<script>
export default {
    name: 'EditTransactionModal',
    props: ['show_modal', 'transaction', 'booking_id'],
    data() {
        return {
            updating: false,
            modalVisible: this.show_modal,
            statuses: {
                paid: this.$t('Paid'),
                pending: this.$t('Pending'),
                failed: this.$t('Failed'),
                refunded: this.$t('Refunded')
            }
        }
    },
    watch: {
        modalVisible() {
            this.$emit('closeModal');
        }
    },
    methods: {
        updateTransaction() {
            this.updating = true;
            this.$put(`schedules/${this.booking_id}/transactions/${this.transaction.id}`, {
                status: this.transaction.status,
                vendor_charge_id: this.transaction.vendor_charge_id,
                meta: {
                    billing_address: this.transaction.meta.billing_address,
                    shipping_address: this.transaction.meta.shipping_address,
                    payment_note: this.transaction.meta.payment_note,
                }
            })
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.modalVisible = false;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                });
        },
    },
    mounted() {
        this.transaction.meta ??= {};
        this.transaction.meta.billing_address ??= '';
        this.transaction.meta.shipping_address ??= '';
        this.transaction.meta.payment_note ??= '';
    },
}
</script>