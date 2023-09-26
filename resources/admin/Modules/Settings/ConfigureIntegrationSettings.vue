<template>
    <div class="fcal_settings_body_inner fcal_settings_general">
        <div class="fcal_settings_header">
            <h3>Configure Integration</h3>
        </div>
        <div class="fcal_settings_content_wrap">
            <el-collapse accordion class="fcal_configure_integrations">
                <el-collapse-item class="fcal_configure_integration_card">
                    <template #title>
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
                            <span class="collapse-btn">
                                Configure <el-icon><ArrowRight /></el-icon>
                            </span>
                        </div>
                    </template>
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
                                        <el-button type="primary"><el-icon><CopyDocument /></el-icon> Copy</el-button>
                                    </template>
                                </el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-button class="fcal_primary_btn" @click="saveSettings()">{{ fieldSettings.save_btn_text }}</el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                </el-collapse-item>
            </el-collapse>
        </div>
    </div>
</template>

<script>
import { Calendar, ArrowRight, CopyDocument } from '@element-plus/icons-vue';

export default {
    name: 'ConfigureIntegrationSettings',
    props: ['settings_key'],
    components: {
        Calendar,
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
    methods: {
        getSettings() {
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
        },
        saveSettings() {
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
        }
    },
    mounted() {
        this.getSettings();
    }
}
</script>
