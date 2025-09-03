<template>
    <div class="fcal_settings_body_inner fcal_settings_general">
        <div class="fcal_settings_content_wrap">
            <div class="fcal_configure_integrations">
                <div class="fcal_configure_integration_card">
                    <div class="fcal_configure_integration_card_header">
                        <div v-if="Object.keys(fieldSettings).length" class="left">
                            <div v-if="fieldSettings.logo" class="img-box">
                                <img :src="fieldSettings.logo"/>
                            </div>
                            <div class="content">
                                <h3>{{ fieldSettings.title }}</h3>
                                <p>{{ fieldSettings.subtitle }}</p>
                            </div>
                        </div>
                        <div v-else-if="headerInfo" class="left">
                            <div class="img-box">
                                <img :src="headerInfo.logo"/>
                            </div>
                            <div class="content">
                                <h3>{{ headerInfo.title }}</h3>
                                <p>{{ headerInfo.subtitle }}</p>
                            </div>
                        </div>
                    </div>
                    <template v-if="isEnabled">
                        <div v-if="!loading" class="fcal_configure_integration_body">
                            <div v-html="fieldSettings.description"></div>
                            <template v-if="fieldSettings.fields">
                                <el-form v-model="settings" label-position="top">
                                    <el-form-item v-for="(field, fieldKey) in fieldSettings.fields" :label="field.label" :class="{'input-with-copy': field.copy_btn}">
                                        <el-input
                                            v-if="field.type == 'text'"
                                            v-model="settings[fieldKey]"
                                            :type="field.type"
                                            :placeholder="field.placeholder"
                                            :disabled="field.readonly">
                                            <template v-if="field.copy_btn" #append>
                                                <el-button type="default" @click="copyText(settings[fieldKey])">
                                                    <el-icon><CopyDocument /></el-icon> {{ $t('Copy') }}
                                                </el-button>
                                            </template>
                                        </el-input>
    
                                        <el-select  v-else-if="field.type == 'select'" popper-class="fcal_select" v-model="settings[fieldKey]">
                                            <el-option
                                                v-for="(item, itemValue) in field.options"
                                                :key="itemValue"
                                                :label="item"
                                                :value="itemValue">
                                            </el-option>
                                        </el-select>
    
                                        <el-checkbox v-else-if="field.type == 'yes_no_checkbox'" true-label="yes" false-label="no" v-model="settings[fieldKey]">
                                            {{ field.checkbox_label }}
                                        </el-checkbox>
    
                                        <p v-if="field.inline_help" v-html="field.inline_help"></p>
                                    </el-form-item>
                                    <div v-if="fieldSettings.check_validation" class="fcal_integration_validation_text">
                                        <p v-if="fieldSettings.is_connected">
                                            <el-icon><CircleCheckFilled /></el-icon>
                                            {{ fieldSettings.valid_message }}
                                            <el-link @click="disconnect">{{ $t('Disconnect') }}</el-link>
                                        </p>
                                        <p v-else-if="fieldSettings.is_configured">
                                            <el-icon><CircleCloseFilled /></el-icon>
                                            {{ fieldSettings.invalid_message }}
                                        </p>
                                    </div>
                                    <SaveButton v-if="fieldSettings.fields" :saving="saving" :label="fieldSettings.save_btn_text" @save="saveSettings"/>
                                </el-form>
                                <p v-if="fieldSettings?.will_encrypt">
                                    <hr />
                                    <el-icon><Lock /></el-icon>
                                    {{ $t('The above app secret key will be encrypted and stored securely.') }}
                                </p>
                            </template>
                        </div>
                        <el-skeleton v-else :rows="4" animated/>
                    </template>
                    <ProNotice v-else/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import SaveButton from '../../Components/Buttons/SaveButton';
import { copyToClipBoard } from '@/Bits/data_config.js';
import ProNotice from '@/Components/Common/ProNotice.vue';
import { Calendar, ArrowRight, CircleCheckFilled, CircleCloseFilled, Lock } from '@element-plus/icons-vue';
export default {
    name: 'ConfigureIntegrationSettings',
    props: ['settings_key', 'disabled'],
    components: {
    Calendar,
    SaveButton,
    ArrowRight,
    CircleCheckFilled,
    CircleCloseFilled,
    Lock,
    ProNotice
},
    data() {
        return {
            saving: false,
            loading: false,
            fieldSettings: {},
            settings: {},
            isEnabled: !this.disabled && this.appVars.has_pro
        }
    },
    watch: {
        settings_key() {
            if (this.isEnabled) {
                this.getSettings();
            }
        }
    },
    computed: {
        headerInfo() {
            const headerInfo = {
                outlook: {
                    logo: this.appVars.asset_url + 'images/ol-icon-color.svg',
                    title: this.$t('Outlook Calendar / MS Teams'),
                    subtitle: this.$t('Configure Outlook Calendar to sync your events')
                },
                apple_calendar: {
                    logo: this.appVars.asset_url + 'images/a-cal.svg',
                    title: this.$t('Apple Calendar'),
                    subtitle: this.$t('Use Outlook Calendar to sync your Fluent Booking events')
                },
                next_cloud_calendar: {
                    logo: this.appVars.asset_url + 'images/Ncloud.svg',
                    title: this.$t('Nextcloud Calendar'),
                    subtitle: this.$t('Enable/Disable Nextcloud Calendar to sync your events')
                },
                twilio: {
                    logo: this.appVars.asset_url + 'images/tw.svg',
                    title: this.$t('SMS by Twilio'),
                    subtitle: this.$t('Configure Twilio to send SMS notifications')
                },
            };
            return headerInfo[this.settings_key] || false;
        }
    },
    methods: {
        getSettings() {
            this.loading = true;
            this.$get('integrations/', {
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
            this.$post('integrations/', {
                settings_key: this.settings_key,
                settings: this.settings
            })
            .then(response => {
                this.$handleSuccess(response);
                this.getSettings();
            })
            .catch(errors => {
                this.$handleError(errors);
            })
            .finally(() => {
                this.saving = false;
            });
        },
        disconnect() {
            this.settings = {};
            this.saveSettings();
        },
        copyText(text) {
            copyToClipBoard(text);
            this.$handleSuccess(this.$t('Copied to clipboard'));
        }
    },
    mounted() {
        if (this.isEnabled) {
            this.getSettings();
        }
    }
}
</script>
