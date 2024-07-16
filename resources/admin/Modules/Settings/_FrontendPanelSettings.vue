<template>
    <div class="fbs_feature_module">
        <div class="fbs_module_desc">
            <div>
                <h4>{{ $t('Frontend Portal') }}
                    <span v-if="featureModules.frontend.enabled == 'yes'" class="fbs_addon_installed">{{ $t('Enabled') }}</span>
                    <span v-else class="fbs_addon_installed fbs_addon_disabled">{{ $t('Disabled') }}</span>
                </h4>
                <p>{{ $t('Load FluentBooking in the frontend of the website') }}
                    <a target="_blank" rel="noopener" href="#">
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
        <div class="fbs_module_actions">
            <el-button @click="showSettings = true" v-if="appVars.has_pro">{{ $t('Settings') }}</el-button>
            <el-button type="primary" v-else disabled>{{ $t('Upgrade to Pro') }}</el-button>
        </div>
    </div>

    <el-drawer
        :append-to-body="true"
        class="fbs_right_sidebar"
        direction="rtl"
        title="Frontend Portal Settings"
        v-model="showSettings">

        <h3>{{ $t('Frontend Panel Settings') }}</h3>
        <p>{{ $t('Add your FluentBooking to WordPress frontend / any Page via Shortcode.') }}</p>
        <hr/>

        <el-form class="fbs_form" v-model="featureModules.frontend" label-position="top">
            <el-form-item>
                <el-checkbox v-model="featureModules.frontend.enabled" true-label="yes"
                             false-label="no">{{ $t('Enable Frontend Portal') }}
                </el-checkbox>
            </el-form-item>

            <template v-if="featureModules.frontend.enabled == 'yes'">
                <el-form-item :label="$t('Via Shortcode / Dedicated Page?')">
                    <el-radio-group v-model="featureModules.frontend.render_type">
                        <el-radio label="standalone">{{ $t('Show in a standalone Frontend URL') }}</el-radio>
                        <el-radio label="shortcode">{{ $t('Use a pre - defined page via shortcode') }}</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item v-if="featureModules.frontend.render_type === 'shortcode'"
                              :label="$t('Please Select the page where you want to show')">
                    <el-select :placeholder="$t('Select Page')" v-model="featureModules.frontend.page_id">
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
                    <el-input v-model="featureModules.frontend.slug"
                              :placeholder="$t('Enter the slug for the frontend portal')"></el-input>
                </el-form-item>

            </template>
            <el-form-item>
                <el-button type="success" @click="saveSettings">{{ $t('Save Settings') }}</el-button>
            </el-form-item>
        </el-form>
    </el-drawer>

</template>
<script type="text/babel">
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
.fbs_form {
    .el-form-item__label {
        font-weight: bold;
    }
}
</style>
