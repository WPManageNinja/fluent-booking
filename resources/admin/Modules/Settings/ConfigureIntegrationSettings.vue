<template>
    <div class="fcal_settings_body_inner fcal_settings_general">
        <div class="fcal_settings_header">
            <h3>Configure Integration</h3>
        </div>

        <div class="fcal_settings_content_wrap">
            <el-collapse accordion class="fcal_configure_integrations">
                <el-collapse-item name="1" class="fcal_configure_integration_card">
                    <template #title>
                        <div class="fcal_configure_integration_card_header">
                            <div class="left">
                                <div class="img-box">
                                    <img src="" alt="G-Calendar" />
                                </div>
                                <div class="content">
                                    <h3>Google Calendar/Meet</h3>
                                    <p>Configure Google Calendar/Meet to sync your events.</p>
                                </div>
                            </div>
                            <span class="collapse-btn">
                                Configure <el-icon><ArrowRight /></el-icon>
                            </span>
                        </div>
                    </template>
                    <div class="fcal_configure_integration_body">
                        <p>Login to your Google account, go to Google Cloud Console, create a project, complete OAuth Consent screen process, click on Create Credentials, and you'll get your client id and secret key. If you get the ID and Keys for Google Calendar, Google Meet will be integrated automatically. For full details read the <a href="">documentation</a>.</p>
                        <el-form v-model="settings" label-position="top">
                            <el-form-item label="Client ID *">
                                <el-input
                                    v-model="settings.client_id"
                                    type="text"
                                    placeholder="Enter Your Client ID"
                                />
                            </el-form-item>
                            <el-form-item label="Client Secret *">
                                <el-input
                                    v-model="settings.client_secret"
                                    type="text"
                                    placeholder="Enter Your Secret Key"
                                />
                            </el-form-item>
                            <el-form-item label="Redirect URI *" class="redirect-url-input">
                                <el-input v-model="settings.redirect_url" disabled type="text">
                                    <template #append>
                                        <el-button type="primary"><el-icon><CopyDocument /></el-icon> Copy</el-button>
                                    </template>
                                </el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-button class="fcal_primary_btn" @click="saveSettings()">Save</el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                </el-collapse-item>
                <el-collapse-item name="2" class="fcal_configure_integration_card">
                    <template #title>
                        <div class="fcal_configure_integration_card_header">
                            <div class="left">
                                <div class="img-box">
                                    <img src="" alt="G-Calendar" />
                                </div>
                                <div class="content">
                                    <h3>Google Calendar/Meet</h3>
                                    <p>Configure Google Calendar/Meet to sync your events.</p>
                                </div>
                            </div>
                            <span class="collapse-btn">
                                Configure <el-icon><ArrowRight /></el-icon>
                            </span>
                        </div>
                    </template>
                    <div class="fcal_configure_integration_body">
                        <p>Login to your Google account, go to Google Cloud Console, create a project, complete OAuth Consent screen process, click on Create Credentials, and you'll get your client id and secret key. If you get the ID and Keys for Google Calendar, Google Meet will be integrated automatically. For full details read the <a href="">documentation</a>.</p>
                        <el-form v-model="settings" label-position="top">
                            <el-form-item label="Client ID *">
                                <el-input
                                    v-model="settings.client_id"
                                    type="text"
                                    placeholder="Enter Your Client ID"
                                />
                            </el-form-item>
                            <el-form-item label="Client Secret *">
                                <el-input
                                    v-model="settings.client_secret"
                                    type="text"
                                    placeholder="Enter Your Secret Key"
                                />
                            </el-form-item>
                            <el-form-item label="Redirect URI *" class="redirect-url-input">
                                <el-input v-model="settings.redirect_url" disabled type="text">
                                    <template #append>
                                        <el-button type="primary"><el-icon><CopyDocument /></el-icon> Copy</el-button>
                                    </template>
                                </el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-button class="fcal_primary_btn" @click="saveSettings()">Save</el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                </el-collapse-item>
            </el-collapse>
        </div>

    </div>
</template>

<script>
import { markRaw } from "vue";
import { Calendar, ArrowRight, ArrowDown, CopyDocument } from '@element-plus/icons-vue';

export default {
    name: 'ConfigureIntegrationSettings',
    components: {
        Calendar,
        ArrowRight,
        ArrowDown,
        CopyDocument
    },
    data() {
        return {
            saving: false,
            loading: false,
            openSettings: true,
            settingsKey: 'google_calendar',
            settings: {},
            ArrowRightIcon: markRaw(ArrowRight),
            ArrowDownIcon: markRaw(ArrowDown),
            activeName: '1',
        }
    },
    computed: {
        toggleIcon() {
            if (this.openSettings) {
                return this.ArrowDownIcon;
            } else {
                return this.ArrowRightIcon;
            }
        }
    },
    methods: {
        toggleSettings() {
            this.openSettings = !this.openSettings;
        },
        getSettings() {
            this.$get('integrations/', {
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
            this.$post('integrations/', {
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
    }
}
</script>
