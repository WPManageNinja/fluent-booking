<template>
    <div class="fcal_settings_body_inner fcal_settings_availability fcal_settings_availability_details">
        <div class="fcal_section_header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><a @click="goBackToList">{{ $t('Availability') }}</a></el-breadcrumb-item>
                <el-breadcrumb-item v-if="scheduleInfo">{{ scheduleInfo.host_name }}</el-breadcrumb-item>
            </el-breadcrumb>
        </div>

        <el-skeleton v-if="loading" :rows="5" animated/>

        <div v-if="!loading" class="fcal_settings_content_wrap">
            <el-form label-position="top">
                <div class="fcal_availability_header_wrap">
                    <el-form-item class="fcal_availability_header">
                        <h3> {{ scheduleInfo.title }} <el-icon style="cursor: pointer" @click="toggleEditTitle"><EditPen /></el-icon>
                            <span v-if="scheduleInfo?.settings?.default" class="default-schedule-badge">
                                <el-icon><StarFilled /></el-icon> {{ $t('Default schedule') }}
                            </span>
                        </h3>
                        <div class="fcal_edit_availability_title">
                            <el-input v-if="editScheduleTitleShow" v-model="scheduleInfo.title" :placeholder="$t('Enter Schedule Title')">
                                <template #append>
                                    <el-button @click="updateTitle">{{ $t('Update') }}</el-button>
                                </template>
                            </el-input>
                        </div>

                        <span class="sub-label">{{ $t('edit_schedule_description') }}</span>

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
                                        <el-button plain text @click="handleCommand('set_default')"><el-icon><StarFilled /></el-icon>
                                            {{ $t('Set as Default') }}</el-button>
                                    </el-dropdown-item>
                                    <el-dropdown-item>
                                        <el-button plain text @click="handleCommand( 'delete')"><el-icon><Delete /></el-icon>
                                            {{ $t('Delete') }}</el-button>
                                    </el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </el-form-item>
                    <el-form-item v-if="scheduleInfo" :label="$t('Timezone:')" class="fcal_availability_header fcal_global_timezone">
                        <TimeZoneSelector v-model="scheduleInfo.settings.timezone"/>
                    </el-form-item>
                </div>

                <el-form-item class="fcal_tab_schedule">
                    <div class="fcal_availability_body">
                        <div class="fcal_availability_setting">
                            <WeeklySchedules
                                :weekly_schedules="scheduleInfo.settings?.weekly_schedules"
                                :title="$t('Weekly Hours')"
                            />
                            <date-over-rides
                                v-if="scheduleInfo?.settings"
                                :settings="scheduleInfo?.settings"
                                :title="$t('Add date overrides')"
                                @overridesUpdated="updateSchedule"
                            />
                        </div>
                    </div>
                </el-form-item>
            </el-form>
            <div class="fcal_settings_footer">
                <SaveButton :saving="saving" :label="$t('Save Changes')" @save="updateSchedule"/>
            </div>
        </div>

        <el-skeleton v-if="loading" :rows="5" animated/>
        <div v-else class="fcal_uses_lists_wrap">
            <div class="fcal_section_header">
                <div class="fcal_title">
                    <h3>{{ $t('Usages List') }}</h3>
                </div>
            </div>

            <div v-if="!usagesLoading" class="fcal_uses_list_body">
                <div v-if="scheduleInfo.usage_count" class="fcal_card_items">
                    <div v-for="usage in availabilityUsages" class="fcal_card_item">
                        <div @click="goToEvent(usage)" class="fcal_card_wrap">
                            <div class="fcal_card_item_details fcal_availability_card">
                                <h4>{{ usage.title }}</h4>
                                <p class="fcal_icon_line">
                                    <el-icon>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                             stroke-linejoin="round" class="h-3.5 w-3.5">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="2" x2="22" y1="12" y2="12"></line>
                                            <path
                                                d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                        </svg>
                                    </el-icon>
                                    <span>{{ usage.calendar.author_timezone }}</span>
                                </p>
                            </div>
                            <div class="fcal_card_actions">
                                <el-button class="fcal_plain_btn">
                                    {{ $t('View Event') }}
                                </el-button>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-else class="fcal_no_usage_list">
                    {{ $t('No events are using this schedule') }}
                </p>
                <div class="fcal_right fcal_tm20">
                    <pagination popper-class="fcal_select" :pagination="pagination" @fetch="fetchAvailabilityUsages"/>
                </div>
            </div>
            <el-skeleton v-else :row="4" animated/>
        </div>
    </div>
</template>

<script>
import { StarFilled, Setting, Location, MoreFilled } from '@element-plus/icons-vue';
import WeeklySchedules from "../Calendars/parts/WeeklySchedules";
import DateOverRides from "../Calendars/Edit/_DateOverRides";
import ScheduleIcon from "../../Components/Icons/ScheduleIcon";
import TimezoneIcon from '../../Components/Icons/TimezoneIcon';
import SaveButton from '../../Components/Buttons/SaveButton.vue';
import Pagination from '../../Pieces/Pagination';
import TimeZoneSelector from '../Calendars/parts/TimeZoneSelector';

export default {
    name: "AvailabilityDetails",
    props: ['schedule_id'],
    components: {
        DateOverRides,
        WeeklySchedules,
        TimezoneIcon,
        SaveButton,
        ScheduleIcon,
        Pagination,
        TimeZoneSelector,
        StarFilled,
        Setting,
        Location,
        MoreFilled
    },
    data() {
        return {
            loading: false,
            usagesLoading: false,
            saving: false,
            scheduleInfo: '',
            editScheduleTitleShow: false,
            availabilityUsages: [],
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 5
            }
        }
    },
    methods: {
        goBackToList() {
            this.$router.push({name: 'availability'});
        },
        goToEvent(event) {
            this.$router.push({
                name: 'event_details',
                params: { calendar_id: event.calendar_id, event_id: event.id}
            })
        },
        toggleEditTitle() {
            this.editScheduleTitleShow = !this.editScheduleTitleShow;
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
        fetchAvailabilityUsages() {
            this.usagesLoading = true;
            this.$get('availability/' + this.schedule_id + '/usages',{
                per_page: this.pagination.per_page,
                page: this.pagination.current_page,
            })
                .then(response => {
                    this.availabilityUsages = response.usages.data;
                    this.pagination.total   = response.usages.total;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.usagesLoading = false;

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
                    this.editScheduleTitleShow = false;
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
            this.$confirm(this.$t('Are you sure you want to delete this availability?'), this.$t('Delete Availability'), {
                    confirmButtonText: this.$t('Delete'),
                    cancelButtonText: this.$t('Cancel'),
                    type: 'warning'
                }).then(() => {
                    this.$del('availability/' + this.schedule_id)
                        .then(response => {
                            this.$handleSuccess(response.message);
                            this.goBackToList();
                        })
                        .catch(errors => {
                            this.$handleError(errors);
                        });
                })
                return;
        },
        handleCommand(command) {
            if (command == 'set_default') {
                this.updateStatus();
            }
            else if (command == 'delete') {
                this.deleteStatus();
            }
        }
    },
    mounted() {
        this.fetchSchedule();
        this.fetchAvailabilityUsages();
    }
}
</script>
