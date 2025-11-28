<template>
    <div v-loading="working" class="fcal_remote_calendar_block">
        <div class="fcal_remote_header">
            <div class="fcal_driver_brand">
                <img :src="driver.icon"/>
                <div class="fcal_driver_heading">
                    <h3>{{ driver.title }}</h3>
                    <p>{{ feed.identifier }}</p>
                </div>
            </div>
            <div class="fcal_driver_action">
                <el-button @click="disconnectCalendar()" class="fcal_plain_btn" size="small">
                    <el-icon>
                        <Delete/>
                    </el-icon>
                </el-button>
            </div>
        </div>
        <div class="fcal_remote_body">
            <p class="fcal_remote_sub">{{ $t('enable_calendar_check_conflicts') }}</p>
            <div class="fcal_remote_cal_items">
                <el-checkbox-group class="fcal_lined_checks" v-model="feed.conflict_check_ids">
                    <el-checkbox :disabled="saving" v-for="cal in feed.remote_calendars" :key="cal.id" :label="cal.id"
                                 @change="saveConflicts(cal.id)">
                        {{ cal.title }}
                        <span v-loading="saving_id == cal.id"></span>
                    </el-checkbox>
                </el-checkbox-group>

                <div v-if="feed.errors">
                    <hr/>
                    <p style="color: red;" class="fcal_remote_sub">{{ $t('API Error:') }} {{ feed.errors }}</p>
                </div>
            </div>
        </div>
        <div v-if="feed.additional_setting_fields" class="fcal_remote_footer">
            <el-form label-position="top">
                <p class="fcal_remote_sub">{{ $t('Additional Settings') }}</p>
                <el-form-item v-for="(field, fieldKey) in feed.additional_setting_fields">
                    <el-checkbox 
                        v-if="field.type == 'yes_no_checkbox'"
                        true-label="yes"
                        false-label="no"
                        @change="saveSettings"
                        v-model="feed.additional_settings[fieldKey]"
                    >
                        {{ field.checkbox_label }}
                    </el-checkbox>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script type="text/babel">
import isArray from "lodash/isArray";
import { Delete } from '@element-plus/icons-vue';

export default {
    name: 'RemoteCalendarBlock',
    props: ['calendar', 'driver', 'feed'],
    $emit: ['refetch'],
    components: {
        Delete
    },
    data() {
        return {
            saving: false,
            saving_id: '',
            working: false
        }
    },
    methods: {
        saveConflicts(id) {
            this.saving_id = id;
            this.saving = true;
            this.$post('calendars/' + this.calendar.id + '/integrations/remote-calendars/patch-conflicts', {
                calendar_id : this.calendar.id,
                meta_id: this.feed.db_id,
                conflict_check_ids: this.feed.conflict_check_ids
            })
                .then(response => {
                    this.$notify.success(response.message);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                    this.saving_id = '';
                });
        },
        saveSettings() {
            this.saving = true;
            this.$post('calendars/' + this.calendar.id + '/integrations/remote-calendars/patch-settings', {
                calendar_id : this.calendar.id,
                meta_id: this.feed.db_id,
                additional_settings: this.feed.additional_settings
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
        },
        disconnectCalendar() {
            this.$confirm(this.$t('RemoteCalendar/disconnect_calendar_description'), this.$t('Disconnect Calendar'), {
                confirmButtonText: this.$t('Confirm Disconnect'),
                cancelButtonText: this.$t('Cancel')
            })
                .then(() => {
                    this.working = true;
                    this.$post('calendars/' + this.calendar.id + '/integrations/remote-calendars/disconnect-calendar', {
                        calendar_id : this.calendar.id,
                        meta_id: this.feed.db_id
                    })
                        .then(response => {
                            this.$notify.success(response.message);
                            this.$emit('refetch');
                        })
                        .catch(errors => {
                            this.$handleError(errors);
                        })
                        .finally(() => {
                            this.working = false;
                        });
                });
        }
    },
    mounted() {
        if (!isArray(this.feed.conflict_check_ids)) {
            this.feed.conflict_check_ids = [];
        }
    }
}
</script>
