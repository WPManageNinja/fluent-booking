<template>
    <div class="fcal_settings_body_inner">
        <div class="fcal_settings_header">
            <template v-if="!disabled">
                <div v-if="!loading" class="fcal_settings_head">
                    <h2>{{ fields?.label }}</h2>
                    <p>{{ fields?.description }}</p>
                </div>
                <el-skeleton v-else :rows="1" animated/>
            </template>
            <div v-else-if="headerInfo">
                <div class="fcal_settings_head">
                    <h2>{{ headerInfo.label }}</h2>
                    <p>{{ headerInfo.description }}</p>
                </div>
            </div>
            <div v-if="!disabled" class="fcal_settings_actions">
                <el-button size="large" :loading="saving" @click="saveSettings()" type="primary">
                    {{ $t('Save Settings') }}
                </el-button>
            </div>
        </div>

        <template v-if="!disabled">
            <div v-if="!loading" class="fcal_calendar_body">
                <Renderer
                    @onSettingsChange="updateSettings"
                    :route_name="route_name"
                    :fields="fields"
                    :settings="settings"/>
            </div>
            <el-skeleton v-else :rows="4" animated/>
        </template>
        <ProNotice v-else/>
    </div>
</template>
<script>
import ProNotice from "@/Components/Common/ProNotice.vue";
import Renderer from "../Payments/PaymentComponet/Renderer.vue";

export default {
    name: 'PaymentSettingsIndex',
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
            is_key_defined: false,
            labelPosition: 'top',
            webhook_url: '',
            pages: [],
            route_name: '',
            ipn_url: 'Blank',
            verifiedMessage: false,
            verifiedStatus: false,
            verifying: false
        }
    },
    watch: {
        $route(to, from) {
            this.getRoute();
            if (!this.disabled) {
                this.getSettings();
            }
        }
    },
    computed: {
        headerInfo() {
            const headerInfo = {
                stripe: {
                    label: this.$t('Stripe Payments'),
                    description: this.$t('Configure stripe to accept payments on your booking events')
                },
                paypal: {
                    label: this.$t('Paypal Payments'),
                    description: this.$t('Configure Paypal to accept payments on your booking eventsl')
                }
            };
            return headerInfo[this.route_name] || false;
        }
    },
    methods: {
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
                .then(response => {
                    this.$notify.success(this.$t('Settings updated!'));
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        verifyKeys(req, method) {
            this.verifying = true;
        },
        getRoute() {
            this.route_name = this.$route.params.settings_key ? this.$route.params.settings_key : 'stripe';
        },
    },
    mounted() {
        this.getRoute();
        if (!this.disabled) {
            this.getSettings();
        }
        if (window.outerWidth < 500) {
            this.labelPosition = "top";
        }
    }
}

</script>