<template>
    <div class="fcal_settings_body_inner fcal_settings_general">
        <div class="fcal_configure_integration_card">
            <div class="fcal_configure_integration_card_header">
                <div class="left">
                    <div class="content">
                        <h3>{{ $t('Manage Coupons') }}</h3>
                        <p>{{ $t('Manage your payment coupons and create discount codes for your customers here.') }}</p>
                    </div>
                </div>
            </div>
            <template v-if="false">
                <el-skeleton animated v-if="loading"></el-skeleton>
                <div v-else class="fcal_configure_integration_body">
                    <el-form v-model="coupons" label-position="top">
                        <el-form-item :label="$t('Coupon Code')">
                            <el-input v-model="coupons.code" :placeholder="$t('Enter Coupon Code')" />
                        </el-form-item>
                    </el-form>
                    <div style="margin-top: 20px;" class="fcal_settings_footer">
                        <el-button :disabled="saving" v-loading="saving" @click="savePaymentSettings()" class="fcal_primary_btn">
                            {{ $t('Save Settings') }}
                        </el-button>
                    </div>
                </div>
            </template>
            <ProNotice v-else/>
        </div>
    </div>
</template>

<script>
import ProNotice from "@/Components/Common/ProNotice.vue";

export default {
    name: 'PaymentCouponsSettings',
    props: ['disabled'],
    components: {
        ProNotice
    },
    data() {
        return {
            saving: false,
            loading: false,
            coupons: {}
        }
    },
    methods: {
        getCoupons() {
            this.loading = true;
            this.$get('settings/coupons')
                .then(response => {
                    this.coupons = response.coupons;
                })
        },
        addCoupons() {
            this.saving = true;
        }
    },
    mounted() {
        // this.getCoupons();
    }
}
</script>