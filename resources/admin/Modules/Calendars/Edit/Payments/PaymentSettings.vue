<template>
    <div class="fcal_webhook_settings fcal_payment_settings">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header">
                <h2>
                    <el-icon>
                        <Money/>
                    </el-icon>
                    Payment Settings
                </h2>
                <el-button
                    v-if="global_enabled"
                    :disabled="saving"
                    v-loading="saving"
                    type="primary"
                    @click="update()"
                >
                    Update Settings
                </el-button>
            </div>
        </div>
        <div v-if="global_enabled" class="fcal_settings_body" style="min-height: calc(100vh - 320px);">
            <el-skeleton v-if="loading && !paymentSettings.enabled" animated :rows="3"></el-skeleton>
            <el-form v-else :model="paymentSettings" label-position="top">
                <el-form-item>
                    <el-checkbox true-label="yes" false-label="no" v-model="paymentSettings.enabled">
                        Enable this event as Paid and collect payment on booking
                    </el-checkbox>
                </el-form-item>
                <template v-if="paymentSettings.enabled === 'yes'">
                    <el-form-item label="Booking Payment Items">
                        <div>
                            <el-skeleton v-if="loading" animated />
                            <el-row v-else style="margin-bottom: 20px;" :gutter="20" v-for="(item, index) in paymentSettings.items">
                                <el-col :span="14">
                                    <el-input placeholder="Item Name" v-model="item.title"></el-input>
                                </el-col>
                                <el-col :span="8">
                                    <el-input class="fcal_group_input" min="0" type="number" v-model="item.value">
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
                                Add more item
                                <el-icon>
                                    <Plus/>
                                </el-icon>
                            </el-link>
                        </div>
                    </el-form-item>
                </template>
            </el-form>
        </div>
        <div v-if="!global_enabled" class="fcal_settings_body">
            <p class="fcal_empty_text">In order to see this setting, you need to enable global Stripe payment first from the <router-link :to="{name: 'PaymentSettingsIndex',params:{settings_key:'stripe'}}">Settings Page.<span class="anim-icon">👈</span></router-link></p>
        </div>
    </div>
</template>
<script>
import {Back, Link, Plus, Money, Delete} from "@element-plus/icons-vue";
import Popover from "../../../../Components/Popover.vue";

export default {
    name: "PaymentSettings.vue",
    components: {Popover, Link, Plus, Back, Money, Delete},
    props: ['calendar_event'],
    data() {
        return {
            loading: false,
            saving: false,
            global_enabled: false,
            global_config_link: '',
            paymentSettings: {
                enabled: 'no',
                items: [
                    {
                        title: 'Booking Fee',
                        value: 100,
                    },
                ],
            },
            currencies: [],
            calendarId: '',
            eventId: '',
        };
    },
    methods: {
        getSettings() {
            this.loading = false;
            this.$get(`calendars/${this.calendar_event.calendar_id}/slots/${this.calendar_event.id}/payment-settings`, {})
                .then((response) => {
                    this.paymentSettings = response.settings;
                    this.global_enabled = response.global_enabled;
                    this.global_config_link = response.global_config_link;
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                })
        },
        update() {
            this.saving = true;
            this.$post(`calendars/${this.calendar_event.calendar_id}/slots/${this.calendar_event.id}/payment-settings`, {
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
        addItem() {
            this.paymentSettings.items.push({
                title: 'Payment Item',
                value: 10,
            });
        },
    },
    mounted() {
        // this.getCurrencies();
        this.getSettings();
    },
}
</script>

<style lang="scss">
.fcal_payment_flex_row .el-form-item__content {
    display: flex;
    justify-content: space-between !important;

    .header_left {
        font-size: 16px;
        font-weight: 500;
        line-height: 24px;
        color: #1B2533;
    }
}


</style>
