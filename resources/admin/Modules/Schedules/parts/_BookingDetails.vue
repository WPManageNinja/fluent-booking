<template>
    <div class="booking_details_wrap">
        <div class="booking_details_header">
            <el-icon class="top_right_icon" @click="openFullScreen(schedule.id)">
                <FullScreen />
            </el-icon>
            <el-dropdown trigger="click" popper-class="fcal_select">
                <el-icon class="more_icon">
                    <More />
                </el-icon>
                <template #dropdown>
                    <el-dropdown-menu>
                        <el-dropdown-item
                            @click="openFullScreen(schedule.id)">
                            <el-icon><View/></el-icon>
                            {{ $t('View Booking') }}
                        </el-dropdown-item>
                        <el-dropdown-item
                            v-if="canMarkAsPaid(schedule)" @click="bookingMarkAsPaid(schedule)">
                            <el-icon><Check/></el-icon>
                            {{ $t('Mark As Paid') }}
                        </el-dropdown-item>
                        <el-dropdown-item
                            v-if="canMarkAsCompleted(schedule)" @click="updateScheduleStatus('completed')">
                            <el-icon><Check/></el-icon>
                            {{ $t('Mark As Completed') }}
                        </el-dropdown-item>
                        <el-dropdown-item
                            v-if="canMakeNoShow(schedule)"
                            @click="updateScheduleStatus('no_show')">
                            <el-icon><Hide/></el-icon>
                            {{ $t('No Show') }}
                        </el-dropdown-item>
                        <el-dropdown-item
                            v-if="canCancelOrReschedule(schedule)"
                            @click="rescheduleBooking(schedule)">
                            <el-icon><Refresh/></el-icon>
                            {{ $t('Reschedule') }}
                        </el-dropdown-item>
                        <el-dropdown-item
                            v-if="canCancelOrReschedule(schedule)" @click="cancelDialog = true">
                            <el-icon><Close/></el-icon>
                            {{ $t('Cancel') }}
                        </el-dropdown-item>
                        <el-dropdown-item
                            v-if="!isStatus(schedule, 'completed')" @click="confirmDeleteBooking">
                            <el-icon><Delete/></el-icon>
                            {{ $t('Delete') }}
                        </el-dropdown-item>
                    </el-dropdown-menu>
                </template>
            </el-dropdown>
            <el-icon class="close_icon" @click="closeBookingDetails">
                <Close />
            </el-icon>
        </div>
        <div class="booking_details_content">
            <div class="booking_item">
                <div class="left_side">
                    <span class="booking_color" :style="{ background: schedule.calendar_event?.color_schema }"></span>
                </div>
                <div class="right_side">
                    <h3 class="booking_heading" v-html="bookingTitle"></h3>
                    <div class="booking_time" v-html="formattedTimeRange"></div>
                </div>
            </div>
            <div class="booking_item">
                <div class="left_side">
                    <el-icon><Location /></el-icon>
                </div>
                <div class="right_side">
                    <div class="booking_location" v-html="schedule.location"></div>
                </div>
            </div>
            <div class="booking_item">
                <div class="left_side">
                    <el-icon><User /></el-icon>
                </div>
                <div class="right_side">
                    <div v-if="!isMultiGuestBooking(schedule.event_type)">1 {{ $t('Guest') }}</div>
                    <div v-else>{{ schedule.booked_count }} {{ $t('Guests') }}</div>
                </div>
            </div>
            <div class="booking_item booking_details">
                <div class="left_side">
                    <el-icon><Operation /></el-icon>
                </div>
                <div class="right_side">
                    <div v-html="schedule.details"></div>
                </div>
            </div>
            <div class="booking_item">
                <div class="left_side">
                    <el-icon><Clock /></el-icon>
                </div>
                <div class="right_side">
                    <div> {{ getMeetingDuration(schedule.slot_minutes) }}</div>
                </div>
            </div>
        </div>
        <el-dialog
            v-model="cancelDialog"
            width="30%"
            :title="$t('Cancel Meeting')"
            class="fcal_modal">
            <div style="text-align: center;">
                <h3>{{ schedule.calendar_event.title }}</h3>
                <p class="fcal_meeting_with">{{ $t('with') }} <b>{{ schedule.first_name }} {{schedule.last_name}}</b></p>
                <p class="fcal_meeting_time">{{ formattedTimeRange }}</p>
                <p>{{ $t('ScheduleBookingDetails/cancel_event_desc') }}</p>
                <el-input type="textarea" v-model="cancel_reason" :placeholder="$t('Reason for cancellation')"></el-input>
                <el-checkbox v-if="isRefundable(schedule)" true-label="yes" false-label="no" class="fcal_refund_checkbox" v-model="refund_payment">
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
                    @click="updateScheduleStatus('cancelled')">
                    {{ $t('Yes, Cancel') }}
                </el-button>
              </span>
            </template>
        </el-dialog>
    </div>
</template>

<script>
import { FullScreen, Delete, More, Close, Location, User, Operation, Clock, Check, Hide, Refresh, View } from '@element-plus/icons-vue';

export default {
    name: 'BookingDetails',
    props: ['schedule'],
    components: {
        FullScreen,
        Delete,
        Close,
        More,
        User,
        Location,
        Operation,
        Clock,
        Check,
        Hide,
        Refresh,
        View
    },
    data() {
        return {
            updating: false,
            cancel_reason: '',
            cancelDialog: false,
            refund_payment: 'no',
            timeFormat: this.appVars.time_format
        }
    },
    computed: {
        formattedTimeRange() {
            const formatStartTime = this.toCurrentTimezone(this.schedule.start_time, this.timeFormat);
            const formatEndTime = this.toCurrentTimezone(this.schedule.end_time, this.timeFormat);
            const formatDate = this.toCurrentTimezone(this.schedule.start_time, 'dddd, MMM D');
            return `${formatDate} · ${formatStartTime} - ${formatEndTime}`;
        },
        bookingTitle() {
            const eventTitle = this.schedule.calendar_event.title;
            if (this.schedule.status == 'reserved') {
                return this.$t('Reserved slot of') + ' ' + eventTitle;
            }
            if (this.isMultiGuestBooking(this.schedule.event_type)) {
                const booked = this.schedule.booked_count;
                return `${eventTitle} ${this.$t('with you and')} ${booked} ${this.$t('other guests')}`;
            }
            return this.schedule.title;
        }
    },
    methods: {
        isMultiGuestBooking(eventType) {
            return ['group', 'group_event'].includes(eventType);
        },
        rescheduleBooking(schedule) {
            window.open(schedule.reschedule_url, '_blank');
        },
        closeBookingDetails() {
            this.$emit('close');
        },
        isStatus(schedule, status) {
            return schedule.status == status;
        },
        isRefundable(schedule) {
            return schedule.payment_method == 'stripe' && schedule.payment_status != 'refunded';
        },
        canCancelOrReschedule(schedule) {
            return !this.isMultiGuestBooking(schedule.event_type) && ['scheduled', 'approved', 'pending'].includes(schedule.status);
        },
        canMarkAsPaid(schedule) {
            const isPaidEvent = schedule.calendar_event.type == 'paid' && schedule.payment_status != 'paid';
            return isPaidEvent && !this.isStatus(schedule, 'cancelled') && !this.isStatus(schedule, 'rejected');
        },
        canMarkAsCompleted(schedule) {
            return !this.canMarkAsPaid(schedule) && ['scheduled', 'approved', 'pending'].includes(schedule.status);
        },
        canMakeNoShow(schedule) {
            return !this.isStatus(schedule, 'no_show') && this.isStatus(schedule, 'completed');
        },
        bookingMarkAsPaid(schedule) {
            if (schedule.source == 'admin' || schedule.payment_method == 'offline') {
                this.updateScheduleStatus('paid', 'payment_status');
            } else {
                this.updateScheduleStatus('scheduled');
            }
        },
        getMeetingDuration(duration) {
            let durationLookup = this.appVars.duration_lookup;
            if (this.schedule?.calendar_event?.settings?.multi_duration?.enabled) {
                durationLookup = this.appVars.multi_duration_lookup;
            }
            return durationLookup[duration] || duration + ' ' + this.$t('Minutes');
        },
        confirmDeleteBooking() {
            this.$confirm('Are you sure you want to delete this booking?', 'Delete Booking', {
                confirmButtonText: 'Confirm',
                cancelButtonText: 'Cancel',
                type: 'warning'
            }).then(() => {
                this.deleteBooking();
            });
        },
        deleteBooking() {
            this.$del('schedules/' + this.schedule.id)
                .then(response => {
                    this.$handleSuccess(response);
                    this.$emit('update');
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.closeBookingDetails();
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

            this.$put(`schedules/${this.schedule.id}`, data)
                .then(response => {
                    this.$handleSuccess(response.message);
                    if (column == 'status') {
                        this.schedule.status = new_status;
                        this.schedule.happening_status = '';
                    }
                    this.cancelDialog = false;
                    this.closeBookingDetails();
                    this.$emit('update');
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                });
        },
        openFullScreen(bookingId) {
            this.$router.push({
                name: 'scheduled_events',
                query: { ...this.$route.query, booking_id: bookingId }
            });
            setTimeout(() => {
                window.location.reload();
            }, 100);
        }
    }
};
</script>