<template>
    <div class="fcal_settings_body_inner fcal_settings_general">
        <div v-if="!loading" class="fcal_settings_content_wrap">
            <div v-if="Object.keys(fieldSettings).length" class="fcal_configure_integrations">
                <div class="fcal_configure_integration_card">
                    <div class="fcal_configure_integration_card_header">
                        <div class="left">
                            <div v-if="fieldSettings.logo" class="img-box">
                                <img :src="fieldSettings.logo"  />
                            </div>
                            <div class="content">
                                <h3>{{ fieldSettings.title }}</h3>
                                <p>{{ fieldSettings.subtitle }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="fcal_configure_integration_body">
                        <div v-html="fieldSettings.description"></div>
                        <template v-if="fieldSettings.fields">
                            <el-form v-model="settings" label-position="top">
                                <el-form-item v-for="(field, fieldKey) in fieldSettings.fields" :label="field.label+' *'" :class="{'input-with-copy': field.copy_btn}">
                                    <el-input
                                        v-if="field.type == 'text'"
                                        v-model="settings[fieldKey]"
                                        :type="field.type"
                                        :placeholder="field.placeholder"
                                        :disabled="field.readonly">
                                        <template v-if="field.copy_btn" #append>
                                            <el-button type="default" @click="copyText(settings[fieldKey])">
                                                <el-icon><CopyDocument /></el-icon> Copy
                                            </el-button>
                                        </template>
                                    </el-input>

                                    <el-select  v-else-if="field.type='select'" v-model="settings[fieldKey]">
                                        <el-option
                                            v-for="(item, itemValue) in field.options"
                                            :key="itemValue"
                                            :label="item"
                                            :value="itemValue">
                                        </el-option>
                                    </el-select>

                                    <p v-if="field.inline_help" v-html="field.inline_help"></p>
                                </el-form-item>
                                <SaveButton v-if="fieldSettings.fields" :saving="saving" :label="fieldSettings.save_btn_text" @save="saveSettings"/>
                            </el-form>
                            <p v-if="fieldSettings && fieldSettings.will_encrypt">
                                <hr />
                                <el-icon><Lock /></el-icon>
                                The above app secret key will be encrypted and stored securely.
                            </p>
                        </template>
                    </div>
                </div>
            </div>
            <el-empty v-else description="No Settings Found for this integration"/>
        </div>
        <el-skeleton v-else :rows="4" animated/>
    </div>
</template>

<script>
import SaveButton from '../../Components/Buttons/SaveButton'
import { copyToClipBoard } from '@/Bits/data_config.js';
import { Calendar, ArrowRight, CopyDocument, Lock } from '@element-plus/icons-vue';
export default {
    name: 'ConfigureIntegrationSettings',
    props: ['settings_key'],
    components: {
        Calendar,
        SaveButton,
        ArrowRight,
        CopyDocument,
        Lock
    },
    data() {
        return {
            saving: false,
            loading: false,
            fieldSettings: {},
            settings: {}
        }
    },
    watch: {
        settings_key() {
            this.getSettings();
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
        copyText(text) {
            copyToClipBoard(text);
            this.$handleSuccess('Copied to clipboard');
        }
    },
    mounted() {
        this.getSettings();
    }
}
</script>
