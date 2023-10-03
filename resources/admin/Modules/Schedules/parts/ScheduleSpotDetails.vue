<template>
    <div class="fcal_schedule_details">
        <div class="fcal_schedule_details_content">
            <div v-if="showing_spot" class="fcal_schedule_event_infos">
                <div class="fcal_schedule_header_bar">
                    {{ meetingDetails }}
                    <el-dropdown v-if="isMoreIconVisible" trigger="click" popper-class="fcal_select">
                        <span class="el-dropdown-link">
                            <el-icon><MoreFilled /></el-icon>
                        </span>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item>
                                    <el-icon><Refresh /></el-icon> Reschedule
                                </el-dropdown-item>
                                <el-dropdown-item @click="cancelDialog = true">
                                    <el-icon><Close /></el-icon> Cancel
                                </el-dropdown-item>
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
                            <p>{{ showing_spot.first_name }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>Event Title</h3>
                            <p>{{ showing_spot.slot.title }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>Event Duration</h3>
                            <p>{{ showing_spot.slot_minutes }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>Location</h3>
                            <div v-html="showing_spot.location"></div>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>Status</h3>
                            <p>{{ showing_spot.status }}</p>
                        </div>
                        <div v-if="showing_spot.source_url" class="fcal_schedule_details_event_item">
                            <h3>Booking URL</h3>
                            <div class="fcal_spot_details_value">
                                <a target="_blank" rel="nofollow" :href="showing_spot.source_url">{{showing_spot.source_url}}</a>
                            </div>
                        </div>
                        <div v-if="showing_spot.source != 'web'" class="fcal_schedule_details_event_item">
                            <h3>Booked From</h3>
                            <div class="fcal_spot_details_value"
                                v-html="showing_spot.source">
                            </div>
                        </div>
                        <div v-if="showing_spot.crm_profile" class="fcal_schedule_details_event_item">
                            <h3>Fluent CRM</h3>
                            <div class="fcal_spot_details_value"
                                v-html="showing_spot.crm_profile">
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
                        <el-table-column label="Name" width="150">
                            <template #default="scope">
                                {{ scope.row.first_name }} {{ scope.row.last_name }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Email" width="200">
                            <template #default="scope">
                                {{ scope.row.email }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Time Zone" width="120">
                            <template #default="scope">
                                {{ scope.row.person_time_zone }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Booked At" width="150">
                            <template #default="scope">
                                {{ toCurrentTimezone(scope.row.created_at, 'DD MMM YYYY, hh:mma') }}
                            </template>
                        </el-table-column>
                        <el-table-column width="40" fixed="right">
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
        <div v-if="showing_spot" class="fcal_booking_activities">
            <BookingActivities :event_id="showing_spot.event_id"/>

            <ScheduleProfile />
        </div>
        <el-dialog
            v-model="cancelDialog"
            width="30%"
            title="Cancel Meeting"
            class="fcal_modal"
        >
            <div style="text-align: center;">
                <h3>{{ showing_spot.slot.title }}</h3>
                <p class="fcal_meeting_with">with <b>{{ showing_spot.first_name }} {{ showing_spot.last_name }}</b></p>
                <p class="fcal_meeting_time">{{ meetingTime }}</p>
                <p>Please confirm that you would like to cancel this event. A cancellation email will also go out to the invitee</p>
                <el-input type="textarea" v-model="cancel_reason" placeholder="Reason for cancellation"></el-input>
            </div>
            <template #footer>
              <span class="dialog-footer">
                <el-button
                    @click="cancelDialog = false"
                    class="fcal_plain_btn"
                >
                    No, Don't cancel
                </el-button>
                <el-button
                    v-loading="updating"
                    :disabled="updating"
                    class="fcal_primary_btn"
                    @click="cancelEvent()">
                  Yes, Cancel
                </el-button>
              </span>
            </template>
        </el-dialog>
    </div>
</template>

<script>
import { Back, MoreFilled, Refresh, Close, EditPen } from '@element-plus/icons-vue';
import BookingActivities from "./_BookingActivities";
import ScheduleProfile from "./ScheduleProfile";
export default {
    name: "ScheduleSpotDetails",
    props: ['spot', 'spot_id'],
    $emits: ['spotFetched'],
    components: {
        ScheduleProfile,
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
            fetching_spot: false,
            showing_spots: this.spot || null,
            showing_spot: this.spot ? this.spot[0] : null,
            isAdditionalNoteOpen: false,
            cancelDialog: false,
            cancel_reason: '',
        }
    },
    watch: {
        spot_id() {
            this.showing_spots = this.spot || null;
            this.showing_spot = this.spot ? this.spot[0] : null;
        }
    },
    computed: {
        isMoreIconVisible() {
            return this.showing_spot.status != 'cancelled' && this.showing_spot.status != 'completed';
        },
        meetingDetails() {
            const guestName = `${this.showing_spot.first_name} ${this.showing_spot.last_name}`;
            const startTime = this.toCurrentTimezone(this.showing_spot.start_time, 'DD MMM YYYY, hh:mma');
            return `${this.showing_spot.slot_minutes} minutes meeting with ${guestName} @ ${startTime}`;
        },
        meetingTime() {
            const startTime = this.toCurrentTimezone(this.showing_spot.start_time, 'MMMM D, YYYY hh:mma');
            const endTime = this.toCurrentTimezone(this.showing_spot.end_time, 'MMMM D, YYYY hh:mma');
            return `${startTime} - ${endTime}`;
        },
    },
    methods: {
        fetchSchedule() {
            this.fetching_spot = true;
            this.$get(`schedules/${this.spot_id}`)
                .then(response => {
                    this.showing_spots = response.schedule;
                    this.showing_spot = response.schedule[0];
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