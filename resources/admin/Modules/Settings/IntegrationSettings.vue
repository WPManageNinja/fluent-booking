<template>
    <div class="fcal_settings_body_inner fcal_settings_integrations">
        <div class="fcal_settings_header">
            <h3>Integrations</h3>
        </div>

        <div  v-if="!loading" class="fcal_settings_content_wrap">
            <div v-if="fieldSettings?.auth_url"  class="fcal_configure_integrations fcal_integrations">
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
                            <SaveButton :saving="saving" :label="fieldSettings.save_btn_text" @save="saveSettings"/>
                        </div>
                    </div>
                </div>
            </div>
            <el-alert v-else title="Please Configure The Integration First" type="info" :closable="false" center show-icon></el-alert>
        </div>
        <el-skeleton v-else :rows="4" animated/>
    </div>
</template>

<script>
import SaveButton from '../../Components/Buttons/SaveButton';
export default {
    name: 'IntegrationSettings',
    props: ['settings_key'],
    components: {
        SaveButton
    },
    data() {
        return {
            saving: false,
            loading: false,
            settings: {},
            fieldSettings: {},
            isConnectedBtn: false
        }
    },
    watch: {
        settings_key() {
            this.getSettings();
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
                settings_key: this.settings_key,
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
                this.fieldSettings.is_connected = false;
            })
            .catch(errors => {
                this.$handleError(errors);
            })
        }
    },
    mounted() {
        this.getSettings();
    }
}
</script>
