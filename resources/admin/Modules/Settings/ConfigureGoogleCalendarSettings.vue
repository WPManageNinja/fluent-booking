
<template>
    <div class="fcal_settings_body_inner fcal_settings_general">
        <div class="fcal_settings_content_wrap">
            <div  class="fcal_configure_integrations">
                <div class="fcal_configure_integration_card">
                    <div class="fcal_configure_integration_card_header">
                        <div v-if="Object.keys(fieldSettings).length" class="left">
                            <div v-if="fieldSettings.logo" class="img-box">
                                <img :src="fieldSettings.logo"  />
                            </div>
                            <div class="content">
                                <h3>{{ fieldSettings.title }}</h3>
                                <p>{{ fieldSettings.subtitle }}</p>
                            </div>
                        </div>
                        <div v-else class="left">
                            <div class="img-box">
                                <img :src="appVars.asset_url + 'images/gg-calendar.svg'"/>
                            </div>
                            <div class="content">
                                <h3>{{ $t('Google Calendar / Meet') }}</h3>
                                <p>{{ $t('Configure Google Calendar/Meet to sync your events') }}</p>
                            </div>
                        </div>
                    </div>
                    <template v-if="isEnabled">
                        <div v-if="!loading" class="fcal_configure_integration_body">
                            <template v-if="Object.keys(fieldSettings).length">
                                <h4>{{ $t('GoogleIntegrationSettings/app_type')}}</h4>
                                <el-radio-group @change="maybeResetConfig()" v-model="settings.driver_type">
                                    <el-radio label="system_defined">{{ $t('GoogleIntegrationSettings/default_app') }}</el-radio>
                                    <el-radio label="custom_defined">{{ $t('GoogleIntegrationSettings/own_app') }}</el-radio>
                                </el-radio-group>
        
                                <template v-if="settings.driver_type == 'custom_defined'">
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
        
                                                <el-select  v-else-if="field.type='select'" popper-class="fcal_select" v-model="settings[fieldKey]">
                                                    <el-option
                                                        v-for="(item, itemValue) in field.options"
                                                        :key="itemValue"
                                                        :label="item"
                                                        :value="itemValue">
                                                    </el-option>
                                                </el-select>
    
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
                                </template>
                                <template v-else>
                                    <p>{{ $t('GoogleIntegrationSettings/official_app_desc') }} <a target="_blank" href="https://fluentbooking.com/docs/google-calendar-meet-integration-with-fluent-booking/">{{ $t("Read the documentation") }}</a></p>
                                    <el-form style="margin-top: 20px;" v-model="settings" label-position="top">
                                        <el-form-item :label="fieldSettings.fields.caching_time.label">
                                            <el-select popper-class="fcal_select" v-model="settings.caching_time">
                                                <el-option
                                                    v-for="(item, itemValue) in fieldSettings.fields.caching_time.options"
                                                    :key="itemValue"
                                                    :label="item"
                                                    :value="itemValue">
                                                </el-option>
                                            </el-select>
                                            <p v-if="fieldSettings.fields.caching_time.inline_help" v-html="fieldSettings.fields.caching_time.inline_help"></p>
                                        </el-form-item>
                                        <SaveButton v-if="fieldSettings.fields" :saving="saving" :label="fieldSettings.save_btn_text" @save="saveSettings"/>
                                    </el-form>
                                </template>
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
import ProNotice from '@/Components/Common/ProNotice.vue';
import { copyToClipBoard } from '@/Bits/data_config.js';
import { Calendar, ArrowRight, CircleCheckFilled, CircleCloseFilled, Lock } from '@element-plus/icons-vue';
export default {
    name: 'ConfigureGoogleCalendarSettings',
    props: ['disabled'],
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
            settings_key: 'google',
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
        maybeResetConfig() {
            if (this.settings.driver_type == 'custom_defined') {
                this.settings.client_id = '';
                this.settings.client_secret = '';
            }
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
