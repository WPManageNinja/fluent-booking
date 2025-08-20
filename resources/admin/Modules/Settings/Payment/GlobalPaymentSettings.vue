<template>
    <div class="fcal_settings_body_inner fcal_settings_general">
        <div class="fcal_configure_integration_card">
            <div class="fcal_configure_integration_card_header">
                <div class="left">
                    <div class="content">
                        <h3>{{ $t('Payment Settings') }}</h3>
                        <p>{{ $t('GeneralSettings/payment_settings_description') }}</p>
                    </div>
                </div>
            </div>
            <template v-if="!disabled">
                <el-skeleton animated v-if="loading"></el-skeleton>
                <div v-else class="fcal_configure_integration_body">
                    <el-form v-model="payments" label-position="top">
                        <el-row>
                            <el-form-item>
                                <el-checkbox v-model="payments.is_active" true-label="yes" false-label="no">
                                    {{ $t('Enable Payment Module') }}
                                </el-checkbox>
                            </el-form-item>
                        </el-row>
                        <el-row :gutter="30">
                            <el-col :sm="24" :md="8">
                                <el-form-item :label="$t('Currency')">
                                    <el-select filterable v-model="payments.currency" popper-class="fcal_select" :placeholder="$t('Select')" placement="bottom">
                                        <el-option
                                            v-for="currency in all_currencies"
                                            :key="currency.value"
                                            :label="currency.label"
                                            :value="currency.value"
                                        />
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :sm="24" :md="8">
                                <el-form-item :label="$t('Number Format')">
                                    <el-select v-model="payments.number_format" popper-class="fcal_select" :placeholder="$t('Select')" placement="bottom">
                                        <el-option value="comma_separated" :label="`${$t('US Style')} (1,000,00.00)`"></el-option>
                                        <el-option value="dot_separated" :label="`${$t('EU Style')} (1.000.00,00)`"></el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :sm="24" :md="8">
                                <el-form-item :label="$t('Currency Position')">
                                    <el-select v-model="payments.currency_position" popper-class="fcal_select" :placeholder="$t('Select')" placement="bottom">
                                        <el-option value="left" :label="`${$t('Left')} (${appVars.currency_sign}100)`"></el-option>
                                        <el-option value="right" :label="`${$t('Right')} (100${appVars.currency_sign})`"></el-option>
                                        <el-option value="left_space" :label="`${$t('Left Space')} (${appVars.currency_sign} 100)`"></el-option>
                                        <el-option value="right_space" :label="`${$t('Right Space')} (100 ${appVars.currency_sign})`"></el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        <div v-if="payments.is_active == 'no'" class="fcal_tips_error">
                            <p> {{ $t('GeneralSettings/payment_tips_error') }} </p>
                        </div>
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
    name: 'GlobalPaymentSettings',
    props: ['disabled'],
    components: {
        ProNotice
    },
    data() {
        return {
            saving: false,
            loading: false,
            payments: {},
            all_currencies: {}
        }
    },
    methods: {
        getPaymentSettings() {
            this.loading = true;
            this.$get('settings/general')
                .then(response => {
                    this.payments = response.payments;
                    this.all_currencies = response.all_currencies;
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        savePaymentSettings() {
            this.saving = true;
            this.$post('settings/payment', {
                payments: this.payments
            })
                .then(response => {
                    this.$notify.success(response.message);
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.saving = false;
                });
        }
    },
    mounted() {
        this.getPaymentSettings();
    }
}

</script>
