<template>
    <div class="fcal_settings">
        <div class="fcal_section_narrow fcal_section">
            <div style="padding:15px;" class="fcal_section_header">
                <div class="fcal_title">
                    <h3>Integrations</h3>
                </div>
            </div>
            <div v-loading="loading" class="fcal_settings_container">
                <div class="fcal_section_body">
                    <el-card shadow="never">
                        <div class="fcal_config_card">
                            <div class="card_top">
                                <div class="card_left">
                                    <div class="config_icon">
                                        <el-icon><Calendar /></el-icon>
                                    </div>
                                    <div class="config_content">
                                        <p class="config_title">Google Calendar / Meet</p>
                                        <p class="config_subtitle">Connect Google Calendar / Meet to sync your events</p>
                                    </div>
                                </div>
                                <div class="card_right" @click="toggleSettings()">
                                    <el-button
                                        :type="buttonAttrs.type"
                                        :icon="buttonAttrs.icon">
                                        {{ buttonAttrs.label }}
                                    </el-button>
                                </div>
                            </div>
                            <div class="card_body" v-if="integrationVars?.connected">
                                <el-divider/>
                                <h4>Configuration</h4>
                                <div class="integration_cards">
                                    <el-card shadow="never">
                                        <div class="fcal_config_card">
                                            <div class="card_top">
                                                <div class="card_left">
                                                    <div class="config_icon">
                                                        <el-icon><FolderAdd /></el-icon>
                                                    </div>
                                                    <div class="config_content">
                                                        <p class="config_title">Add to calendar</p>
                                                        <p class="config_subtitle">Set the calendar you would like to add new events</p>
                                                    </div>
                                                </div>
                                                <div class="card_right">
                                                    <el-switch v-model="settings.add_to_calendar"></el-switch>
                                                </div>
                                            </div>
                                        </div>
                                    </el-card>
                                    <el-card shadow="never">
                                        <div class="fcal_config_card">
                                            <div class="card_top">
                                                <div class="card_left">
                                                    <div class="config_icon">
                                                        <el-icon><FolderChecked /></el-icon>
                                                    </div>
                                                    <div class="config_content">
                                                        <p class="config_title">Check for conflicts</p>
                                                        <p class="config_subtitle">Set the calendar to check for conflicts to prevent double bookings</p>
                                                    </div>
                                                </div>
                                                <div class="card_right">
                                                    <el-switch v-model="settings.check_conflict"></el-switch>
                                                </div>
                                            </div>
                                        </div>
                                    </el-card>
                                    <div><el-button type="primary" @click="saveSettings()">Save</el-button></div>
                                </div>
                            </div>
                        </div>
                    </el-card>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { markRaw } from "vue";
import { Calendar, FolderAdd, FolderChecked, Plus, Minus } from '@element-plus/icons-vue';

export default {
    name: 'Integrations',
    components: {
        Plus,
        Minus,
        Calendar,
        FolderAdd,
        FolderChecked
    },
    data() {
        return {
            saving: false,
            loading: false,
            settings: {},
            settingsKey: 'google_calendar',
            integrationVars: '',
            PlusIcon: markRaw(Plus),
            MinusIcon: markRaw(Minus)
        }
    },
    computed: {
        buttonAttrs() {
            const isConnected = this.integrationVars?.connected;
            return {
                label: isConnected ? 'Disconnect' : 'Connect',
                type: isConnected ? 'danger' : 'primary',
                icon: isConnected ? this.MinusIcon : this.PlusIcon
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
        disconnectIntegration() {
            this.$post('integrations/disconnect/', {
                settings_key: this.settingsKey,
            })
            .then(response => {
                this.$handleSuccess(response);
                this.integrationVars.connected = false;
            })
            .catch(errors => {
                this.$handleError(errors);
            })
        },
        getSettings() {
            this.$get('integrations/settings', {
                settings_key: this.settingsKey,
            })
            .then(response => {
                this.settings = response.settings;
            })
            .catch(errors => {
                this.$handleError(errors);
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
        }
    },
    mounted() {
        this.getSettings();
    },
    created() {
        this.integrationVars = window['fluentFramework_' + this.settingsKey];
    }
}
</script>
