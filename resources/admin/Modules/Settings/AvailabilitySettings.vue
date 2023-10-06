<template>
    <div v-if="!scheduleId" class="fcal_settings_body_inner fcal_settings_availability">
        <div class="fcal_settings_header">
            <h3>Availability</h3>
            <div class="fcal_settings_header_bottom">
                <div class="fcal_settings_header_left_action">
                    <el-select v-model="filters.author" @change="fetchSchedules" placeholder="Select" popper-class="fcal_select">
                        <el-option value="me" label="My Schedule"></el-option>
                        <el-option value="all" label="All Schedules"></el-option>
                        <el-option v-for="host in all_hosts" :key="host.id" :value="host.id" :label="host.label"></el-option>
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
                            <img :src="scope.row.host_avatar"> {{ scope.row.host_name }}
                        </h4>
                    </template>
                </el-table-column>
                <el-table-column label="Schedule" width="180">
                    <template #default="scope">
                        <h4>{{ scope.row.title }}
                            <span v-if="scope.row.settings.default" class="default-schedule-badge">
                                <el-icon><StarFilled /></el-icon> Default
                            </span>
                        </h4>
                    </template>
                </el-table-column>
                <el-table-column label="Created Date" width="180">
                    <template #default="scope">
                        <h4>{{ scope.row.created_at }}</h4>
                    </template>
                </el-table-column>
                <el-table-column label="Action" width="100">
                    <template #default="scope">
                        <el-button class="fcal_primary_btn" @click="viewDetails(scope.row)">
                            <el-icon><Edit /></el-icon>
                        </el-button>
                        <el-popconfirm
                            title="Are you sure to delete this schedule?"
                            popper-class="fcal_confirm_dialog"
                            confirm-button-type="danger"
                            @confirm="deleteSchedule(scope.row.id)"
                        >
                            <template #reference>
                                <el-button type="danger" class="fcal_danger_btn">
                                    <el-icon><Delete /></el-icon>
                                </el-button>
                            </template>
                        </el-popconfirm>
                    </template>
                </el-table-column>
            </el-table>
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
                    <el-input v-model="scheduleTitle" />
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button class="fcal_plain_btn" @click="dialogVisible = false">Cancel</el-button>
                    <SaveButton :saving="saving" label="Add" @save="createSchedule"/>
                </span>
            </template>
        </el-dialog>
        <div class="fcal_right fcal_tm20">
            <pagination :pagination="pagination" @fetch="fetchSchedules"/>
        </div>
    </div>
    <AvailabilityDetailsSettings v-if="scheduleId" @backToList="backToList" :schedule="schedule" :schedule_id="scheduleId"/>
</template>

<script>
import { Plus, StarFilled, Edit, Delete } from '@element-plus/icons-vue';
import ScheduleSettings from "../Calendars/Edit/_ScheduleSettings";
import WeeklySchedules from "../Calendars/parts/WeeklySchedules";
import DateOverRides from "../Calendars/Edit/_DateOverRides";
import AvailabilityDetailsSettings from './AvailabilityDetailsSettings';
import SaveButton from '../../Components/Buttons/SaveButton';
import Pagination from '../../Pieces/Pagination';

export default {
    name: "AvailabilitySettings",
    components: {
        DateOverRides,
        WeeklySchedules,
        ScheduleSettings,
        AvailabilityDetailsSettings,
        SaveButton,
        Pagination,
        StarFilled,
        Plus,
        Edit,
        Delete
    },
    data() {
        return {
            loading: false,
            saving: false,
            dialogVisible: false,
            schedule: '',
            scheduleId: '',
            schedules: [],
            scheduleTitle: '',
            all_hosts: null,
            filters: {
                author: 'me'
            },
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 10
            }
        }
    },
    methods: {
        viewDetails(schedule) {
            this.schedule = schedule;
            this.scheduleId = schedule.id;
            this.$router.push({
                query: { schedule_id: schedule.id }
            });
        },
        backToList() {
            this.scheduleId = '';
            this.fetchSchedules();
        },
        fetchSchedules() {
            this.loading = true;
            this.$get('availability', {
                per_page: this.pagination.per_page,
                page: this.pagination.current_page,
                filters: this.filters
            })
                .then(response => {
                    this.schedules = response.schedules;
                    this.pagination.total = response.total;
                    this.pagination.current_page = response.current_page;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        fetchHosts() {
            this.$get('admin/other-hosts')
                .then(response => {
                    this.all_hosts = response.hosts;
                });
        },
        createSchedule() {
            this.dialogVisible = false;
            this.saving = true;
            this.$post('availability/', {
                title: this.scheduleTitle
            })
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.fetchSchedules();
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                    this.dialogVisible = false;
                });
        },
        deleteSchedule(id) {
            this.$del('availability/' + id)
                .then(response => {
                    this.$handleSuccess(response.message);
                })
                .catch(errors => {
                    this.$handleError(errors);
                });
            this.fetchSchedules();
        },
    },
    mounted() {
        if (this.$route.query.schedule_id) {
            this.scheduleId = this.$route.query.schedule_id;
        }
        this.$router.push({name: 'availability'});
        this.fetchSchedules();

        if(this.hasSupport('multi_users')) {
            this.fetchHosts();
        }
    }
}
</script>
