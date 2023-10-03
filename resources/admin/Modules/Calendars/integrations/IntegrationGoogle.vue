<template>
    <h2 class="title">Integrations</h2>
    <el-skeleton v-if="loading" />
    <div v-if="!loading && fieldSettings?.auth_url" class="fcal_configure_integrations fcal_integrations">
        <div class="fcal_configure_integration_card">
            <div class="fcal_configure_integration_card_header">
                <div class="left">
                    <div class="img-box">
                        <div v-html="fieldSettings.logo"></div>
                    </div>
                    <div class="content">
                        <h3>{{ fieldSettings.title }}</h3>
                        <p>{{ fieldSettings.subtitle }}</p>
                    </div>
                </div>
                <el-button class="fcal_primary_btn2" :class="isConnectedBtn ? 'disconnect-btn' : null" @click="toggleSettings()">
                    <span class="icon">{{ buttonAttrs.icon }}</span> {{ buttonAttrs.label }}
                </el-button>
            </div>

            <div v-if="fieldSettings?.is_connected && fieldSettings.fields" class="fcal_configure_integration_body">
                <h4>Configuration</h4>
                <div class="fcal_integration_configuration_items">
                    <div v-for="(field, fieldKey) in fieldSettings.fields" :key="fieldKey" class="fcal_configure_integration_card fcal_integration_configuration_card">
                        <div v-if="field.type === 'checkbox'">
                            <div class="fcal_configure_integration_card_header">
                                <div class="left">
                                    <span class="icon">
                                        <div v-html="field.logo"></div>
                                    </span>
                                    <div class="content">
                                        <h3>{{ field.title }}</h3>
                                        <p>{{ field.subtitle }}</p>
                                    </div>
                                </div>
                                <div >
                                    <el-switch class="configuration-switch" v-model="settings[fieldKey]"/>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <el-form-item :label="field.label">
                                <el-input
                                    v-model="settings[fieldKey]"
                                    :type="field.type"
                                    :placeholder="field.placeholder">
                                </el-input>
                            </el-form-item>
                        </div>
                    </div>
                    <el-form-item>
                        <el-button class="fcal_primary_btn" @click="saveSettings()">{{ fieldSettings.save_btn_text }}</el-button>
                    </el-form-item>
                </div>
            </div>
        </div>
    </div>
    <div v-if="!loading && !fieldSettings?.auth_url">
        <el-alert title="No Integration Found" type="info" :closable="false" center show-icon></el-alert>
    </div>
</template>

<script>
import {markRaw} from "vue";
import {Minus, Plus} from "@element-plus/icons-vue";

export default {
    name: "IntegrationGoogle",
    data() {
        return {
            user_id: this.$route.params.id,
            saving: false,
            loading: false,
            settings: {},
            fieldSettings: {},
            settingsKey: 'google_calendar',
            PlusIcon: markRaw(Plus),
            MinusIcon: markRaw(Minus),
            isConnectedBtn: false
        }
    },
    computed: {
        buttonAttrs() {
            const isConnected = this.fieldSettings?.is_connected;
            this.isConnectedBtn = isConnected;
            return {
                label: isConnected ? 'Disconnect' : 'Connect',
                icon: isConnected ? '-' : '+'
            };
        }
    },
    methods: {
        toggleSettings() {
            if (this.fieldSettings?.is_connected) {
                this.disconnectIntegration();
            } else {
                window.location.href = this.fieldSettings?.auth_url;
            }
        },
        getSettings() {
            this.loading = true;
            this.$get('integrations/settings', {
                settings_key: this.settingsKey,
            })
            .then(response => {
                this.settings = response.settings;
                this.fieldSettings = response.field_settings;
            })
            .catch(errors => {
                this.$handleError(errors);
            })
            .finally(() => {
                this.loading = false;
            })
        },
        saveSettings() {
            this.$post('integrations/settings', {
                settings_key: this.settingsKey,
                settings: this.settings
            })
                .then(response => {
                    this.$handleSuccess(response);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        disconnectIntegration() {
            this.$post('integrations/disconnect/', {
                settings_key: this.settingsKey,
            })
                .then(response => {
                    this.$handleSuccess(response);
                    this.fieldSettings.connected = false;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
        },
    },
    mounted() {
        this.getSettings();
    }
}
</script>

<style scoped>

</style>