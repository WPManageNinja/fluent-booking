<template>
    <div class="fcal_settings_body_inner fcal_settings_general">
        <div class="fcal_settings_header">
            <h3>Configure Integration</h3>
        </div>
        <div v-if="!loading" class="fcal_settings_content_wrap">
            <div v-if="Object.keys(fieldSettings).length" class="fcal_configure_integrations">
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
                    </div>
                    <div class="fcal_configure_integration_body">
                        <div v-html="fieldSettings.description"></div>
                        <el-form label-position="top">
                            <el-form-item v-for="(field, fieldKey) in fieldSettings.fields" :label="field.label+' *'" :class="{'input-with-copy': field.copy_btn}">
                                <el-input
                                    v-model="settings[fieldKey]"
                                    :type="field.type"
                                    :placeholder="field.placeholder"
                                    :disabled="field.readonly">
                                    <template v-if="field.copy_btn" #append>
                                        <el-button type="primary" @click="copyText(settings[fieldKey])">
                                            <el-icon><CopyDocument /></el-icon> Copy
                                        </el-button>
                                    </template>
                                </el-input>
                            </el-form-item>
                            <SaveButton :saving="saving" :label="fieldSettings.save_btn_text" @save="saveSettings"/>
                        </el-form>
                    </div>
                </div>
            </div>
            <el-alert v-else title="No Settings Found" type="info" :closable="false" center show-icon></el-alert>
        </div>
        <el-skeleton v-else :rows="4" animated/>
    </div>
</template>

<script>
import SaveButton from '../../Components/Buttons/SaveButton'
import { copyToClipBoard } from '@/Bits/data_config.js';
import { Calendar, ArrowRight, CopyDocument } from '@element-plus/icons-vue';
export default {
    name: 'ConfigureIntegrationSettings',
    props: ['settings_key'],
    components: {
        Calendar,
        SaveButton,
        ArrowRight,
        CopyDocument
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
