<template>
    <div class="fcal_settings_body_inner fcal_settings_availability">
        <div class="fcal_settings_header">
            <h3>Availability</h3>
            <div class="fcal_settings_header_bottom">
                <div class="fcal_settings_header_left_action">
                    <el-select v-model="filter" placeholder="Select" popper-class="fcal_select">
                        <el-option value="all" label="All Schedule" />
                        <el-option value="1" label="Tanbir" />
                    </el-select>
                </div>
                <div class="fcal_settings_header_right_action">
                    <el-button class="fcal_primary_btn2" @click="dialogVisible = true">
                        <el-icon><Plus /></el-icon> Add New Schedule
                    </el-button>
                </div>
            </div>
        </div>
        <div v-if="!loading" class="fcal_settings_content_wrap">
            <el-table :data="schedules">
                <el-table-column label="Name" width="180">
                    <template #default="scope">
                        <h4 class="author">
                            <img src="https://images.unsplash.com/photo-1564564321837-a57b7070ac4f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8N3x8bWFufGVufDB8fDB8fHww&auto=format&fit=crop&w=800&q=60" alt=""> {{ scope.row.title }}
                        </h4>
                    </template>
                </el-table-column>
                <el-table-column label="Schedule" width="180">
                    <template #default="scope">
                        <h4>{{ scope.row.title }}</h4>
                    </template>
                </el-table-column>
                <el-table-column label="Created Date" width="180">
                    <template #default="scope">
                        <h4>{{ scope.row.created_at }}</h4>
                    </template>
                </el-table-column>
                <el-table-column width="180">
                    <template #default="scope">
                        <el-button class="fcal_plain_btn" @click="this.$router.push({name: 'availability', params: {id: scope.row.id}})">
                            View Details
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
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
import { Calendar, Plus, StarFilled, Setting, Delete, EditPen } from '@element-plus/icons-vue';
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
            scheduleSchema: this.appVars.schedule_schema,
            schedules: [],
            scheduleTitle: '',
            filter: 'all'
        }
    },
    methods: {
        handleCommand(tab, command) {
            if (command == 'delete') {
                this.$confirm('Are you sure you want to delete this availability?', 'Delete Availability', {
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    type: 'warning'
                }).then(() => {
                    this.$del('availability/' + tab.id)
                        .then(response => {
                            this.$handleSuccess(response.message);
                            this.fetchSchedules();
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
        addScheduleTab(schedule) {
            this.schedules.push({
                title: schedule.key,
                id: schedule.id,
                created_at: schedule.created_at,
                settings: {
                    timezone: schedule.value.timezone,
                    default: schedule.value.default,
                    date_overrides: schedule.value.date_overrides,
                    weekly_schedules: schedule.value.weekly_schedules,
                },
            })
        },
        fetchSchedules() {
            this.loading = true;
            this.$get('availability')
                .then(response => {
                    this.schedules = response.schedules;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        createSchedule() {
            this.dialogVisible = false;
            this.saving = true;
            this.$post('availability/', {
                title: this.scheduleTitle,
                schedule: this.scheduleSchema
            })
                .then(response => {
                    this.$handleSuccess(response);
                    this.addScheduleTab(response.schedule);
                    console.log(response.schedule);
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
