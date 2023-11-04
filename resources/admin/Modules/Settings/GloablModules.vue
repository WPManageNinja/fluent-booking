<template>
    <div style="margin-bottom: 25px;" class="fcal_settings_body_inner fcal_settings_general">
        <div class="fcal_configure_integration_card">
            <div class="fcal_configure_integration_card_header">
                <div class="left">
                    <div class="content">
                        <h3>{{ $t('Global Feature Modules') }}</h3>
                        <p>{{ $t('global_feature_modules_desc') }}</p>
                    </div>
                </div>
            </div>
            <el-skeleton animated v-if="loading"></el-skeleton>
            <div v-else class="fcal_configure_integration_body">
                <div class="fcal_integration_items">
                    <div v-for="(item, itemKey) in modules" class="fcal_integration_item">
                        <div class="fcal_card_wrap">
                            <div class="fcal_integration_icon">
                                <img style="width: 100%; height: auto;" class="general_integration_logo"
                                     :src="item.logo" alt="">
                            </div>
                            <div class="fcal_card_item_details">
                                <h3>{{ item.title }}</h3>
                                <p style="margin: 0;">{{ item.description }}</p>
                            </div>
                        </div>
                        <div class="fcal_card_actions">
                            <template v-if="item.is_system == 'yes'">
                                <el-button type="success" text v-if="item.is_active" :readonly="true">
                                    {{ $t('System Enabled') }}
                                </el-button>
                                <a v-else class="el-button el-button--primary" :href="item.install_url">{{ $t('Install') }}
                                    {{ item.title }}</a>
                            </template>
                            <template v-else>
                                <el-button type="info" :disabled="true" plain v-if="item.is_unavailable">
                                    {{ $t('Unavailable') }}
                                </el-button>
                                <template v-else>
                                    <span v-if="settings[itemKey] == 'yes'">{{ $t('Enabled') }}</span>
                                    <span v-else>{{ $t('Disabled') }}</span>
                                    <el-switch :disabled="saving" v-loading="saving" @click="updateSettings()"
                                               active-value="yes" inactive-value="no"
                                               v-model="settings[itemKey]"></el-switch>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">

export default {
    name: 'GloablModules',
    data() {
        return {
            loading: false,
            modules: {},
            settings: {},
            saving: false
        }
    },
    methods: {
        fetchModules() {
            this.loading = true;
            this.$get('settings/global-modules')
                .then((response) => {
                    this.settings = response.settings;
                    this.modules = response.modules;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        updateSettings() {
            this.saving = true;
            this.$post('settings/global-modules', {settings: this.settings})
                .then((response) => {
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
        this.fetchModules();
    }
}
</script>
