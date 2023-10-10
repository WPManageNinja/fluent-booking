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
                <el-button @click="disconnectCalendar()" size="small">
                    <el-icon>
                        <Delete/>
                    </el-icon>
                </el-button>
            </div>
        </div>
        <div class="fcal_remote_body">
            <p class="fcal_remote_sub">Enable the calendars you want to check for conflicts to prevent double
                bookings.</p>
            <div class="fcal_remote_cal_items">
                <el-checkbox-group class="fcal_lined_checks" v-model="feed.conflict_check_ids">
                    <el-checkbox :disabled="saving" v-for="cal in feed.remote_calendars" :key="cal.id" :label="cal.id"
                                 @change="saveChange(cal.id)">
                        {{ cal.title }}
                        <span v-loading="saving_id == cal.id"></span>
                    </el-checkbox>
                </el-checkbox-group>

                <div v-if="feed.errors">
                    <hr />
                    <p style="color: red;" class="fcal_remote_sub">API Error: {{ feed.errors }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
<script type="text/babel">
import isArray from "lodash/isArray";

export default {
    name: 'RemoteCalendarBlock',
    props: ['calendar', 'driver', 'feed'],
    $emit: ['refetch'],
    data() {
        return {
            saving: false,
            saving_id: '',
            working: false
        }
    },
    methods: {
        saveChange(id) {
            this.saving_id = id;
            this.saving = true;
            this.$post('calendars/' + this.calendar.id + '/integrations/remote-calendars/patch-conflicts', {
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
        disconnectCalendar() {
            this.$confirm('Are you sure you want to disconnect this calendar? This action can\'t be undone.', 'Disconnect Calendar', {
                confirmButtonText: 'Confirm Disconnect',
                cancelButtonText: 'Cancel'
            })
                .then(() => {
                    this.working = true;
                    this.$post('calendars/' + this.calendar.id + '/integrations/remote-calendars/disconnect-calendar', {
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
