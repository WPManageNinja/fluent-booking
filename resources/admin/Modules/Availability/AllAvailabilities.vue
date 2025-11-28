<template>
    <div class="fcal_section fcal_availability_route fcal_section_narrow">
        <div class="fcal_section_header">
            <div class="fcal_title">
                <h3>{{ $t('Availability') }}</h3>
                <p>{{ $t('Configure times when you are available for bookings.') }}</p>
            </div>
            <div class="fcal_actions">
                <el-radio-group class="fcal_radio_switch" size="large" v-model="filters.author" @change="fetchAvailabilities">
                    <el-radio-button label="me">{{ $t('My Schedules') }}</el-radio-button>
                    <el-radio-button label="all">{{ $t('All Schedules') }}</el-radio-button>
                </el-radio-group>

                <el-button @click="creatingNew = true" type="primary">
                    <el-icon>
                        <Plus/>
                    </el-icon>
                    <span>{{ $t('Add New') }}</span>
                </el-button>
            </div>
        </div>
        <div class="fcal_section_body">
            <el-skeleton v-if="loading" :animated="true" :rows="5"></el-skeleton>
            <div v-else class="fcal_card_items">
                <template v-if="availabilities.length">
                    <div v-for="availability in availabilities" :key="availability.id" class="fcal_card_item">
                        <div @click="gotoDetails(availability)" class="fcal_card_wrap">
                            <div class="fcal_card_item_details fcal_availability_card">
                                <h4>
                                    {{ availability.title }}
                                    <span v-if="availability.settings?.default && filters.author == 'me'"
                                          class="default-schedule-badge">
                                    <el-icon><StarFilled/></el-icon> {{ $t('Default') }}
                                </span>
                                </h4>
                                <p class="fcal_human_text" v-html="formatAvailability(availability.settings.weekly_schedules)"></p>
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
                                    <span>{{ availability.settings?.timezone }}</span>
                                </p>
                                <p class="fcal_icon_line">
                                    <el-icon>
                                        <Location/>
                                    </el-icon>
                                    <span v-if="availability.usage_count">{{ availability.usage_count }} {{ $t('calendar events are using this schedule') }}</span>
                                    <span v-else>{{ $t('No events are using this schedule') }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="fcal_card_actions">
                            <el-dropdown trigger="click" popper-class="fcal_select">
                                <el-icon class="el-dropdown-link"><MoreFilled /></el-icon>
                                <template #dropdown>
                                    <el-dropdown-menu>
                                        <el-dropdown-item v-if="!availability.settings?.default && filters.author == 'me'"
                                                          @click="updateDefaultStatus(availability.id)">
                                            <el-icon><StarFilled /></el-icon>
                                            {{ $t('Set as Default') }}
                                        </el-dropdown-item>
                                        <el-dropdown-item @click="cloneAvailability(availability.id)">
                                            <el-icon><CopyDocument /></el-icon>
                                            {{ $t('Duplicate') }}
                                        </el-dropdown-item>
                                        <el-dropdown-item @click="deleteAvailability(availability.id)">
                                            <el-icon><Delete /></el-icon>
                                            {{ $t('Delete') }}
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </template>
                            </el-dropdown>
                        </div>
                    </div>
                </template>
                <el-empty class="fcal_empty" v-else :description="$t('No Availability found')"/>
            </div>
            <div class="fcal_right fcal_tm20">
                <pagination popper-class="fcal_select" :pagination="pagination" @fetch="fetchAvailabilities"/>
            </div>
        </div>

        <el-dialog
            v-model="creatingNew"
            :title="$t('Add New Availability Schedule')"
            width="40%"
            class="fcal_dialog fcal_dialog_body_p0 fcal_availability_dialog"
            :close-on-press-escape="false"
            :close-on-click-modal="false"
        >
            <el-form label-position="top">
                <el-form-item :label="$t('Schedule Title *')">
                    <el-input v-model="newSchedule.title"/>
                </el-form-item>
                <el-form-item :label="$t('Select Your Timezone *')" class="fcal_global_timezone">
                    <time-zone-selector v-model="newSchedule.timezone"/>
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button class="fcal_plain_btn" @click="cancelCreate">{{ $t('Cancel') }}</el-button>
                    <SaveButton :saving="saving" :label="$t('Add New Schedule')" @save="createNew"/>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script>
import SaveButton from "../../Components/Buttons/SaveButton.vue";
import TimeZoneSelector from "../Calendars/parts/TimeZoneSelector.vue";
import { StarFilled, MoreFilled } from "@element-plus/icons-vue";
import Pagination from "../../Pieces/Pagination.vue";

export default {
    name: 'AllAvailabilities',
    components: { Pagination, StarFilled, MoreFilled, TimeZoneSelector, SaveButton },
    data() {
        return {
            loading: false,
            availabilities: [],
            pagination: {
                current_page: 1,
                per_page: this.appVars.default_paginations?.availabilities || 10,
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
        removeSchedule(scheduleId) {
            const updatedAilabilities = this.availabilities.filter(schedule => schedule.id !== scheduleId);
            this.availabilities = updatedAilabilities;
        },
        cancelCreate() {
            this.newSchedule.title = '';
            this.creatingNew = false;
        },
        gotoDetails(schedule) {
            this.$router.push({
                name: 'availability_details',
                params: { schedule_id: schedule.id }
            })
        },
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
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        createNew() {
            if (!this.newSchedule.title) {
                this.$handleError("Title is required");
                return;
            } else if (!this.newSchedule.timezone) {
                this.$handleError("Timezone is required");
                return;
            }
            this.saving = true;
            this.$post('availability', this.newSchedule)
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.gotoDetails(response.schedule);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                    this.creatingNew = false;
                });
        },
        updateDefaultStatus(scheduleId) {
            this.saving = true;
            this.$post('availability/' + scheduleId + '/update-status')
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.fetchAvailabilities();     
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        cloneAvailability(availabilityId) {
            this.saving = true;
            this.$post('availability/' + availabilityId + '/clone')
                .then(response => {
                    this.$handleSuccess(response);
                    this.gotoDetails(response.schedule);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        deleteAvailability(availabilityId) {
            this.$confirm('Are you sure you want to delete this schedule?', 'Delete Schedule', {
                    confirmButtonText: this.$t('Delete'),
                    cancelButtonText: this.$t('Cancel'),
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
        formatAvailability(avail) {

            if(!avail) return '';

            const days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            const humanDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            let segments = [];
            let currentSegment = null;

            for (let i = 0; i < days.length; i++) {
                const day = days[i];
                const slots = avail[day].slots.map(slot => `${slot.start}-${slot.end}`).join(" & ");

                if (avail[day].enabled && slots) {
                    if (currentSegment && currentSegment.slots === slots) {
                        currentSegment.days.push(this.$t(humanDays[i].toLowerCase()));
                    } else {
                        if (currentSegment) {
                            segments.push(currentSegment);
                        }
                        currentSegment = { days: [this.$t(humanDays[i].toLowerCase())], slots: slots };
                    }
                } else {
                    if (currentSegment) {
                        segments.push(currentSegment);
                        currentSegment = null;
                    }
                }
            }

            if (currentSegment) {
                segments.push(currentSegment);
            }

            const groupedDays = segments.reduce((acc, segment) => {
                const key = segment.slots;
                if (!acc[key]) {
                    acc[key] = [];
                }
                acc[key].push(segment.days);
                return acc;
            }, {});

            const results = [];
            for (const slots in groupedDays) {
                const daySegments = groupedDays[slots].map(segment => {
                    if (segment.length === 1) {
                        return segment[0];
                    } else {
                        return `${segment[0]}-${segment[segment.length - 1]}`;
                    }
                });
                results.push(`${daySegments.join(' & ')}, ${slots}`);
            }

            return results.join('<span class="fc_space_10"> </span>');
        }
    },
    mounted() {
        this.fetchAvailabilities();
    }
}
</script>
