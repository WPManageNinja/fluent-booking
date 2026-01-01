<template>
    <div class="fcal_schedule_details">
        <div class="fcal_schedule_details_content">
            <div v-if="showing_booking" class="fcal_schedule_event_infos">
                <div :class="'fcal_event_status_' + showing_booking.status" class="fcal_schedule_header_bar">
                    <div><span v-html="meetingDetails"></span> - {{ $t(ucFirst(showing_booking.status)) }}</div>
                    <el-dropdown v-if="hasWriteAccess" trigger="click" popper-class="fcal_select">
                        <span class="el-dropdown-link">
                            <el-icon><MoreFilled/></el-icon>
                        </span>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item
                                    v-if="isSingleGuestEvent && isBookingScheduled" @click="sendConfirmationEmail('guest')">
                                    <el-icon><Message/></el-icon>
                                    {{ $t('Send Confirmation Email') }}
                                </el-dropdown-item>
                                <el-dropdown-item 
                                    v-if="canMarkAsPaid" @click="bookingMarkAsPaid">
                                    <el-icon><Check/></el-icon>
                                    {{ $t('Mark As Paid') }}
                                </el-dropdown-item>
                                <el-dropdown-item
                                    v-if="canMarkAsCompleted" @click="updateScheduleStatus('completed')">
                                    <el-icon><Check/></el-icon>
                                    {{ $t('Mark As Completed') }}
                                </el-dropdown-item>
                                <el-dropdown-item
                                    v-if="canMakeNoShow"
                                    @click="updateScheduleStatus('no_show')">
                                    <el-icon><Hide/></el-icon>
                                    {{ $t('No Show') }}
                                </el-dropdown-item>
                                <el-dropdown-item
                                    v-if="isSingleGuestEvent && !isReservedBooking"
                                    @click="rebookAttendee">
                                    <el-icon><RefreshRight /></el-icon>
                                    {{ $t('Rebook') }}
                                </el-dropdown-item>
                                <el-dropdown-item 
                                    v-if="canReschedule"
                                    @click="rescheduleBooking">
                                    <el-icon><Refresh/></el-icon>
                                    {{ $t('Reschedule') }}
                                </el-dropdown-item>
                                <el-dropdown-item 
                                    v-if="canCancel" @click="cancelDialog = true">
                                    <el-icon><Close/></el-icon>
                                    {{ $t('Cancel') }}
                                </el-dropdown-item>
                                <el-dropdown-item 
                                    @click="deleteDialog = true">
                                    <el-icon><Delete/></el-icon>
                                    {{ $t('Delete') }}
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
                <SingleInviteeInfo
                    v-if="!isMultiGuestEvent && !isReservedBooking"
                    :booking="showing_booking"
                />
                <group-booking-guests
                    v-else-if="isMultiGuestEvent && !isReservedBooking"
                    :group_id="showing_booking.group_id"
                    @updateAdditionalInfo="updateAdditionalInfo"
                />
                <div class="fcal_schedule_event_infos fcal_schedule_event_infos_body">
                    <div class="fcal_schedule_details_header">
                        <h1 class="fcal_header_title">
                            {{ $t('Meeting Information') }}
                        </h1>
                    </div>
                    <div class="fcal_schedule_details_event">
                        <div v-if="!isReservedBooking" class="fcal_schedule_details_event_item">
                            <h3>{{ $t('Meeting Host') }}</h3>
                            <div v-if="hostProfiles" class="fcal_author_avatars">
                                <div v-for="author in hostProfiles" class="fcal_author">
                                    <img class="fcal_author_avatar" :src="author.avatar">
                                    <div class="fcal_author_tooltip">
                                        <span>{{ author.name }}</span>
                                    </div>
                                </div>
                            </div>
                            <p v-else>{{ showing_booking.author.name }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>{{ $t('Meeting Title') }}</h3>
                            <p>{{ showing_booking.calendar_event.title }}</p>
                        </div>
                        <div class="fcal_schedule_details_event_item">
                            <h3>{{ $t('Meeting Duration') }}</h3>
                            <p>{{ getMeetingDuration(showing_booking.slot_minutes) }}</p>
                        </div>
                        <div v-if="!isReservedBooking" class="fcal_schedule_details_event_item">
                            <h3>{{ $t('Location') }}</h3>
                            <div v-html="showing_booking.location"></div>
                        </div>
                        <div v-if="!isMultiGuestEvent && !isReservedBooking"
                            class="fcal_schedule_details_event_item">
                            <h3>{{ $t('Status') }}</h3>
                            <p>{{ $t(showing_booking.status) }}</p>
                        </div>
                        <div v-if="isReservedBooking"
                            class="fcal_schedule_details_event_item">
                            <h3>{{ $t('Expires') }}</h3>
                            <p>{{ getExpireTime }}</p>
                        </div>
                        <div v-if="showing_booking.source_url && isMultiGuestEvent"
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
                    <div v-if="isReservedBooking"
                        class="fcal_schedule_details_reserve_times">
                        <h3>{{ $t('Reserved Times') }}</h3>
                        <div v-for="(times, date) in reserveTimes" class="fcal_reserve_time">
                            <h4>{{ date }}</h4>
                            <p v-for="(time, indx) in times">
                                {{ time }}<span v-if="indx < times.length - 1">,</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div v-loading="loading_sidebar" v-if="showing_booking && isSingleGuestEvent">
                    <template v-if="main_body_contents && main_body_contents.length">
                        <div v-for="bodyMeta in main_body_contents" :key="bodyMeta.id"
                             class="fcal_schedule_event_infos_body">
                            <div class="fcal_schedule_details_header">
                                <h1 class="fcal_header_title">
                                    {{ bodyMeta.title }}
                                </h1>
                            </div>
                            <div>
                                <div v-html="bodyMeta.content" style="color: var(--fcal-secondary-text, #525866);"></div>
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
            class="fcal_modal">
            <div style="text-align: center;">
                <h3>{{ showing_booking.calendar_event.title }}</h3>
                <p class="fcal_meeting_with">{{ $t('with') }}
                    <b>{{ showing_booking.first_name }} {{ showing_booking.last_name }}</b>
                </p>
                <p class="fcal_meeting_time">{{ meetingTime }}</p>
                <p>{{ $t('ScheduleBookingDetails/cancel_event_desc') }}</p>
                <el-input type="textarea" v-model="cancel_reason" :placeholder="$t('Reason for cancellation')"></el-input>
                <el-checkbox v-if="isRefundable" true-label="yes" false-label="no" class="fcal_refund_checkbox" v-model="refund_payment">
                    {{ $t('Refund payment from stripe') }}
                </el-checkbox>
            </div>
            <template #footer>
              <span class="dialog-footer">
                <el-button
                    @click="cancelDialog = false"
                    class="fcal_plain_btn">
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
        <AddNewBookingModal
            v-if="isRebookOpen"
            :showModal="isRebookOpen"
            :calendarEventLists="calendarEventLists"
            :bookingData="showing_booking"
            @closeModal="isRebookOpen = false"
        />
    </div>
</template>

<script>
import { MoreFilled, Message, Refresh, RefreshRight, Close, Check, Hide } from '@element-plus/icons-vue';
import BookingActivities from "./_BookingActivities";
import GroupBookingGuests from './GroupBookingGuests';
import SingleInviteeInfo from './SingleInviteeInfo';
import EditableBookingData from "./EditableBookingData";
import PaymentLogs from "./PaymentLogs";
import AddNewBookingModal from "./_AddNewBookingModal";
import { each } from 'lodash';

export default {
    name: "ScheduleSpotDetails",
    props: ['booking', 'booking_id', 'calendarEventLists'],
    $emits: ['bookingFetched'],
    components: {
        PaymentLogs,
        BookingActivities,
        SingleInviteeInfo,
        GroupBookingGuests,
        EditableBookingData,
        AddNewBookingModal,
        Message,
        MoreFilled,
        Refresh,
        RefreshRight,
        Close,
        Check,
        Hide
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
            refund_payment: 'no',
            loading_sidebar: false,
            activities: [],
            sidebar_contents: [],
            payment_order: null,
            main_body_contents: [],
            durationLookup: [],
            isRebookOpen: false
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
        isBookingScheduled() {
            return this.showing_booking.status == 'scheduled';
        },
        isBookingCompleted() {
            return this.showing_booking.status == 'completed';
        },
        isBookingCancelled() {
            return this.showing_booking.status == 'cancelled';
        },
        isBookingRejected() {
            return this.showing_booking.status == 'rejected';
        },
        isReservedBooking() {
            return this.showing_booking.status == 'reserved';
        },
        isNotShowBooking() {
            return this.showing_booking.status == 'no_show';
        },
        canMakeNoShow() {
            return !this.isNotShowBooking && this.isBookingCompleted;
        },
        canMarkAsCompleted() {
            return !this.canMarkAsPaid && ['scheduled', 'approved', 'pending'].includes(this.showing_booking.status);
        },
        canMarkAsPaid() {
            const isPaidEvent = this.showing_booking.calendar_event.type == 'paid' && this.showing_booking.payment_status != 'paid';
            return isPaidEvent && !this.isBookingCancelled && !this.isBookingRejected;
        },
        canReschedule() {
            return this.isSingleGuestEvent && ['scheduled', 'approved', 'pending'].includes(this.showing_booking.status);
        },
        canCancel() {
            return this.isSingleGuestEvent && ['scheduled', 'approved', 'pending'].includes(this.showing_booking.status);
        },
        isGroup() {
            return this.showing_booking.event_type == 'group';
        },
        isGroupEvent() {
            return this.showing_booking.event_type == 'group_event';
        },
        isRefundable() {
            return this.showing_booking.payment_method == 'stripe' && this.showing_booking.payment_status != 'refunded';
        },
        isMultiGuestEvent() {
            return this.isGroup || this.isGroupEvent;
        },
        isSingleGuestEvent() {
            return !this.isMultiGuestEvent;
        },
        meetingTime() {
            const startTime = this.toCurrentTimezone(this.showing_booking.start_time, this.appVars.date_time_formatter);
            const endTime = this.toCurrentTimezone(this.showing_booking.end_time, this.appVars.date_time_formatter);
            return `${startTime} - ${endTime}`;
        },
        meetingDetails() {
            const startTime = this.toCurrentTimezone(this.showing_booking.start_time, this.appVars.date_time_formatter);
            if (this.booking.status == 'reserved') {
                return this.$t('Reserved slot of') + ' ' + this.showing_booking.calendar_event.title + ' @ ' + startTime;
            }
            if (this.isMultiGuestEvent) {
                const booked = this.showing_booking.booked_count;
                return `${booked} ${this.$t('guests with')} ${this.$t('you')} @ ${startTime}`;
            }
            return this.showing_booking.title + ' @ ' + startTime;
        },
        getMeetingDuration() {
            return (duration) => {
                return this.durationLookup[duration] || duration + ' ' + this.$t('Minutes');
            }
        },
        hostProfiles() {
            return this.showing_booking.host_profiles;
        },
        getExpireTime() {
            return this.toCurrentTimezone(this.showing_booking.expires, this.appVars.date_time_formatter);
        },
        reserveTimes() {
            const reserveTimes = {};
            each(this.showing_booking.reserved_times, (reserveTime) => {
                const localTime = this.toCurrentTimezone(reserveTime);
                const date = this.toDateFormat(localTime, 'D MMM YYYY');
                const time = this.toDateFormat(localTime, 'hh:mma');
                reserveTimes[date] = reserveTimes[date] || [];
                reserveTimes[date].push(time);
            });
            return reserveTimes;
        }
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

                    this.updateDurationLookup();
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
        updateScheduleStatus(new_status, column = 'status') {
            this.updating = true;
            const data = {
                column: column,
                value: new_status
            };
            if (new_status == 'cancelled') {
                data.cancel_reason = this.cancel_reason;
                data.refund_payment = this.refund_payment;
            }

            this.$put(`schedules/${this.showing_booking.id}`, data)
                .then(response => {
                    this.$handleSuccess(response.message);
                    if (column == 'status') {
                        this.showing_booking.status = new_status;
                        this.showing_booking.happening_status = '';
                    }
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
        hasWriteAccess() {
            const allBookingAccess = this.hasAccess(['manage_all_data', 'manage_all_bookings']);
            const myCalendarBooking = this.showing_booking.calendar_id == this.appVars.me.calendar_id;
            const myBooking = this.showing_booking.host_user_id == this.appVars.me.id;
            return myBooking || myCalendarBooking || allBookingAccess;
        },
        bookingMarkAsPaid() {
            if (this.showing_booking.source == 'admin' || this.showing_booking.payment_method == 'offline') {
                this.updateScheduleStatus('paid', 'payment_status');
            } else {
                this.updateScheduleStatus('scheduled');
            }
        },
        handleDataUpdated(data) {
            if (this.booking) {
                this.booking[data.key] = data.value;
            }
        },
        updateAdditionalInfo(activities, sidebarContents) {
            this.activities = activities;
            this.sidebar_contents = sidebarContents;
        },
        updateDurationLookup() {
            if (this.showing_booking?.calendar_event?.settings?.multi_duration?.enabled) {
                this.durationLookup = this.appVars.multi_duration_lookup;
            } else {
                this.durationLookup = this.appVars.duration_lookup;
            }
        },
        rebookAttendee() {
            this.isRebookOpen = true;
        },
        sendConfirmationEmail(emailTo) {
            this.updating = true;
            this.loading_sidebar = true;
            this.$post(`schedules/${this.showing_booking.id}/send-confirmation-email`, {
                    email_to: emailTo
                })
                .then(response => {
                    this.$handleSuccess(response.message);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                    this.getAdditionalData();
                });
        }
    },
    mounted() {
        if (!this.booking) {
            this.fetchBooking();
        } else {
            this.getAdditionalData();
        }
        this.updateDurationLookup();
    }
}
</script>
