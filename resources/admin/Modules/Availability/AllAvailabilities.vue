<template>
    <div class="fcal_section fcal_availability_route fcal_section_narrow">
        <div class="fcal_section_header">
            <div class="fcal_title">
                <h3>Availability</h3>
                <p>Configure times when you are available for bookings.</p>
            </div>
            <div class="fcal_actions">
                <el-radio-group class="fcal_radio_switch" size="large" v-model="filters.author" @change="fetchAvailabilities">
                    <el-radio-button label="me">My Schedules</el-radio-button>
                    <el-radio-button label="all">All Schedules</el-radio-button>
                </el-radio-group>

                <el-button @click="creatingNew = true" type="primary">
                    <el-icon>
                        <Plus/>
                    </el-icon>
                    <span>Add New</span>
                </el-button>
            </div>
        </div>
        <div class="fcal_section_body">
            <el-skeleton v-if="loading" :animated="true" :rows="5"></el-skeleton>
            <div v-else class="fcal_card_items">
                <div v-for="availability in availabilities" :key="availability.id" class="fcal_card_item">
                    <div class="fcal_card_wrap">
                        <div @click="gotoDetails(availability)" class="fcal_card_item_details fcal_availability_card">
                            <h4>
                                {{ availability.title }}
                                <span v-if="availability.settings.default && filters.author == 'me'"
                                      class="default-schedule-badge">
                                    <el-icon><StarFilled/></el-icon> Default
                                </span>
                            </h4>
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
                                <span>{{ availability.settings.timezone }}</span>
                            </p>
                            <p class="fcal_icon_line">
                                <el-icon>
                                    <Location/>
                                </el-icon>
                                <span v-if="availability.usage_count">{{ availability.usage_count }} calendar events are using this schedule</span>
                                <span v-else>No events are using this schedule</span>
                            </p>
                        </div>
                        <div class="fcal_card_actions">
                            <el-dropdown trigger="click" popper-class="fcal_select">
                                <el-button class="fcal_plain_btn">
                                    <el-icon><MoreFilled /></el-icon>
                                </el-button>
                                <template #dropdown>
                                    <el-dropdown-menu>
                                        <el-dropdown-item @click="">
                                            <el-icon><CopyDocument /></el-icon> Duplicate
                                        </el-dropdown-item>
                                        <el-dropdown-item @click="deleteAvailability(availability.id)">
                                            <el-icon><Delete /></el-icon> Delete
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </template>
                            </el-dropdown>

                        </div>
                    </div>
                </div>
            </div>
            <div class="fcal_right fcal_tm20">
                <pagination :pagination="pagination" @fetch="fetchAvailabilities"/>
            </div>
        </div>

        <el-dialog
            v-model="creatingNew"
            title="Add New Availability Schedule"
            width="40%"
            class="fcal_dialog"
        >
            <el-form label-position="top">
                <el-form-item label="Schedule Title *">
                    <el-input v-model="newSchedule.title"/>
                </el-form-item>
                <el-form-item label="Select Your Timezone *" class="fcal_global_timezone">
                    <time-zone-selector v-model="newSchedule.timezone"/>
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button class="fcal_plain_btn" @click="creatingNew = false">Cancel</el-button>
                    <SaveButton :saving="saving" label="Add New Schedule" @save="createNew"/>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script>
import SaveButton from "../../Components/Buttons/SaveButton.vue";
import TimeZoneSelector from "../Calendars/parts/TimeZoneSelector.vue";
import { StarFilled, MoreFilled, CopyDocument, Delete } from "@element-plus/icons-vue";
import Pagination from "../../Pieces/Pagination.vue";

export default {
    name: 'AllAvailabilities',
    components: {Pagination, StarFilled, MoreFilled, CopyDocument, Delete, TimeZoneSelector, SaveButton},
    data() {
        return {
            loading: false,
            availabilities: [],
            pagination: {
                current_page: 1,
                per_page: 10,
                total: 0,
            },
            filters: {
                author: 'me'
            },
            newSchedule: {
                title: '',
                timezone: this.currentTimezone
            },
            creatingNew: false,
            saving: false,
        }
    },
    methods: {
        fetchAvailabilities() {
            this.loading = true;
            this.$get('availability', {
                per_page: this.pagination.per_page,
                page: this.pagination.current_page,
                filters: this.filters
            })
                .then(response => {
                    this.availabilities = response.availabilities.data;
                    this.pagination.total = response.availabilities.total;
                    console.log(this.availabilities);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        createNew() {
            this.saving = true;
            this.$post('availability', this.newSchedule)
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.fetchAvailabilities();
                    this.newSchedule.title = '';
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                    this.creatingNew = false;
                });
        },
        deleteAvailability(availabilityId) {
            this.$confirm('Are you sure you want to delete this schedule?', 'Delete Schedule', {
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    type: 'warning'
                }).then(() => {
                    this.$del('availability/' + availabilityId)
                        .then(response => {
                            this.$handleSuccess(response.message);
                            this.removeSchedule(availabilityId);
                        })
                        .catch(errors => {
                            this.$handleError(errors);
                        });
                })
                return;
        },
        removeSchedule(scheduleId) {
            const updatedAilabilities = this.availabilities.filter(schedule => schedule.id !== scheduleId);
            this.availabilities = updatedAilabilities;
        },
        gotoDetails(schedule) {
            this.$router.push({
                name: 'availability_details',
                params: { schedule_id: schedule.id }
            })
        }
    },
    mounted() {
        this.fetchAvailabilities();
    }
}
</script>
