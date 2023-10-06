<template>
    <div class="fcal_settings_body_inner fcal_settings_availability fcal_settings_availability_details">
        <div class="fcal_settings_header">
            <h3 @click="this.$router.push({name: 'settings'})"><el-icon><Back /></el-icon> Availability</h3>
        </div>
        <div v-if="!loading" class="fcal_settings_content_wrap">
            <el-form label-position="top">
                <el-form-item label="Available hours" class="fcal_availability_header">
                    <span class="sub-label">Edit the schedule below so that you can apply to your event/booking types</span>

                    <h3> Working Hours Schedule
                        <span class="default-schedule-badge">
                            <el-icon><StarFilled /></el-icon> Default schedule
                        </span>
                    </h3>

                    <div class="timezone">
                        <div class="fcal_timezone_text">
                            <el-icon><TimezoneIcon/></el-icon>
                            <p>{{ schedule.value?.timezone }}</p>
                        </div>
                    </div>

                    <el-dropdown
                            trigger="click"
                            popper-class="fcal_select"
                        >
                        <el-button class="fcal_plain_btn el-dropdown-link">
                            <el-icon><Setting /></el-icon>
                        </el-button>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item>
                                    <el-button plain text @click="handleCommand( 'edit')"><el-icon><EditPen /></el-icon> Edit Name</el-button>
                                </el-dropdown-item>
                                <el-dropdown-item>
                                    <el-button plain text @click="handleCommand( 'set_as')"><el-icon><StarFilled /></el-icon> Set as Default</el-button>
                                </el-dropdown-item>
                                <el-dropdown-item>
                                    <el-button plain text @click="handleCommand( 'delete')"><el-icon><Delete /></el-icon> Delete</el-button>
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </el-form-item>
                <el-form-item class="fcal_tab_schedule">
                    <div class="fcal_availability_body">
                        <div class="fcal_availability_setting">
                            <WeeklySchedules
                                :weekly_schedules="schedule.value?.weekly_schedules"
                                title="Weekly Hours"
                            />
                            <date-over-rides
                                v-if="schedule?.value"
                                :settings="schedule?.value"
                                title="Add date overrides"
                            />
                        </div>
                    </div>
                </el-form-item>
            </el-form>
            <div class="fcal_settings_footer">
                <el-button @click="updateSchedule(schedule)" class="fcal_primary_btn">
                    Save
                </el-button>
            </div>
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
import { Calendar, Plus, StarFilled, Setting, Delete, EditPen, Back } from '@element-plus/icons-vue';
import ScheduleSettings from "../Calendars/Edit/_ScheduleSettings";
import WeeklySchedules from "../Calendars/parts/WeeklySchedules";
import DateOverRides from "../Calendars/Edit/_DateOverRides";
import ScheduleIcon from "../../Components/Icons/ScheduleIcon";
import TimezoneIcon from '../../Components/Icons/TimezoneIcon';

export default {
    name: "AvailabilityDetailsSettings",
    components: {
        DateOverRides,
        WeeklySchedules,
        TimezoneIcon,
        Calendar,
        Plus,
        ScheduleSettings,
        ScheduleIcon,
        StarFilled,
        Setting,
        Delete,
        EditPen,
        Back
    },
    data() {
        return {
            loading: false,
            saving: false,
            dialogVisible: false,
            scheduleSchema: this.appVars.schedule_schema,
            scheduleTabs: [],
            scheduleTabValue: '1',
            scheduleTitle: '',
            schedule: '',
            scheduleID: this.$route.params.id
        }
    },
    methods: {
        handleCommand(command) {
            if (command == 'delete') {
                this.$confirm('Are you sure you want to delete this availability?', 'Delete Availability', {
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    type: 'warning'
                }).then(() => {
                    this.$del('availability/' + this.scheduleID)
                        .then(response => {
                            this.$handleSuccess(response.message);
                            this.$router.push({name: 'availability'});
                        })
                        .catch(errors => {
                            this.$handleError(errors);
                        });
                })
                return;
            }

        },
        addNewSchedule() {
            this.scheduleTitle = '';
            this.dialogVisible = true;
        },
        fetchSchedule() {
            this.loading = true;
            this.$get('availability/'+this.scheduleID)
                .then(response => {
                    this.schedule = response.schedule;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        updateSchedule(item) {
            this.saving = true;
            this.$post('availability/' + item.id, {
                settings: item.value,
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
        this.fetchSchedule();
    }
}
</script>
