<template>
    <div class="fcal_feature_module">
        <div class="fcal_module_desc">
            <div>
                <h4>
                    <span v-if="appVars.has_pro">{{ $t('Frontend Portal') }}</span>
                    <span v-else>{{ $t('Frontend Portal (Pro Required)') }}</span>
                    <span v-if="appVars.has_pro && featureModules.frontend.enabled == 'yes'" class="fcal_addon_installed">{{ $t('Enabled') }}</span>
                    <span v-else class="fcal_addon_installed fcal_addon_disabled">{{ $t('Disabled') }}</span>
                </h4>
                <p>{{ $t('Load FluentBooking in the frontend of the website') }}
                    <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/fluentbooking-frontend-panel/">
                        {{ $t('Learn') }} {{ $t('more') }}
                    </a> {{ $t('about this feature') }}.
                </p>
                <p v-if="featureModules.frontend.enabled == 'yes' && featureModules.frontend.slug">
                    {{ $t('This portal can be accessed from') }}
                    <code>{{ featureModules.panel_url }}</code>
                    <a target="_blank" rel="noopener" :href="featureModules.panel_url">
                        <span :title="$t('Open link in a new tab')" style="margin-left: 2px;" v-html="'&#8599;'"></span>
                    </a>
                </p>
            </div>
        </div>
        <div class="fcal_module_actions">
            <el-button @click="showSettings = true" v-if="appVars.has_pro" class="fcal_plain_btn">{{ $t('Settings') }}</el-button>
        </div>
    </div>

    <el-drawer
        v-model="showSettings"
        direction="rtl"
        :title="$t('Frontend Portal Settings')"
        :zIndex="999"
        :direction="appVars.is_rtl ? 'ltr' : 'rtl'"
        modal-class="fcal_drawer fcal_frontend_panel_drawer">
        <div class="fcal_frontend_panel_settings_drawer">
            <h3>{{ $t('Frontend Panel Settings') }}</h3>
            <p>{{ $t('Add your FluentBooking to WordPress frontend / any Page via Shortcode.') }}</p>
            <hr/>

            <el-form class="fcal_form" v-model="featureModules.frontend" label-position="top">
                <el-form-item>
                    <el-checkbox 
                        v-model="featureModules.frontend.enabled"
                        true-label="yes"
                        false-label="no">
                        {{ $t('Enable Frontend Portal') }}
                    </el-checkbox>
                </el-form-item>
                <template v-if="featureModules.frontend.enabled == 'yes'">
                    <el-form-item :label="$t('Via Shortcode / Dedicated Page?')">
                        <el-radio-group v-model="featureModules.frontend.render_type">
                            <el-radio label="standalone">{{ $t('Show in a standalone Frontend URL') }}</el-radio>
                            <el-radio label="shortcode">{{ $t('Use a pre - defined page via shortcode') }}</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <el-form-item v-if="featureModules.frontend.render_type === 'shortcode'" :label="$t('Please Select the page where you want to show')">
                        <el-select :placeholder="$t('Select Page')" filterable popper-class="fcal_select" placement="bottom" v-model="featureModules.frontend.page_id">
                            <el-option v-for="page in pages" :key="page.id" :label="page.title" :value="page.id">
                                <span style="float: left">{{ page.title }}</span>
                                <span style="float: right; color: var(--el-text-color-secondary); font-size: 13px;">
                                #{{ page.id }}
                            </span>
                            </el-option>
                        </el-select>
                        <p>{{ $t('Please add this shortcode to your selected page:') }} <code style="background: rgb(226 227 227); padding: 3px 10px;border-radius: 3px;cursor:pointer;" @click="handleCopyShortcode('[fluent_booking_panel]')" ref="copyShortcode">[fluent_booking_panel]</code></p>
                    </el-form-item>

                    <el-form-item v-else :label="$t('URL Slug for the frontend panel (eg: projects)')">
                        <el-input
                            v-model="featureModules.frontend.slug"
                            :placeholder="$t('Enter the slug for the frontend portal')">
                        </el-input>
                    </el-form-item>

                </template>
                <el-form-item>
                    <el-button class="fcal_primary_btn" @click="saveSettings">{{ $t('Save Settings') }}</el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-drawer>

</template>
<script>
import { copyToClipBoard } from '@/Bits/data_config.js';

export default {
    name: 'FrontPanel',
    props: ['featureModules'],
    emits: ['save'],
    data() {
        return {
            showSettings: false,
            pages: [],
            loading_pages: false
        }
    },
    methods: {
        saveSettings() {
            this.$emit('save');
        },
        getPages() {
            this.loading_pages = true;
            this.$get('settings/pages')
                .then(response => {
                    this.pages = response.pages;
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading_pages = false;
                });
        },
        handleCopyShortcode(shortcode) {
            copyToClipBoard(shortcode);
            this.$handleSuccess(this.$t('Shortcode has been copied to your clipboard'));
        }
    },
    mounted() {
        this.getPages();
    }
}
</script>

<style lang="scss">
.fcal_form {
    .el-form-item__label {
        font-weight: bold;
    }
}
</style>
