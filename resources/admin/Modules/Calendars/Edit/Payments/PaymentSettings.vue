<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_webhook_settings fcal_payment_settings">
            <div class="fcal_create_calendar_form">
                <div class="fcal_create_calendar_form_header">
                    <h2><el-icon><Money/></el-icon>
                        {{ $t('Payment Settings') }}
                    </h2>
                </div>
            </div>
            <el-skeleton v-if="loading" animated :rows="3"></el-skeleton>
            <div v-else-if="global_enabled" class="fcal_settings_body">
                <el-form :model="paymentSettings" label-position="top">
                    <el-form-item>
                        <div class="fcal_group_checkbox">
                            <el-checkbox true-label="yes" false-label="no" v-model="paymentSettings.enabled">
                                {{ $t('PaymentSettings/enable_payment_description') }}
                            </el-checkbox>
                            <el-checkbox v-if="displayMultiEnabled" true-label="yes" false-label="no" v-model="paymentSettings.multi_payment_enabled">
                                {{ $t('PaymentSettings/multi_payment_description') }}
                            </el-checkbox>
                        </div>
                    </el-form-item>
                    <template v-if="paymentSettings.enabled === 'yes'">
                        <el-form-item :label="$t('Checkout Method')">
                            <el-radio-group v-model="paymentSettings.driver">
                                <el-radio label="native">
                                    {{ $t('Use Native Payment Methods by FluentBooking') }}
                                </el-radio>
                                <el-radio v-if="paymentConfig.has_woo" :disabled="!paymentConfig.woo_enabled" label="woo">
                                    {{ $t('Use WooCommerce Checkout') }}
                                </el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <template v-if="paymentSettings.driver == 'native'">
                            <template v-if="paymentConfig.native_enabled">
                                <template v-if="isPaymentConfigured">
                                    <div class="fcal_payment_enable_checkbox">
                                        <el-checkbox v-if="paymentConfig.stripe_configured" true-label="yes" false-label="no" v-model="paymentSettings.stripe_enabled">
                                            {{ $t('PaymentSettings/enable_stripe_description') }}
                                        </el-checkbox>
                                        <el-checkbox v-if="paymentConfig.paypal_configured" true-label="yes" false-label="no" v-model="paymentSettings.paypal_enabled">
                                            {{ $t('PaymentSettings/enable_paypal_description') }}
                                        </el-checkbox>
                                    </div>
                                    <el-form-item :label="$t('Booking Payment Items')" class="fcal_payment_label">
                                        <template v-if="!loading">                                          
                                            <div v-if="displayMultiOption" class="fcal_multi_payments">
                                                <el-row class="fcal_multi_payments_header">
                                                    <el-col :span="6">
                                                        <p>{{ $t('Meeting Duration') }}</p>
                                                    </el-col>
                                                    <el-col :span="12">
                                                        <p>{{ $t('Payment Title') }}</p>
                                                    </el-col>
                                                    <el-col :span="6">
                                                        <p>{{ $t('Amount') }}</p>
                                                    </el-col>
                                                </el-row>
                                                <template v-if="!loading" v-for="(item, index) in paymentSettings.multi_payment_items" :key="index">
                                                    <el-row v-if="isDurationAvailable(index)">
                                                        <el-col :span="6">
                                                            <p>{{ getDuration(index) }}</p>
                                                        </el-col>
                                                        <el-col :span="12">
                                                            <el-input :placeholder="$t('Item Name')" v-model="item.title"></el-input>
                                                        </el-col>
                                                        <el-col :span="6">
                                                            <el-input class="fcal_group_input" min="0" type="number"
                                                                v-model="item.value">
                                                                <template #prepend>{{ appVars.currency_sign }}</template>
                                                            </el-input>
                                                        </el-col>
                                                    </el-row>
                                                </template>
                                            </div>
                                            <div v-else>
                                                <el-row style="margin-bottom: 20px;" :gutter="20"
                                                        v-for="(item, index) in paymentSettings.items">
                                                    <el-col :span="14">
                                                        <el-input :placeholder="$t('Item Name')" v-model="item.title"></el-input>
                                                    </el-col>
                                                    <el-col :span="8">
                                                        <el-input class="fcal_group_input" min="0" type="number"
                                                                v-model="item.value">
                                                            <template #prepend>{{ appVars.currency_sign }}</template>
                                                        </el-input>
                                                    </el-col>
                                                    <el-col :span="2" class="action_btn">
                                                        <span v-if="index > 0" @click="()=>{ paymentSettings.items.splice(index, 1); }">
                                                            <el-icon><Delete/></el-icon>
                                                        </span>
                                                    </el-col>
                                                </el-row>
                                                <el-link @click="addItem" style="cursor: pointer;">
                                                    {{ $t('Add more item') }}
                                                    <el-icon>
                                                        <Plus/>
                                                    </el-icon>
                                                </el-link>
                                            </div>
                                        </template>
                                        <div v-else class="fcal_skeleton">
                                            <el-skeleton :rows="3" animated/>
                                        </div>
                                    </el-form-item>
                                </template>
                                <p v-else class="fcal_empty_text">
                                    {{ $t('PaymentSettings/enable_payment_settings') }}
                                    <router-link :to="{name: 'PaymentSettingsIndex',params:{settings_key:'stripe'}}">
                                        {{ $t('Stripe') }}
                                    </router-link> {{ $t('or') }}
                                    <router-link :to="{name: 'PaymentSettingsIndex',params:{settings_key:'paypal'}}">
                                        {{ $t('PayPal') }}
                                    </router-link>
                                    {{ $t('PaymentSettings/from_global_settings') }}
                                </p>
                            </template>
                            <p v-else class="fcal_empty_text">
                                {{ $t('PaymentSettings/enable_global_payment_settings') }}
                                <router-link :to="{name: 'general_settings'}">
                                    {{ $t('Go to Payment Settings') }}. <span class="anim-icon">👈</span>
                                </router-link>
                            </p>
                        </template>
                        <template v-else-if="paymentSettings.driver == 'woo'">
                            <el-form-item label="Select WooCommerce Product" class="fcal_payment_label">
                                <div v-if="displayMultiOption" class="fcal_multi_payments">
                                    <el-row class="fcal_multi_payments_header">
                                        <el-col :span="6">
                                            <p>{{ $t('Meeting Duration') }}</p>
                                        </el-col>
                                        <el-col :span="12">
                                            <p>{{ $t('Product') }}</p>
                                        </el-col>
                                    </el-row>
                                    <template v-if="!loading" v-for="(item, index) in paymentSettings.multi_payment_woo_ids" :key="index">
                                        <el-row v-if="isDurationAvailable(index)" class="fcal_woo_row">
                                            <el-col :span="6">
                                                <p>{{ getDuration(index) }}</p>
                                            </el-col>
                                            <el-col :span="16">
                                                <woo-product-selector v-model="paymentSettings.multi_payment_woo_ids[index]"/>
                                            </el-col>
                                        </el-row>
                                    </template>
                                </div>
                                <woo-product-selector v-else v-model="paymentSettings.woo_product_id"/>
                                <p>{{ $t('PaymentSettings/woo_payment_description') }}</p>
                            </el-form-item>
                        </template>
                    </template>
                </el-form>
            </div>
            <div v-else class="fcal_settings_body">
                <p class="fcal_empty_text">
                    {{ $t('PaymentSettings/enable_global_payment_settings') }}
                </p>
            </div>
            <div v-if="global_enabled && !loading" class="fcal_create_calendar_form_footer">
                <SaveButton :saving="saving" :label="$t('Save Changes')" @click="saveSettings"/>
            </div>
        </div>
    </div>
</template>
<script>
import { Back, Link, Plus, Money, Delete } from "@element-plus/icons-vue";
import WooProductSelector from "@/Pieces/WooProductSelector";
import SaveButton from "@/Components/Buttons/SaveButton.vue";

export default {
    name: "PaymentSettings.vue",
    components: { Link, Plus, Back, Money, Delete, WooProductSelector, SaveButton },
    props: ['calendar_event'],
    data() {
        return {
            loading: true,
            saving: false,
            global_config_link: '',
            paymentSettings: {
                enabled: 'no',
                driver: 'native',
                multi_payment_enabled: 'no',
                items: [
                    {
                        title: 'Booking Fee',
                        value: 100,
                    },
                ],
                woo_product_id: '',
                multi_payment_items: [],
                multi_payment_woo_ids: [],
            },
            paymentConfig: {},
            multiDuration: [],
            currencies: [],
            calendarId: '',
            eventId: '',
        };
    },
    computed: {
        global_enabled() {
            return this.paymentConfig.native_enabled || this.paymentConfig.woo_enabled;
        },
        isPaymentConfigured() {
            return this.paymentConfig.stripe_configured || this.paymentConfig.paypal_configured;
        },
        displayMultiEnabled() {
            return this.paymentSettings?.enabled == 'yes' && this.multiDuration?.enabled;
        },
        multiOptionEnabled() {
            return this.paymentSettings?.multi_payment_enabled == 'yes';
        },
        displayMultiOption() {
            return this.multiOptionEnabled && this.multiDuration?.enabled;
        }
    },
    methods: {
        getSettings() {
            this.loading = true;
            this.$get(`calendars/${this.calendar_event.calendar_id}/events/${this.calendar_event.id}/payment-settings`, {})
                .then((response) => {
                    this.paymentConfig = response.config;
                    this.paymentSettings = response.settings;
                    this.maybeUpdatePaymentItems();
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                })
        },
        saveSettings() {
            this.saving = true;
            this.$post(`calendars/${this.calendar_event.calendar_id}/events/${this.calendar_event.id}/payment-settings`, {
                settings: this.paymentSettings,
            })
                .then((response) => {
                    this.$handleSuccess(response.message);
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        isDurationAvailable(duration) {
            return this.multiDuration.available_durations.includes(duration);
        },
        getDuration(duration) {
            return duration + ' ' + this.$t('Minutes');
        },
        addItem() {
            this.paymentSettings.items.push({
                title: 'Payment Item',
                value: 10,
            });
        },
        maybeUpdatePaymentItems() {
            if (this.multiDuration.enabled) {
                if (this.paymentConfig?.native_enabled) {
                    this.multiDuration.available_durations.forEach(duration => {
                        this.paymentSettings.multi_payment_items[duration] ??= {
                            title: 'Booking Fee',
                            value: 0,
                        };
                    })
                }
                if (this.paymentConfig?.has_woo) {
                    this.multiDuration.available_durations.forEach(duration => {
                        this.paymentSettings.multi_payment_woo_ids[duration] ??= ''
                    })
                }
            }
        },
    },
    mounted() {
        this.getSettings();
        this.multiDuration = this.calendar_event.settings.multi_duration;
    }
}
</script>
