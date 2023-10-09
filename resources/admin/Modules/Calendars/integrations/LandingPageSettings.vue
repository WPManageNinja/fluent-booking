<template>
    <div class="fcal_settings_landing_page">
        <div class="fcal_settings_header">
            <div class="fcal_settings_head">
                <h2>Landing Page Settings</h2>
                <p class="short-desc">Share your Booking Types in a beautiful & standalone landing page</p>
            </div>
            <div class="fcal_settings_actions">
                <a v-if="settings.enabled" :href="share_url" target="_blank" rel="noopener noreferrer" class="el-button el-button--text el-button--large">
                    <el-icon><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" x2="21" y1="14" y2="3"></line></svg></el-icon> <span>View</span>
                </a>
            </div>
        </div>
        <div v-loading="loading" class="fcal_settings_body">
            <el-form v-model="settings" label-position="top">
                <el-form-item>
                    <el-checkbox true-label="yes" false-label="no" v-model="settings.enabled">Enable Landing Page
                        Features for this calendar
                    </el-checkbox>
                </el-form-item>
                <el-form-item label="Landing Page Description">
                    <el-input
                        v-model="calendar.description"
                        type="textarea"
                        :rows="3"
                        placeholder="Enter description for your landing page"
                    />
                </el-form-item>
                <template v-if="settings.enabled == 'yes'">
                    <el-form-item label="Which Booking Forms to Show?">
                        <el-radio-group v-model="settings.show_type">
                            <el-radio label="all">All Active Booking Forms</el-radio>
                            <el-radio label="selected_only">Only Selected Active Booking Types</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item v-if="settings.show_type == 'selected_only'"
                                  label="Please select which Booking Forms to show in the page?">
                        <el-checkbox-group class="fcal_radio_lined" v-model="settings.enabled_slots">
                            <el-checkbox v-for="slot in calendar.slots" :key="slot.id" :label="slot.id">{{
                                    slot.title
                                }}
                            </el-checkbox>
                        </el-checkbox-group>
                    </el-form-item>
                </template>
                <el-form-item>
                    <el-button
                        @click="saveSettings()"
                        :disabled="saving"
                        v-loading="saving"
                        type="primary">Save
                        Settings
                    </el-button>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script type="text/babel">
import {Share} from '@element-plus/icons-vue';
export default {
    name: 'LandingPageCalendarSettings',
    props: ['calendar'],
    components: {
        Share
    },
    data() {
        return {
            loading: false,
            settings: {},
            saving: false,
            share_url: ''
        }
    },
    methods: {
        fetchSettings() {
            this.loading = true;
            this.$get('calendars/' + this.calendar.id + '/sharing-settings')
                .then(response => {
                    this.settings = response.settings;
                    this.share_url = response.share_url;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        saveSettings() {
            this.saving = true;
            this.$post('calendars/' + this.calendar.id + '/sharing-settings', {
                settings: this.settings,
                description: this.calendar.description
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
        this.fetchSettings();
    }
}
</script>
