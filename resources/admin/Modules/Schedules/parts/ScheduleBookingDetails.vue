<template>
    <div class="fcal_schedule_details">
        <div class="fcal_schedule_details_content">
            <div v-if="showing_booking" class="fcal_schedule_event_infos">
                <div class="fcal_schedule_header_bar">
                    {{ meetingDetails }}
                    <el-dropdown v-if="isMoreIconVisible" trigger="click" popper-class="fcal_select">
                        <span class="el-dropdown-link">
                            <el-icon><MoreFilled/></el-icon>
                        </span>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item @click="cancelDialog = true">
                                    <el-icon>
                                        <Close/>
                                    </el-icon>
                                    Cancel
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
                <SingleInviteeInfo v-if="showing_booking.event_type == 'single'" :booking="showing_booking"/>
                <group-booking-guests v-else-if="showing_booking.event_type == 'group'"
                                      :group_id="showing_booking.group_id"/>
                <div class="fcal_schedule_event_infos fcal_schedule_event_infos_body">
                    <div class="fcal_schedule_details_header">
                        <h1 class="fcal_header_title">
                            Meeting Information
                        </h1>
                    </div>

                    <div class="fcal_schedule_details_event">
                        <div class="fcal_schedule_details_event_item">
                            <h3>Meeting Host</h3>
                            <p>{{ showing_booking.author.name }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>Meeting Title</h3>
                            <p>{{ showing_booking.slot.title }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>Meeting Duration</h3>
                            <p>{{ showing_booking.slot_minutes }} minutes</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>Location</h3>
                            <div v-html="showing_booking.location"></div>
                        </div>
                        <div
                            v-if="showing_booking.event_type != 'group'"
                            class="fcal_schedule_details_event_item">
                            <h3>Status</h3>
                            <p>{{ showing_booking.status }}</p>
                        </div>
                        <div v-if="showing_booking.source_url && showing_booking.event_type != 'group'" class="fcal_schedule_details_event_item">
                            <h3>Booking URL</h3>
                            <div class="fcal_spot_details_value">
                                <a target="_blank" rel="nofollow"
                                   :href="showing_booking.source_url">{{ showing_booking.source_url }}</a>
                            </div>
                        </div>
                        <div v-if="showing_booking.source != 'web'" class="fcal_schedule_details_event_item">
                            <h3>Booked From</h3>
                            <div class="fcal_spot_details_value"
                                 v-html="showing_booking.source">
                            </div>
                        </div>
                    </div>
                    <div v-if="showing_booking.event_type == 'single'"
                         class="fcal_schedule_details_event_additional fcal_schedule_details_event_item">
                        <editable-booking-data
                            input_type="textarea"
                            input_label="Internal Note"
                            data_key="internal_note"
                            @dataUpdated="handleDataUpdated"
                            :booking="showing_booking">
                        </editable-booking-data>
                    </div>
                </div>
                <SourceDetailsSection v-if="showing_booking.sourceDetails" :booking="showing_booking"/>

                <PaymentLogs
                    v-if="showing_booking.event_type == 'single' && showing_booking.order_info"
                    :booking="showing_booking" />
            </div>
        </div>
        <div v-if="showing_booking" class="fcal_booking_activities">
            <BookingActivities :booking_id="showing_booking.id"/>
            <FluentCrmProfile :crm_email="showing_booking.email"/>
        </div>
        <el-dialog
            v-model="cancelDialog"
            width="30%"
            title="Cancel Meeting"
            class="fcal_modal"
        >
            <div style="text-align: center;">
                <h3>{{ showing_booking.slot.title }}</h3>
                <p class="fcal_meeting_with">with <b>{{ showing_booking.first_name }} {{
                        showing_booking.last_name
                    }}</b></p>
                <p class="fcal_meeting_time">{{ meetingTime }}</p>
                <p>Please confirm that you would like to cancel this event. A cancellation email will also go out to the
                    invitee</p>
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

<script type="text/babel">
import {Back, MoreFilled, Refresh, Close, EditPen} from '@element-plus/icons-vue';
import BookingActivities from "./_BookingActivities";
import FluentCrmProfile from "./FluentCrmProfile";
import GroupBookingGuests from './GroupBookingGuests';
import SingleInviteeInfo from './SingleInviteeInfo';
import EditableBookingData from "./EditableBookingData";
import SourceDetailsSection from './SourceDetailsSection';
import PaymentLogs from "./PaymentLogs";

export default {
    name: "ScheduleSpotDetails",
    props: ['booking', 'booking_id'],
    $emits: ['bookingFetched'],
    components: {
        PaymentLogs,
        FluentCrmProfile,
        BookingActivities,
        SingleInviteeInfo,
        GroupBookingGuests,
        EditableBookingData,
        SourceDetailsSection,
        Back,
        MoreFilled,
        Refresh,
        Close,
        EditPen,
    },
    data() {
        return {
            loading: false,
            updating: false,
            fetching: false,
            showing_booking: this.booking,
            cancelDialog: false,
            cancel_reason: '',
        }
    },
    watch: {
        booking_id() {
            this.showing_booking = null;
            if (this.booking) {
                this.$nextTick(() => {
                    this.showing_booking = this.booking;
                });
            } else {
                this.fetchBooking();
            }
        }
    },
    computed: {
        isMoreIconVisible() {
            return this.showing_booking.status != 'cancelled' && this.showing_booking.status != 'completed';
        },
        isGroupEvent() {
            return this.showing_booking.event_type == 'group';
        },
        meetingDetails() {
            const guestName = `${this.showing_booking.first_name} ${this.showing_booking.last_name}`;
            const startTime = this.toCurrentTimezone(this.showing_booking.start_time, 'DD MMM YYYY, hh:mma');
            return `${this.showing_booking.slot_minutes} minutes meeting with ${guestName} @ ${startTime}`;
        },
        meetingTime() {
            const startTime = this.toCurrentTimezone(this.showing_booking.start_time, 'MMMM D, YYYY hh:mma');
            const endTime = this.toCurrentTimezone(this.showing_booking.end_time, 'MMMM D, YYYY hh:mma');
            return `${startTime} - ${endTime}`;
        },
    },
    methods: {
        fetchBooking() {
            this.fetching = true;
            this.$get(`schedules/${this.booking_id}`)
                .then(response => {
                    this.showing_booking = response.schedule;
                    this.$emit('bookingFetched', response.schedule);
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.fetching = false;
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

            this.$put(`schedules/${this.showing_booking.id}`, data)
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.showing_booking.status = new_status;
                    this.showing_booking.happening_status = '';
                    this.cancelDialog = false;
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
            if (this.booking) {
                this.booking[data.key] = data.value;
            }
        }
    },
    mounted() {
        if (!this.booking) {
            this.fetchBooking();
        }
    }
}
</script>
