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
                                <img src="" alt="G-Calendar" />
                            </div>
                            <div class="content">
                                <h3>Google Calendar</h3>
                                <p>Connect Google Calendar/Meet to sync your events.</p>
                            </div>
                        </div>
                        <el-button class="fcal_primary_btn2" :class="isConnectedBtn ? 'disconnect-btn' : null" @click="toggleSettings()">
                            <span class="icon">{{ buttonAttrs.icon }}</span> {{ buttonAttrs.label }}
                        </el-button>
                    </div>

                    <div class="fcal_configure_integration_body" v-if="integrationVars?.connected">
                        <h4>Configuration</h4>

                        <div class="fcal_integration_configuration_items">
                            <div class="fcal_configure_integration_card fcal_integration_configuration_card">
                                <div class="fcal_configure_integration_card_header">
                                    <div class="left">
                                        <span class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M8 2V5" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M16 2V5" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M3.5 9.08984H20.5" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M22 19C22 19.75 21.79 20.46 21.42 21.06C20.73 22.22 19.46 23 18 23C16.99 23 16.07 22.63 15.37 22C15.06 21.74 14.79 21.42 14.58 21.06C14.21 20.46 14 19.75 14 19C14 16.79 15.79 15 18 15C19.2 15 20.27 15.53 21 16.36C21.62 17.07 22 17.99 22 19Z" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M16.4399 18.9995L17.4299 19.9895L19.5599 18.0195" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M21 8.5V16.36C20.27 15.53 19.2 15 18 15C15.79 15 14 16.79 14 19C14 19.75 14.21 20.46 14.58 21.06C14.79 21.42 15.06 21.74 15.37 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M11.9955 13.7002H12.0045" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8.29431 13.7002H8.30329" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8.29431 16.7002H8.30329" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <div class="content">
                                            <h3>Check for conflicts</h3>
                                            <p>Set the calendar(s) to check for conflicts to prevent double bookings.</p>
                                        </div>
                                    </div>

                                    <el-switch class="configuration-switch" v-model="settings.check_conflict" />
                                </div>
                            </div>
                            <div class="fcal_configure_integration_card fcal_integration_configuration_card">
                                <div class="fcal_configure_integration_card_header">
                                    <div class="left">
                                        <span class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M8 2V5" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M16 2V5" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M3.5 9.08984H20.5" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M22 19C22 19.75 21.79 20.46 21.42 21.06C20.73 22.22 19.46 23 18 23C16.99 23 16.07 22.63 15.37 22C15.06 21.74 14.79 21.42 14.58 21.06C14.21 20.46 14 19.75 14 19C14 16.79 15.79 15 18 15C19.2 15 20.27 15.53 21 16.36C21.62 17.07 22 17.99 22 19Z" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M16.4399 18.9995L17.4299 19.9895L19.5599 18.0195" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M21 8.5V16.36C20.27 15.53 19.2 15 18 15C15.79 15 14 16.79 14 19C14 19.75 14.21 20.46 14.58 21.06C14.79 21.42 15.06 21.74 15.37 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M11.9955 13.7002H12.0045" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8.29431 13.7002H8.30329" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8.29431 16.7002H8.30329" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <div class="content">
                                            <h3>Add to calendar</h3>
                                            <p>Set the calendar you would like to add new events to as they’re scheduled.</p>
                                        </div>
                                    </div>

                                    <el-switch class="configuration-switch" v-model="settings.add_to_calendar" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import { markRaw } from "vue";
import { Calendar, FolderAdd, FolderChecked, Plus, Minus } from '@element-plus/icons-vue';

export default {
    name: 'IntegrationSettings',
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
            settings: {
                check_conflict: false,
                add_to_calendar: false
            },
            settingsKey: 'google_calendar',
            integrationVars: '',
            PlusIcon: markRaw(Plus),
            MinusIcon: markRaw(Minus),
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
                this.check_conflict = this.settings.check_conflict;
                this.add_to_calendar = this.settings.add_to_calendar;
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
