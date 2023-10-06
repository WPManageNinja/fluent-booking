<template>
    <div class="fcal_settings_body_inner fcal_settings_availability fcal_settings_availability_details">
        <div class="fcal_settings_header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><a @click="goBackToList">Availability</a></el-breadcrumb-item>
                <el-breadcrumb-item>{{ scheduleInfo.host_name }}</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div v-if="!loading" class="fcal_settings_content_wrap">
            <el-form label-position="top">
                <el-form-item label="Available hours" class="fcal_availability_header">
                    <span class="sub-label">Edit the schedule below so that you can apply to your event/booking types</span>

                    <h3> {{ scheduleInfo.title }}
                        <span v-if="scheduleInfo?.settings?.default" class="default-schedule-badge">
                            <el-icon><StarFilled /></el-icon> Default schedule
                        </span>
                    </h3>

                    <div class="timezone">
                        <div class="fcal_timezone_text">
                            <el-icon><TimezoneIcon/></el-icon>
                            <p>{{ scheduleInfo.settings?.timezone }}</p>
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
                                    <el-button plain text @click="handleCommand('edit')"><el-icon><EditPen /></el-icon> Edit Name</el-button>
                                </el-dropdown-item>
                                <el-dropdown-item>
                                    <el-button plain text @click="handleCommand('set_as')"><el-icon><StarFilled /></el-icon> Set as Default</el-button>
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
                                :weekly_schedules="scheduleInfo.settings?.weekly_schedules"
                                title="Weekly Hours"
                            />
                            <date-over-rides
                                v-if="scheduleInfo?.settings"
                                :settings="scheduleInfo?.settings"
                                title="Add date overrides"
                            />
                        </div>
                    </div>
                </el-form-item>
            </el-form>
            <div class="fcal_settings_footer">
                <SaveButton :saving="saving" label="Save Changes" @save="updateSchedule"/>
            </div>
        </div>
        <el-skeleton v-else :rows="5" animated/>
        <el-dialog
            v-model="dialogVisible"
            title="Add New Schedule"
            width="30%"
            class="fcal_dialog"
        >
            <el-form label-position="top">
                <el-form-item label="Schedule Title">
                    <el-input v-model="scheduleInfo.title" />
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button class="fcal_plain_btn" @click="dialogVisible = false">Cancel</el-button>
                    <SaveButton :saving="saving" label="Add" @save="updateTitle"/>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script>
import { StarFilled, Setting, Delete, EditPen } from '@element-plus/icons-vue';
import ScheduleSettings from "../Calendars/Edit/_ScheduleSettings";
import WeeklySchedules from "../Calendars/parts/WeeklySchedules";
import DateOverRides from "../Calendars/Edit/_DateOverRides";
import ScheduleIcon from "../../Components/Icons/ScheduleIcon";
import TimezoneIcon from '../../Components/Icons/TimezoneIcon';
import SaveButton from '../../Components/Buttons/SaveButton.vue';

export default {
    name: "AvailabilityDetailsSettings",
    props: ['schedule', 'schedule_id'],
    emits: ['backToList'],
    components: {
        DateOverRides,
        WeeklySchedules,
        TimezoneIcon,
        SaveButton,
        ScheduleSettings,
        ScheduleIcon,
        StarFilled,
        Setting,
        Delete,
        EditPen
    },
    data() {
        return {
            loading: false,
            saving: false,
            dialogVisible: false,
            scheduleInfo: this.schedule || '',
        }
    },
    methods: {
        goBackToList() {
            this.$router.push({name: 'availability'});
            this.$emit('backToList');
        },
        fetchSchedule() {
            this.loading = true;
            this.$get('availability/' + this.schedule_id)
                .then(response => {
                    this.scheduleInfo = response.schedule;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        updateSchedule() {
            this.saving = true;
            this.$post('availability/' + this.schedule_id, {
                schedule: this.scheduleInfo,
            })
                .then(response => {
                    this.$handleSuccess(response.message);      
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        updateTitle() {
            this.saving = true;
            this.$post('availability/' + this.schedule_id + '/update-title', {
                title: this.scheduleInfo.title
            })
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.scheduleInfo.title = response.title;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                    this.dialogVisible = false;
                });
        },
        updateStatus() {
            this.saving = true;
            this.$post('availability/' + this.schedule_id + '/update-status')
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.scheduleInfo.settings.default = true;       
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        deleteStatus() {
            this.$confirm('Are you sure you want to delete this availability?', 'Delete Availability', {
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    type: 'warning'
                }).then(() => {
                    this.$del('availability/' + this.schedule_id)
                        .then(response => {
                            this.$handleSuccess(response.message);
                        })
                        .catch(errors => {
                            this.$handleError(errors);
                        });
                })
                return;
        },
        handleCommand(command) {
            if (command == 'edit') {
                this.dialogVisible = true;
            }
            else if (command == 'set_as') {
                this.updateStatus();
            }
            else if (command == 'delete') {
                this.deleteStatus();
            }
        }
    },
    mounted() {
        if (!this.schedule) {
            this.$router.push({query: {schedule_id: this.schedule_id}});
            this.fetchSchedule();
        }
    }
}
</script>
