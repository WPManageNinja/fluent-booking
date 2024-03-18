<template>
    <div class="fcal_settings_body_inner">
        <div class="fcal_settings_header">
            <div v-if="!loading" class="fcal_settings_head">
                <h2>{{ fields?.label }}</h2>
                <p>{{ fields?.description }}</p>
            </div>
            <el-skeleton v-else :rows="1" animated/>
            <div class="fcal_settings_actions">
                <el-button size="large" :loading="saving" @click="saveSettings()" type="primary">
                    {{ $t('Save Settings') }}
                </el-button>
            </div>
        </div>

        <el-skeleton :rows="4" animated v-if="loading"/>

        <div v-else class="fcal_calendar_body">
            <Renderer
                @onSettingsChange="updateSettings"
                :route_name="route_name"
                :fields="fields"
                :settings="settings"/>
        </div>
    </div>
</template>
<script>
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
            this.getSettings();
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
        this.getSettings();
        if (window.outerWidth < 500) {
            this.labelPosition = "top";
        }
    }
}

</script>
<script setup>
</script>
