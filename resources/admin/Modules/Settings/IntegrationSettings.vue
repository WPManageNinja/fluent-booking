<template>
    <div class="fcal_settings_body_inner fcal_settings_integrations">
        <div class="fcal_settings_header">
            <h3>Integrations</h3>
        </div>

        <div class="fcal_settings_content_wrap">
            <div class="fcal_configure_integrations fcal_integrations">
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

                    <div v-if="integrationVars?.connected && fieldSettings.fields" class="fcal_configure_integration_body">
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
        </div>

    </div>
</template>

<script>
export default {
    name: 'IntegrationSettings',
    props: ['settings_key'],
    data() {
        return {
            saving: false,
            settings: {},
            fieldSettings: {},
            integrationVars: '',
            isConnectedBtn: false
        }
    },
    computed: {
        buttonAttrs() {
            const isConnected = this.integrationVars?.connected;
            this.isConnectedBtn = isConnected;
            return {
                label: isConnected ? 'Disconnect' : 'Connect',
                icon: isConnected ? '-' : '+'
            };
        }
    },
    methods: {
        toggleSettings() {
            if (this.integrationVars?.connected) {
                this.disconnectIntegration();
            } else {
                window.location.href = this.integrationVars?.auth_url;
            }
        },
        getSettings() {
            this.$get('integrations/settings', {
                settings_key: this.settings_key,
            })
            .then(response => {
                this.settings = response.settings;
                this.fieldSettings = response.field_settings;
                console.log(response);
            })
            .catch(errors => {
                this.$handleError(errors);
            })
        },
        saveSettings() {
            this.saving = true;
            this.$post('integrations/settings', {
                settings_key: this.settings_key,
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
                settings_key: this.settings_key,
            })
            .then(response => {
                this.$handleSuccess(response);
                this.integrationVars.connected = false;
            })
            .catch(errors => {
                this.$handleError(errors);
            })
        }
    },
    mounted() {
        this.getSettings();
    },
    created() {
        this.integrationVars = window['fluentFramework_' + this.settings_key];
        console.log(this.integrationVars);
    }
}
</script>
