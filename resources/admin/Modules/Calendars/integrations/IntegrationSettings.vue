<template>
    <el-skeleton :rows="4" animated v-if="loading" />
    <h2 v-if="!loading" class="title">{{ fieldSettings?.header }}</h2>
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
                <el-button class="fcal_primary_btn2" v-loading="saving" :disabled="saving" :class="isConnectedBtn ? 'disconnect-btn' : null" @click="toggleSettings()">
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
                        <SaveButton :saving="saving" :label="fieldSettings.save_btn_text" @save="saveSettings"/>
                    </el-form-item>
                </div>
            </div>
        </div>
    </div>
    <div v-if="!loading && !fieldSettings?.auth_url" class="fcal_integration_required">
        <h3>Integration has not been configured yet</h3>
        <p v-if="isAdmin">Configure the integration <a @click="goToConfiguration">here</a></p>
    </div>
</template>

<script type="text/babel">
import SaveButton from '../../../Components/Buttons/SaveButton.vue';
export default {
    name: "IntegrationSettings",
    props: ['calendar'],
    components: {
        SaveButton
    },
    data() {
        return {
            saving: false,
            loading: false,
            settings: {},
            fieldSettings: {},
            isConnectedBtn: false,
            settingsKey: this.$route.name,
            hostId: this.calendar.user_id,
            isAdmin: this.appVars?.me?.is_admin
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
        goToConfiguration() {
            this.$router.push({
                name: 'configure-integrations',
                params: {settings_key: this.settingsKey}
            })
        },
        toggleSettings() {
            if (this.fieldSettings?.is_connected) {
                this.disconnectIntegration();
            } else {
                this.saving = true;
                window.location.href = this.fieldSettings?.auth_url;
            }
        },
        getSettings() {
            this.loading = true;
            this.$get('integrations/' + this.hostId + '/settings', {
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
            this.saving = true;
            this.$post('integrations/' + this.hostId + '/settings', {
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
            this.saving = true;
            this.$post('integrations/' + this.hostId + '/disconnect', {
                settings_key: this.settingsKey,
            })
                .then(response => {
                    this.$handleSuccess(response);
                    this.fieldSettings.is_connected = false;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
    },
    mounted() {
        this.getSettings();
    }
}
</script>
