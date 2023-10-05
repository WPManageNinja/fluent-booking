<template>
    <div class="fcal_settings_landing_page">
        <div class="fcal_settings_header">
            <h3 class="title">
                Landing Page Settings
                <p class="short-desc">Share your Booking Types in a beautiful & standalone landing page</p>
            </h3>
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
                            <el-radio label="all">All Booking Forms</el-radio>
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
export default {
    name: 'LandingPageCalendarSettings',
    props: ['calendar'],
    data() {
        return {
            loading: false,
            settings: {},
            saving: false
        }
    },
    methods: {
        fetchSettings() {
            this.loading = true;
            this.$get('calendars/' + this.calendar.id + '/sharing-settings')
                .then(response => {
                    this.settings = response.settings;
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
