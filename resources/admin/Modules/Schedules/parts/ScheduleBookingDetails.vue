<template>
    <div class="fcal_schedule_details">
        <div class="fcal_schedule_details_content">
            <div v-if="showing_booking" class="fcal_schedule_event_infos">
                <div :class="'fcal_event_status_' + showing_booking.status" class="fcal_schedule_header_bar">
                    {{ meetingDetails }} - {{ $t(ucFirst(showing_booking.status)) }}

                    <el-dropdown v-if="isMoreIconVisible" trigger="click" popper-class="fcal_select">
                        <span class="el-dropdown-link">
                            <el-icon><MoreFilled/></el-icon>
                        </span>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item @click="updateScheduleStatus('completed')">
                                    <el-icon>
                                        <Check/>
                                    </el-icon>
                                    {{ $t('Mark As Completed') }}
                                </el-dropdown-item>
                                <el-dropdown-item v-if="showing_booking.status!='no_show'"
                                                  @click="updateScheduleStatus('no_show')">
                                    <el-icon>
                                        <Hide/>
                                    </el-icon>
                                    {{ $t('No Show') }}
                                </el-dropdown-item>
                                <el-dropdown-item @click="rescheduleBooking">
                                    <el-icon>
                                        <Refresh/>
                                    </el-icon>
                                    {{ $t('Reschedule') }}
                                </el-dropdown-item>
                                <el-dropdown-item @click="cancelDialog = true">
                                    <el-icon>
                                        <Close/>
                                    </el-icon>
                                    {{ $t('Cancel') }}
                                </el-dropdown-item>
                                <el-dropdown-item @click="deleteDialog = true">
                                    <el-icon>
                                        <Delete/>
                                    </el-icon>
                                    {{ $t('Delete') }}
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
                            {{ $t('Meeting Information') }}
                        </h1>
                    </div>

                    <div class="fcal_schedule_details_event">
                        <div class="fcal_schedule_details_event_item">
                            <h3>{{ $t('Meeting Host') }}</h3>
                            <p>{{ showing_booking.author.name }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>{{ $t('Meeting Title') }}</h3>
                            <p>{{ showing_booking.calendar_event.title }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>{{ $t('Meeting Duration') }}</h3>
                            <p>{{ showing_booking.slot_minutes }} {{ $t('minutes') }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>{{ $t('Location') }}</h3>
                            <div v-html="showing_booking.location"></div>
                        </div>
                        <div
                            v-if="showing_booking.event_type != 'group'"
                            class="fcal_schedule_details_event_item">
                            <h3>{{ $t('Status') }}</h3>
                            <p>{{ $t(showing_booking.status) }}</p>
                        </div>
                        <div v-if="showing_booking.source_url && showing_booking.event_type != 'group'"
                             class="fcal_schedule_details_event_item">
                            <h3>{{ $t('Booking URL') }}</h3>
                            <div class="fcal_spot_details_value">
                                <a target="_blank" rel="nofollow"
                                   :href="showing_booking.source_url">{{ showing_booking.source_url }}</a>
                            </div>
                        </div>
                    </div>
                    <div v-if="showing_booking.event_type == 'single'"
                         class="fcal_schedule_details_event_additional fcal_schedule_details_event_item">
                        <editable-booking-data
                            input_type="textarea"
                            :input_label="$t('Internal Note')"
                            data_key="internal_note"
                            @dataUpdated="handleDataUpdated"
                            :booking="showing_booking">
                        </editable-booking-data>
                    </div>
                </div>

                <div v-loading="loading_sidebar" v-if="showing_booking && showing_booking.event_type == 'single'">
                    <template v-if="main_body_contents && main_body_contents.length">
                        <div v-for="bodyMeta in main_body_contents" :key="bodyMeta.id"
                             class="fcal_schedule_event_infos_body">
                            <div class="fcal_schedule_details_header">
                                <h1 class="fcal_header_title">
                                    {{ bodyMeta.title }}
                                </h1>
                            </div>
                            <div>
                                <div v-html="bodyMeta.content"></div>
                            </div>
                        </div>
                    </template>
                    <PaymentLogs v-if="payment_order" :payment_order="payment_order" :booking="showing_booking"/>
                </div>
            </div>
        </div>
        <div v-loading="loading_sidebar" class="fcal_booking_activities">
            <template v-if="showing_booking">
                <BookingActivities :activities="activities"/>
                <div v-if="sidebar_contents && sidebar_contents.length">
                    <div v-for="sideItem in sidebar_contents" :key="sideItem.id" class="fcal_schedule_profile_box">
                        <div class="fcal_schedule_profile_header">
                            <h1>{{ sideItem.title }}</h1>
                        </div>
                        <div class="fcal_schedule_profile_body" v-html="sideItem.content"></div>
                    </div>
                </div>
            </template>
            <el-skeleton :animated="true" :rows="10" v-if="loading_sidebar" />
        </div>
        <div style="background: white; padding: 20px;" v-if="fetching">
            <el-skeleton :rows="10" :animated="true"/>
        </div>
        <el-dialog
            v-model="cancelDialog"
            width="30%"
            :title="$t('Cancel Meeting')"
            class="fcal_modal"
        >
            <div style="text-align: center;">
                <h3>{{ showing_booking.calendar_event.title }}</h3>
                <p class="fcal_meeting_with">{{ $t('with') }} <b>{{ showing_booking.first_name }} {{
                        showing_booking.last_name
                    }}</b></p>
                <p class="fcal_meeting_time">{{ meetingTime }}</p>
                <p>{{ $t('ScheduleBookingDetails/cancel_event_desc') }}</p>
                <el-input type="textarea" v-model="cancel_reason"
                          :placeholder="$t('Reason for cancellation')"></el-input>
            </div>
            <template #footer>
              <span class="dialog-footer">
                <el-button
                    @click="cancelDialog = false"
                    class="fcal_plain_btn"
                >
                    {{ $t("No, Don't cancel") }}
                </el-button>
                <el-button
                    v-loading="updating"
                    :disabled="updating"
                    class="fcal_primary_btn"
                    @click="cancelEvent()">
                  {{ $t('Yes, Cancel') }}
                </el-button>
              </span>
            </template>
        </el-dialog>
        <el-dialog
            v-model="deleteDialog"
            width="30%"
            :title="$t('Delete Meeting')"
            class="fcal_modal"
        >
            <div style="text-align: center;">
                <h3>{{ showing_booking.calendar_event.title }}</h3>
                <p class="fcal_meeting_with">{{ $t('with') }} <b>{{ showing_booking.first_name }} {{
                        showing_booking.last_name
                    }}</b></p>
                <p class="fcal_meeting_time">{{ meetingTime }}</p>
                <p>{{ $t('ScheduleBookingDetails/delete_booking') }}</p>
            </div>
            <template #footer>
              <span class="dialog-footer">
                <el-button
                    @click="deleteDialog = false"
                    class="fcal_plain_btn"
                >
                    {{ $t("No, Don't delete") }}
                </el-button>
                <el-button
                    v-loading="updating"
                    :disabled="updating"
                    class="fcal_primary_btn"
                    @click="deleteEvent()">
                  {{ $t('Yes, Delete') }}
                </el-button>
              </span>
            </template>
        </el-dialog>
    </div>
</template>

<script type="text/babel">
import {Back, MoreFilled, Refresh, Close, Delete, EditPen, Check, Hide} from '@element-plus/icons-vue';
import BookingActivities from "./_BookingActivities";
import GroupBookingGuests from './GroupBookingGuests';
import SingleInviteeInfo from './SingleInviteeInfo';
import EditableBookingData from "./EditableBookingData";
import PaymentLogs from "./PaymentLogs";

export default {
    name: "ScheduleSpotDetails",
    props: ['booking', 'booking_id'],
    $emits: ['bookingFetched'],
    components: {
        PaymentLogs,
        BookingActivities,
        SingleInviteeInfo,
        GroupBookingGuests,
        EditableBookingData,
        Back,
        MoreFilled,
        Refresh,
        Close,
        EditPen,
        Check,
        Hide,
        Delete
    },
    data() {
        return {
            loading: false,
            updating: false,
            fetching: false,
            showing_booking: this.booking,
            cancelDialog: false,
            deleteDialog: false,
            cancel_reason: '',

            loading_sidebar: false,
            activities: [],
            sidebar_contents: [],
            payment_order: null,
            main_body_contents: []
        }
    },
    watch: {
        booking_id() {
            this.showing_booking = null;
            if (this.booking) {
                this.getAdditionalData();
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
            return this.hasAccess('manage_all_bookings') && this.showing_booking.status != 'cancelled' && this.showing_booking.status != 'completed';
        },
        isGroupEvent() {
            return this.showing_booking.event_type == 'group';
        },
        meetingDetails() {
            const day   = this.toCurrentTimezone(this.showing_booking.start_time, 'DD');
            const month = this.toCurrentTimezone(this.showing_booking.start_time, 'MMM').toLowerCase();
            const hour  = this.toCurrentTimezone(this.showing_booking.start_time, 'hh').toLowerCase();
            const min   = this.toCurrentTimezone(this.showing_booking.start_time, 'mm').toLowerCase();
            const a     = this.toCurrentTimezone(this.showing_booking.start_time, 'a').toLowerCase();

            const guestName = `${this.showing_booking.first_name} ${this.showing_booking.last_name}`;
            const startTime = this.$t(day) + ' ' + this.$t(month) + ' ' + this.toCurrentTimezone(this.showing_booking.start_time, 'YYYY') +', '+ this.$t(hour) + ':' + this.$t(min) + this.$t(a);
            return `${this.$t(this.showing_booking.slot_minutes)} ${this.$t('minutes meeting with')} ${guestName} @ ${startTime}`;
        },
        meetingTime() {
            const startMonth = this.$t(this.toCurrentTimezone(this.showing_booking.start_time, 'MMMM'));
            const startDay   = this.$t(this.toCurrentTimezone(this.showing_booking.start_time, 'D'));
            const startYear  = this.$t(this.toCurrentTimezone(this.showing_booking.start_time, 'YYYY'));
            const startHour  = this.$t(this.toCurrentTimezone(this.showing_booking.start_time, 'hh'));
            const startMin   = this.$t(this.toCurrentTimezone(this.showing_booking.start_time, 'mm'));
            const startA     = this.$t(this.toCurrentTimezone(this.showing_booking.start_time, 'a'));

            const endMonth = this.$t(this.toCurrentTimezone(this.showing_booking.end_time, 'MMMM'));
            const endDay   = this.$t(this.toCurrentTimezone(this.showing_booking.end_time, 'D'));
            const endYear  = this.$t(this.toCurrentTimezone(this.showing_booking.end_time, 'YYYY'));
            const endHour  = this.$t(this.toCurrentTimezone(this.showing_booking.end_time, 'hh'));
            const endMin   = this.$t(this.toCurrentTimezone(this.showing_booking.end_time, 'mm'));
            const endA     = this.$t(this.toCurrentTimezone(this.showing_booking.end_time, 'a'));

            const startTime = startMonth + ' ' + startDay + ', ' + startYear + ' ' + startHour + ':' + startMin + startA;
            const endTime   = endMonth + ' ' + endDay + ', ' + endYear + ' ' + endHour + ':' + endMin + endA;
            return `${startTime} - ${endTime}`;
        },
    },
    methods: {
        fetchBooking() {
            this.fetching = true;
            this.loading_sidebar = true;
            this.$get(`schedules/${this.booking_id}`, {
                with: ['all_data']
            })
                .then(response => {
                    this.showing_booking = response.schedule;

                    this.activities = response.activities;
                    this.sidebar_contents = response.sidebar_contents;
                    this.payment_order = response.payment_order;
                    this.main_body_contents = response.main_body_contents;

                    this.$emit('bookingFetched', response.schedule);
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.fetching = false;
                    this.loading_sidebar = false;
                });
        },
        getAdditionalData() {
            this.loading_sidebar = true;
            this.$get(`schedules/${this.booking_id}/meta-info`)
                .then(response => {
                    this.activities = response.activities;
                    this.sidebar_contents = response.sidebar_contents;
                    this.payment_order = response.payment_order;
                    this.main_body_contents = response.main_body_contents;
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading_sidebar = false;
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
        deleteEvent() {
            this.$del('schedules/' + this.showing_booking.id)
                .then(response => {
                    this.$handleSuccess(response);
                    this.$emit('bookingFetched', null);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                    this.deleteDialog = false;
                });
        },
        rescheduleBooking() {
            window.open(this.showing_booking.reschedule_url, '_blank');
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
        this.getAdditionalData();
    }
}
</script>
