<template>
    <div class="fcal_settings_body_inner fcal_configure_integration_card">
        <div class="fcal_payment_methods_header">
            <div class="fcal_payment_method_tabs">
                <div v-for="(method, key) in paymentMethods" @click="handleClick(key)" :class="['fcal_payment_method_tab', { 'active': route_name === key }]">
                    {{ method?.title || key }}
                </div>
            </div>
        </div>

        <template v-if="!disabled">
            <div v-if="!loading && route_name" class="fcal_calendar_body">
                <Renderer
                    @onSettingsChange="updateSettings"
                    :route_name="route_name"
                    :fields="fields"
                    :settings="settings"
                />
                <div class="fcal_settings_actions">
                    <el-button size="large" :loading="saving" @click="saveSettings()" type="primary">
                        {{ $t('Save Settings') }}
                    </el-button>
                </div>
            </div>
            <el-skeleton v-else :rows="4" animated/>
        </template>
        <ProNotice v-else/>
    </div>
</template>
<script>
import ProNotice from "@/Components/Common/ProNotice.vue";
import Renderer from "@/Modules/Calendars/integrations/Payments/PaymentComponet/Renderer.vue";

export default {
    name: 'PaymentMethodsSettings',
    props: ['disabled'],
    components: {
        Renderer,
        ProNotice
    },
    data() {
        return {
            fields: {},
            settings: {},
            saving: false,
            loading: false,
            route_name: '',
            paymentMethods: this.appVars.payment_methods
        }
    },
    methods: {
        handleClick(val) {
            if (this.route_name == val) {
                return;
            }
            this.route_name = val;
            this.$router.push({
                name: 'payment_methods',
                params: {
                    settings_key: val
                }
            });
            if (!this.disabled) {
                this.getSettings();
            }
        },
        updateSettings(settings) {
            this.settings = settings;
        },
        getSettings() {
            this.loading = true;
            this.$get('integrations/settings/payment-methods', {
                method: this.route_name
            })
                .then((response) => {
                    this.fields = response.fields;
                    this.settings = response.settings;
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        saveSettings() {
            this.saving = true;
            this.$post('integrations/settings/payment-methods', {
                settings: this.settings,
                method: this.route_name
            })
                .then(() => {
                    this.$handleSuccess(this.$t('Settings updated!'));
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        getRoute() {
            this.route_name = this.$route.params?.settings_key || 'stripe';
        },
    },
    mounted() {
        this.getRoute();
        if (!this.disabled) {
            this.getSettings();
        }
    }
}

</script>
