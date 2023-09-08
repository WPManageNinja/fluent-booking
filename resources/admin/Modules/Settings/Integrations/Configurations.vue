<template>
    <div class="fcal_settings">
        <div class="fcal_section_narrow fcal_section">
            <div style="padding:15px;" class="fcal_section_header">
                <div class="fcal_title">
                    <h3>Configuration</h3>
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
                                        <p class="config_subtitle">Configure Google Calendar / Meet to sync your events</p>
                                    </div>
                                </div>
                                <div class="card_right" @click="toggleSettings()">
                                    <el-link
                                        type="primary" 
                                        :underline="false">
                                        Configure
                                    </el-link>
                                    <el-link 
                                        type="primary"
                                        :underline="false"
                                        :icon="toggleIcon"
                                    />
                                </div>
                            </div>
                            <div v-if="openSettings" class="card_body">
                                <el-divider/>
                                <p class="config_description">
                                    Login to your Google account, go to Google Cloud Console, create a project, complete OAuth Consent screen process,
                                    click on Create Credentials, and you'll get your client id and secret key. If you get the ID and Keys for Google Calendar, 
                                    Goole Meet will be integrated automatically. For full details read the documentation.
                                </p>
                                <div class="fcal_form_section">
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
                                        <el-form-item label="Redirect URI *">
                                            <el-input v-model="settings.redirect_url" disabled type="text">
                                                <template #append>
                                                    <el-button type="primary">Copy</el-button>
                                                </template>
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item>
                                            <el-button type="primary" @click="saveSettings()">Save</el-button>
                                        </el-form-item>
                                    </el-form>
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
import { Calendar, ArrowRight, ArrowDown } from '@element-plus/icons-vue';

export default {
    name: 'Configuratios',
    components: {
        Calendar,
        ArrowRight,
        ArrowDown
    },
    data() {
        return {
            saving: false,
            loading: false,
            openSettings: true,
            settingsKey: 'google_calendar',
            settings: {},
            ArrowRightIcon: markRaw(ArrowRight),
            ArrowDownIcon: markRaw(ArrowDown)
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
                this.$notify.success(response.message);
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
