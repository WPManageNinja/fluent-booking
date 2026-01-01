<template>
    <div class="fcal_schedule_event_infos">
        <div class="fcal_schedule_event_infos_body">
            <div class="fcal_schedule_details_header">
                <template v-if="!isUnconfirmed">
                    <h1 class="fcal_header_title">
                        {{ $t('Invitees Information') }}
                    </h1>
                </template>
                <div v-else class="fcal_invitee_header">
                    <h1 class="fcal_header_title">
                        {{ $t('Invitees Information') }}
                    </h1>
                    <div class="fcal_event_actions">
                        <el-button v-loading="updating" :disabled="updating" @click="updateScheduleStatus('scheduled')" class="fcal_plain_btn">
                            <el-icon><Check/></el-icon> {{ $t('Confirm') }}
                        </el-button>
                        <el-button @click="rejectDialog = true" class="fcal_plain_btn">
                            <el-icon><Eleme/></el-icon> {{ $t('Reject') }}
                        </el-button>
                    </div>
                </div>
            </div>
            <div class="fcal_schedule_details_event">
                <div class="fcal_schedule_details_event_item">
                    <h3>{{ $t('Invitee Name') }}</h3>
                    <p>{{ booking.first_name }} {{ booking.last_name }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <editable-booking-data
                        v-if="isEmailEditable"
                        input_type="text"
                        data_key="email"
                        :input_label="$t('Invitee Email')"
                        :booking="booking">
                    </editable-booking-data>
                    <template v-else>
                        <h3>{{ $t('Invitee Email') }}</h3>
                        <p>{{ booking.email }}</p>
                    </template>
                </div>
                <div v-if="booking.additional_guests.length" class="fcal_schedule_details_event_item">
                    <h3>{{ $t('Additional Guests') }}</h3>
                    <div v-for="guest in booking.additional_guests" class="fcal_spot_details_value">
                        <p>{{ guest }}</p>
                    </div>
                </div>
                <div v-if="booking.message" class="fcal_schedule_details_event_item">
                    <h3>{{ $t('Message') }}</h3>
                    <p>{{ booking.message }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <h3>{{ $t('Invitee Timezone') }}</h3>
                    <p>{{ booking.person_time_zone }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <h3>{{ $t('Booked At') }}</h3>
                    <p>{{ toCurrentTimezone(booking.created_at, this.appVars.date_time_formatter) }}</p>
                </div>
                <div v-if="booking.ip_address" class="fcal_schedule_details_event_item">
                    <h3>{{ $t('IP Address') }}</h3>
                    <p>{{ booking.ip_address }}</p>
                </div>
                <div v-for="utm in getUtmData(booking)" class="fcal_schedule_details_event_item">
                    <h3>{{ utm.label }}</h3>
                    <p>{{ utm.value }}</p>
                </div>
                <template v-if="booking.custom_form_data" v-for="field in booking.custom_form_data">
                    <div v-if="field.value && field.value != 'undefined' && field.label != 'Location'" class="fcal_schedule_details_event_item">
                        <h3>{{ field.label }}</h3>
                        <div class="fcal_spot_details_value">
                            <p v-if="field.type == 'date'">{{ field.value }}</p>
                            <p v-else v-html="field.value"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <el-dialog
            v-model="rejectDialog"
            width="30%"
            :title="$t('Reject Booking')"
            class="fcal_modal">
            <div style="text-align: center;">
                <h3>{{ booking.calendar_event.title }}</h3>
                <p class="fcal_meeting_with">{{ $t('with') }} <b>{{ booking.first_name }} {{ booking.last_name }}</b></p>
                <p class="fcal_meeting_time">{{ meetingTime }}</p>
                <p>{{ $t('ScheduleBookingDetails/reject_event_desc') }}</p>
                <el-input type="textarea" v-model="rejectReason" :placeholder="$t('Reason for rejection')"></el-input>
                <el-checkbox v-if="isRefundable" true-label="yes" false-label="no" class="fcal_refund_checkbox" v-model="refundPayment">
                    {{ $t('Refund payment from stripe') }}
                </el-checkbox>
            </div>
            <template #footer>
              <span class="dialog-footer">
                <el-button
                    @click="rejectDialog = false"
                    class="fcal_plain_btn">
                    {{ $t("Don't Reject") }}
                </el-button>
                <el-button
                    v-loading="updating"
                    :disabled="updating"
                    class="fcal_primary_btn"
                    @click="updateScheduleStatus('rejected')">
                    {{ $t('Reject') }}
                </el-button>
              </span>
            </template>
        </el-dialog>
    </div>
</template>

<script>
import EditableBookingData from "./EditableBookingData";
import { Check, Eleme } from '@element-plus/icons-vue';
export default {
    name: "SingleInviteeInfo",
    props: ['booking'],
    data() {
        return {
            updating: false,
            rejectDialog: false,
            refundPayment: false,
            rejectReason: ''
        }
    },
    components: {
        EditableBookingData,
        Check,
        Eleme
    },
    computed: {
        isUnconfirmed() {
            const isConfRequired = this.booking.calendar_event?.settings?.requires_confirmation?.enabled;
            return this.booking.status === 'pending' && this.booking.payment_status != 'pending' && isConfRequired;
        },
        isRefundable() {
            return this.booking.payment_method == 'stripe' && this.booking.payment_status != 'refunded';
        },
        isEmailEditable() {
            return this.booking.status == 'scheduled';
        },
        meetingTime() {
            const startTime = this.toCurrentTimezone(this.booking.start_time, this.appVars.date_time_formatter);
            const endTime = this.toCurrentTimezone(this.booking.end_time, this.appVars.date_time_formatter);
            return `${startTime} - ${endTime}`;
        }
    },
    methods: {
        getUtmData(booking) {
            const utmData = [];
            const utmDataMap = {
                utm_source: this.$t('UTM Source'),
                utm_medium: this.$t('UTM Medium'),
                utm_campaign: this.$t('UTM Campaign'),
                utm_term: this.$t('UTM Term'),
                utm_content: this.$t('UTM Content')
            }
            Object.keys(utmDataMap).forEach(key => {
                if (booking[key]) {
                    let value = booking[key];
                    if (Array.isArray(value)) {
                        value = value.join(', ');
                    }
                    utmData.push({
                        label: utmDataMap[key],
                        value: value
                    });
                }
            });
            return utmData.length ? utmData : [];
        },
        updateScheduleStatus(newStatus) {
            this.updating = true;
            const data = {
                column: 'status',
                value: newStatus
            };
            if (newStatus == 'rejected') {
                data.reject_reason = this.rejectReason;
                data.refund_payment = this.refundPayment;
            }

            this.$put(`schedules/${this.booking.id}`, data)
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.booking.status = newStatus;
                    this.cancelDialog = false;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                    this.rejectDialog = false;
                });
        },
    },
    mounted() {
        if (this.isUnconfirmed) {
            if (this.$route.query.confirm_booking) {
                this.updateScheduleStatus('scheduled');
            } else if (this.$route.query.reject_booking) {
                this.rejectDialog = true;
            }
        }
    }
}
</script>
