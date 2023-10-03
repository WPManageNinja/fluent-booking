<template>
    <div class="fcal_settings_body_inner fcal_settings_availability">
        <div class="fcal_settings_header">
            <h3>Availability</h3>
        </div>
        <div v-if="!loading" class="fcal_settings_content_wrap">
            <el-form label-position="top">
                <el-form-item label="Available hours">
                    <span class="sub-label">Edit the schedule below so that you can apply to your event/booking types</span>
                </el-form-item>
                <el-form-item label="Schedule" class="fcal_tab_schedule">
                    <el-button class="fcal_plain_btn fcal_add_new_tab" @click="addNewSchedule">
                        <el-icon><Plus /></el-icon>
                    </el-button>
                    <el-tabs
                        v-model="scheduleTabValue"
                        type="card"
                        class="fcal_tabs2"
                    >
                        <el-tab-pane
                            v-for="item in scheduleTabs"
                            :key="item.id"
                            :name="item.id"
                        >
                            <template #label>
                                <el-icon><ScheduleIcon/></el-icon> {{ item.title }}
                            </template>
                            <div class="fcal_availability_body">
                                <div class="fcal_availability_header">
                                    <h3> Working Hours Schedule
                                        <span v-if="item.settings.default" class="default-schedule-badge">
                                            <el-icon><StarFilled /></el-icon> Default schedule
                                        </span>
                                    </h3>
                                </div>
                                <div class="timezone">
                                    <div class="fcal_timezone_text">
                                        <el-icon><TimezoneIcon/></el-icon>
                                        <p>{{ item.settings.timezone }}</p>
                                    </div>
                                </div>
                                <div class="fcal_availability_setting">
                                    <WeeklySchedules
                                        :weekly_schedules="item.settings.weekly_schedules"
                                        title="Weekly Hours"
                                    />
                                    <date-over-rides
                                        :settings="item.settings"
                                        title="Add date overrides"
                                    />
                                </div>
                            </div>
                            <div class="fcal_settings_footer">
                                <el-button @click="updateSchedule(item)" class="fcal_primary_btn">
                                    Save
                                </el-button>
                            </div>
                        </el-tab-pane>
                    </el-tabs>
                </el-form-item>
            </el-form>
        </div>
        <el-skeleton v-else :rows="5" animated/>
        <el-dialog
            v-model="dialogVisible"
            title="Add New Schedule"
            width="30%"
        >
            <el-form label-position="top">
                <el-form-item label="Schedule Title">
                    <el-input v-model="scheduleTitle" />
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button class="fcal_plain_btn" @click="dialogVisible = false">
                        Cancel
                    </el-button>
                    <el-button class="fcal_primary_btn" @click="createSchedule()">
                        Add
                    </el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script>
import { Calendar, Plus, StarFilled } from '@element-plus/icons-vue';
import ScheduleSettings from "../Calendars/Edit/_ScheduleSettings";
import WeeklySchedules from "../Calendars/parts/WeeklySchedules";
import DateOverRides from "../Calendars/Edit/_DateOverRides";
import ScheduleIcon from "../../Components/Icons/ScheduleIcon";
import TimezoneIcon from '../../Components/Icons/TimezoneIcon';

export default {
    name: "AvailabilitySettings",
    components: {
        DateOverRides,
        WeeklySchedules,
        TimezoneIcon,
        Calendar,
        Plus,
        ScheduleSettings,
        ScheduleIcon,
        StarFilled
    },
    data() {
        return {
            loading: false,
            saving: false,
            dialogVisible: false,
            scheduleSchema: this.appVars.schedule_schema,
            scheduleTabs: [],
            scheduleTabValue: '',
            scheduleTitle: ''
        }
    },
    methods: {
        addNewSchedule() {
            this.scheduleTitle = '';
            this.dialogVisible = true;
        },
        addScheduleTab(schedule) {
            this.scheduleTabs.push({
                title: schedule.key,
                id: schedule.id,
                settings: {
                    timezone: schedule.value.timezone,
                    default: schedule.value.default,
                    date_overrides: schedule.value.date_overrides,
                    weekly_schedules: schedule.value.weekly_schedules,
                },
            })
            this.scheduleTabValue = schedule.id;
        },
        fetchSchedules() {
            this.loading = true;
            this.$get('availability')
                .then(response => {
                    this.scheduleTabs = response.schedules;
                    this.scheduleTabValue = response.schedules[0].id;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        createSchedule() {
            this.saving = true;
            this.$post('availability/', {
                title: this.scheduleTitle,
                schedule: this.scheduleSchema
            })
                .then(response => {
                    this.$handleSuccess(response);           
                    this.addScheduleTab(response.schedule);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                    this.dialogVisible = false;
                });
        },
        updateSchedule(item) {
            this.saving = true;
            this.$post('availability/' + item.id, {
                title: item.title,
                settings: item.settings,
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
        this.fetchSchedules();
    }
}
</script>
