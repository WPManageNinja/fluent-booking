<template>
    <div class="fcal_settings_body_inner">
        <div class="fcal_settings_header">
            <div class="fcal_settings_head">
                <h2>{{ $t('Stripe Payments') }}</h2>
                <p>{{ $t('PaymentSettingsIndex/configure_stripe_desc') }}</p>
            </div>
            <div class="fcal_settings_actions">
                <el-button size="large" :loading="saving" @click="saveSettings()" type="primary">
                    {{ $t('Save Settings') }}
                </el-button>
            </div>
        </div>

        <el-skeleton :rows="4" animated v-if="fetching"/>

        <div v-else class="fcal_calendar_body">
            <Renderer
                @onSettingsChange="updateSettings"
                :route_name="route_name"
                :fields="fields"
                :settings="settings"/>
        </div>
    </div>
</template>
<script type="text/babel">
import Renderer from "../Payments/PaymentComponet/Renderer.vue";

export default {
    name: 'PaymentSettingsIndex',
    components: {
        Renderer
    },
    data() {
        return {
            fields: {},
            settings: {},
            saving: false,
            fetching: false,
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
            this.getSettings();
        }
    },
    methods: {
        updateSettings(settings) {
            this.settings = settings;
        },
        getSettings() {
            this.fetching = true;
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
                    this.fetching = false;
                });
        },
        saveSettings() {
            this.saving = true;
            this.$post('integrations/settings/payment-methods', {
                settings: this.settings,
                method: this.route_name
            })
                .then(response => {
                    this.$notify.success('Settings updated!');
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
        this.getSettings();
        if (window.outerWidth < 500) {
            this.labelPosition = "top";
        }
    }
}

</script>
<script setup>
</script>
