<template>
    <div class="fcal_schedule_details">
        <div class="fcal_schedule_details_content">
            <div v-if="showing_spots" class="fcal_schedule_event_infos">
                <div class="fcal_schedule_header_bar">
                    {{ first_spot.slot_minutes }} minutes meeting with {{ first_spot.first_name }}
                    {{ first_spot.last_name }} @ {{ toCurrentTimezone(first_spot.start_time, 'DD MMM YYYY, hh:mma') }}
                    <el-dropdown trigger="click" popper-class="fcal_select">
                            <span class="el-dropdown-link">
                                 <el-icon><MoreFilled /></el-icon>
                            </span>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item><el-icon><Refresh /></el-icon> Reschedule</el-dropdown-item>
                                <el-dropdown-item><el-icon><Close /></el-icon> Cancel</el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
                <div class="fcal_schedule_event_infos_body">
                    <div class="fcal_schedule_details_header">
                        <h1 class="fcal_header_title">
                            Event Information
                        </h1>
                    </div>

                    <div class="fcal_schedule_details_event">
                        <div class="fcal_schedule_details_event_item">
                            <h3>Meeting Host</h3>
                            <p>{{ first_spot.first_name }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>Event Title</h3>
                            <p>{{ first_spot.slot.title }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>Event Duration</h3>
                            <p>{{ first_spot.slot_minutes }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>Location</h3>
                            <div v-html="first_spot.location"></div>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>Status</h3>
                            <p>{{ first_spot.status }}</p>
                        </div>
                        <div v-if="first_spot.source_url" class="fcal_schedule_details_event_item">
                            <h3>Booking URL</h3>
                            <div class="fcal_spot_details_value">
                                <a target="_blank" rel="nofollow" :href="first_spot.source_url">{{first_spot.source_url}}</a>
                            </div>
                        </div>
                        <div v-if="first_spot.source != 'web'" class="fcal_schedule_details_event_item">
                            <h3>Booked From</h3>
                            <div class="fcal_spot_details_value"
                                v-html="first_spot.source">
                            </div>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>Fluent CRM</h3>
                            <div class="fcal_spot_details_value"
                                v-html="first_spot.crm_profile">
                            </div>
                        </div>
                    </div>
                    <div class="fcal_schedule_details_event_additional fcal_schedule_details_event_item">
                        <h3>Additional Note <el-icon @click="isAdditionalNoteOpen=true"><EditPen /></el-icon></h3>
                        <p>N/A</p>
                        <div v-if="isAdditionalNoteOpen" class="fcal_schedule_additional_form">
                            <el-input
                                type="textarea"
                                placeholder="Additional Note"
                            />
                            <div class="action">
                                <el-button class="fcal_plain_btn" @click="isAdditionalNoteOpen=false">Cancel</el-button>
                                <el-button class="fcal_primary_btn">Update</el-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fcal_schedule_event_infos">
                <div class="fcal_schedule_event_infos_body">
                    <div class="fcal_schedule_details_header">
                        <h1 class="fcal_header_title">
                            Invitees Information
                        </h1>
                    </div>
                    <el-table :data="showing_spots">
                        <el-table-column label="Name" width="180">
                            <template #default="scope">
                                {{ scope.row.first_name }} {{ scope.row.last_name }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Email" width="200">
                            <template #default="scope">
                                {{ scope.row.email }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Time Zone" width="150">
                            <template #default="scope">
                                {{ scope.row.person_time_zone }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Booked At" width="150">
                            <template #default="scope">
                                {{ toCurrentTimezone(scope.row.created_at, 'DD MMM YYYY, hh:mma') }}
                            </template>
                        </el-table-column>
                        <el-table-column width="40">
                            <template #default="scope">
                                <el-dropdown trigger="click" popper-class="fcal_select">
                                    <span class="el-dropdown-link">
                                         <el-icon><MoreFilled /></el-icon>
                                    </span>
                                    <template #dropdown>
                                        <el-dropdown-menu>
                                            <el-dropdown-item><el-icon><Refresh /></el-icon> Reschedule</el-dropdown-item>
                                            <el-dropdown-item><el-icon><Close /></el-icon> Cancel</el-dropdown-item>
                                        </el-dropdown-menu>
                                    </template>
                                </el-dropdown>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>
        </div>
        <div class="fcal_booking_activities">
            <BookingActivities :event_id="first_spot.event_id"/>
        </div>
    </div>
</template>

<script>
import { Back, MoreFilled, Refresh, Close, EditPen } from '@element-plus/icons-vue';
import BookingActivities from "./_BookingActivities";
export default {
    name: "ScheduleSpotDetails",
    props: ['spot', 'spot_id'],
    $emits: ['spotFetched'],
    components: {
        BookingActivities,
        Back,
        MoreFilled,
        Refresh,
        Close,
        EditPen
    },
    data() {
        return {
            loading: false,
            updating: false,
            first_spot: null,
            fetching_spot: false,
            showing_spots: this.spot,
            first_spot: this.spot[0],
            isAdditionalNoteOpen: false,
            cancel_reason: '',
        }
    },
    watch: {
        spot_id() {
            this.showing_spots = this.spot;
            this.first_spot = this.spot[0];
            this.fetching_spot = false;
        }
    },
    methods: {
        fetchSchedule() {
            this.fetching_spot = true;
            this.$get(`schedules/${this.spot_id}`)
                .then(response => {
                    this.showing_spots = response.schedule;
                    this.first_spot = response.schedule[0];
                    this.$emit('spotFetched', response.schedule);
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.fetching_spot = false;
                });
        },
        updateScheduleStatus(new_status) {
            this.updating = true;
            const data = {
                column: 'status',
                value: new_status
            };

            if (new_status == 'cancelled') {
                data.cancel_reason = this.cancel_reason;
            }

            const updatePromises = this.showing_spots.map(spot => {
                return this.$put(`schedules/${spot.id}`, data)
                    .then(response => {
                        data.message = response.message;
                        spot.status = new_status;
                        spot.happening_status = '';
                        this.cancelDialog = false;
                    })
            });

            Promise.all(updatePromises)
                .then(() => {
                    this.$handleSuccess(data.message);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                });
        },
        cancelEvent() {
            this.updateScheduleStatus('cancelled');
        },
        handleDataUpdated(data) {
            if (this.spot) {
                this.spot[data.key] = data.value;
            }
        }
    },
    mounted() {
        if (!this.spot) {
            this.fetchSchedule();
        }
    }
}
</script>