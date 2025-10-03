<template>
    <div class="fcal_schedule_event_infos">
        <div class="fcal_schedule_event_infos_body">
            <div class="fcal_schedule_group_header">
                <h1 class="fcal_header_title">
                    {{ $t('Event Guests') }}
                </h1>

                <div class="fcal_schedule_details_header_action">
                    <el-input
                        v-model="search"
                        @keyup.enter="fetchGuests"
                        clearable
                        :placeholder="$t('Search Host')">
                        <template #append>
                            <el-button @click="fetchGuests">
                                <el-icon><Search /></el-icon>
                            </el-button>
                        </template>
                    </el-input>
                    <el-tooltip
                        class="fcal_tooltip_box"
                        effect="dark"
                        :content="$t('Export Hosts')"
                        placement="top-start"
                    >
                        <el-button class="fcal_export_btn" @click="exportHosts(group_id)"><el-icon><Download /></el-icon></el-button>
                    </el-tooltip>
                </div>
            </div>
            <el-skeleton v-if="!app_loaded" :rows="5" :animated="true" :loading="loading" />

            <el-table
                v-else
                stripe
                :data="attendees"
                :empty-text="$t('No Host Found')"
                @expand-change="handleExpand"
            >
                <el-table-column type="expand">
                    <template #default="scope">
                        <div class="fcal_group_booking_guests_wrap">
                            <div class="fcal_schedule_details_event">
                                <div v-if="scope.row.message" class="fcal_schedule_details_event_item">
                                    <h3>{{ $t('Message') }}</h3>
                                    <div class="fcal_spot_details_value">
                                        {{ scope.row.message }}
                                    </div>
                                </div>
                                <div class="fcal_schedule_details_event_item">
                                    <h3>{{ $t('Timezone') }}</h3>
                                    <div class="fcal_spot_details_value">
                                        {{ scope.row.person_time_zone }}
                                    </div>
                                </div>
                                <div class="fcal_schedule_details_event_item">
                                    <h3>{{ $t('Booking URL') }}</h3>
                                    <div class="fcal_spot_details_value">
                                        <a :href="scope.row.source_url" target="_blank">{{ scope.row.source_url }}</a>
                                    </div>
                                </div>
                                <div class="fcal_schedule_details_event_item">
                                    <editable-booking-data
                                        input_type="textarea"
                                        :input_label="$t('Internal Note')"
                                        data_key="internal_note"
                                        @dataUpdated="handleDataUpdated"
                                        :booking="scope.row">
                                    </editable-booking-data>
                                </div>
                            </div>

                            <div class="fcal_schedule_details_event">
                                <div
                                    v-if="scope.row.custom_form_data"
                                    v-for="field in scope.row.custom_form_data"
                                    class="fcal_schedule_details_event_item"
                                >
                                    <h3>{{ field.label }}</h3>
                                    <div class="fcal_spot_details_value">
                                        <p>{{ field.value }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="sidebar_loading"><el-skeleton :rows="5" :animated="true" /></div>
                            <div v-else-if="bookingId == scope.row.id">
                                <div class="fcal_schedule_event_infos">
                                    <div v-if="main_body_contents && main_body_contents.length">
                                        <div v-for="bodyMeta in main_body_contents" :key="bodyMeta.id" class="fcal_schedule_event_infos_body">
                                            <div class="fcal_schedule_details_header">
                                                <h1 class="fcal_header_title">
                                                    {{ bodyMeta.title }}
                                                </h1>
                                            </div>
                                            <div>
                                                <div v-html="bodyMeta.content"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <PaymentLogs v-if="payment_order" :payment_order="payment_order" :booking="scope.row" />
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column :label="$t('Name')" width="150">
                    <template #default="scope">
                        {{ scope.row.first_name }} {{ scope.row.last_name }}
                    </template>
                </el-table-column>
                <el-table-column :label="$t('Email')" width="200">
                    <template #default="scope">
                        {{ scope.row.email }}
                    </template>
                </el-table-column>
                <el-table-column :label="$t('Status')" width="120">
                    <template #default="scope">
                        {{ $t(scope.row.status) }}
                    </template>
                </el-table-column>
                <el-table-column :label="$t('Booked At')" width="150">
                    <template #default="scope">
                        {{ bookedAtHandler(scope.row.created_at) }}
                    </template>
                </el-table-column>
                <el-table-column width="40" fixed="right">
                    <template #default="scope">
                        <el-dropdown v-if="canEdit(scope.row)" trigger="click" popper-class="fcal_select">
                            <span class="el-dropdown-link">
                                    <el-icon><MoreFilled /></el-icon>
                            </span>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item
                                        @click="sendConfirmationEmail(scope.row)">
                                        <el-icon>
                                            <Notification/>
                                        </el-icon>
                                        {{ $t('Send Confirmation Email') }}
                                    </el-dropdown-item>
                                    <el-dropdown-item 
                                        @click="rescheduleBooking(scope.row.reschedule_url)">
                                        <el-icon>
                                            <Refresh/>
                                        </el-icon>
                                        {{ $t('Reschedule') }}
                                    </el-dropdown-item>
                                    <el-dropdown-item 
                                        @click="cancelBooking(scope.row)">
                                        <el-icon>
                                            <Close/>
                                        </el-icon>
                                        {{ $t('Cancel') }}
                                    </el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </template>
                </el-table-column>
            </el-table>
            <div class="fcal_right fcal_tm20">
                <pagination popper-class="fcal_select" :pagination="pagination" @fetch="fetchGuests"/>
            </div>
        </div>
        <el-dialog
            v-model="cancelDialog"
            width="30%"
            :title="$t('Cancel Meeting')"
            class="fcal_modal"
        >
            <div style="text-align: center;">
                <h3>{{ selectedBooking.calendar_event?.title }}</h3>
                <p class="fcal_meeting_with">{{ $t('with') }} <b>{{ selectedBooking.first_name }} {{
                        selectedBooking.last_name
                    }}</b></p>
                <p class="fcal_meeting_time">{{ meetingTime }}</p>
                <p>{{ $t('ScheduleBookingDetails/cancel_event_desc') }}</p>
                <el-input type="textarea" v-model="cancelReason"
                          :placeholder="$t('Reason for cancellation')"></el-input>
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
import { MoreFilled, Refresh, Close, Download, Search, Notification } from '@element-plus/icons-vue';
import EditableBookingData from "./EditableBookingData";
import Pagination from "../../../Pieces/Pagination.vue";
import PaymentLogs from './PaymentLogs';
export default {
    name: "GroupBookingGuests",
    props: ['group_id'],
    emits: ['updateAdditionalInfo'],
    components: {
        PaymentLogs,
        Pagination,
        EditableBookingData,
        MoreFilled,
        Notification,
        Refresh,
        Close,
        Download,
        Search
    },
    data() {
        return {
            bookingId: null,
            selectedBooking: {},
            cancelReason: '',
            attendees: [],
            loading: false,
            updating: false,
            sidebar_loading: false,
            app_loaded: false,
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 20
            },
            search: '',
            cancelDialog: false,
            payment_order: null,
            main_body_contents: []
        }
    },
    computed: {
        canEdit() {
            return (booking) => {
                if (['cancelled', 'completed'].includes(booking.status)) {
                    return false;
                }

                if (this.hasAccess('manage_all_bookings')) {
                    return true;
                }

                return booking.host_user_id == this.appVars.me.id;
            }
        },
        meetingTime() {
            const startTime = this.toCurrentTimezone(this.selectedBooking.start_time, this.appVars.date_time_formatter);
            const endTime = this.toCurrentTimezone(this.selectedBooking.end_time, this.appVars.date_time_formatter);
            return `${startTime} - ${endTime}`;
        },
    },
    methods: {
        fetchGuests() {
            this.loading = true;
            this.$get(`schedules/group-bookings/${this.group_id}/attendees`, {
                per_page: this.pagination.per_page,
                page: this.pagination.current_page,
                search: this.search
            })
                .then(response => {
                    this.attendees = response.attendees.data;
                    this.pagination.total = response.attendees.total;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                    this.app_loaded = true;
                });
        },
        getAdditionalData(bookingId) {
            this.sidebar_loading = true;
            this.$get(`schedules/${bookingId}/meta-info`)
                .then(response => {
                    this.updateAdditionalData(response);
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.sidebar_loading = false;
                });
        },
        updateScheduleStatus(new_status) {
            this.updating = true;
            const data = {
                column: 'status',
                value: new_status
            };
            if (new_status == 'cancelled') {
                data.cancel_reason = this.cancelReason;
            }

            this.$put(`schedules/${this.selectedBooking.id}`, data)
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.selectedBooking.status = new_status;
                    this.selectedBooking.happening_status = '';
                    this.cancelDialog = false;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                });
        },
        cancelBooking(booking) {
            this.cancelDialog = true;
            this.selectedBooking = booking;
        },
        exportHosts() {
            location.href = window.ajaxurl + '?' + jQuery.param({
                action: 'fluent_booking_export_hosts',
                group_id: this.group_id
            });
        },
        bookedAtHandler(date) {
            return this.toCurrentTimezone(date, 'DD MMM YYYY, hh:mma')
        },
        handleExpand(row, expandedRows) {
            if (expandedRows.includes(row)) {
                this.bookingId = row.id;
                this.getAdditionalData(row.id);
            }
        },
        updateAdditionalData(booking) {
            this.payment_order = booking.payment_order;
            this.main_body_contents = booking.main_body_contents;
            this.$emit('updateAdditionalInfo', booking.activities, booking.sidebar_contents)
        },
        rescheduleBooking(rescheduleUrl) {
            window.open(rescheduleUrl, '_blank');
        },
        sendConfirmationEmail(booking) {
            this.updating = true;
            this.selectedBooking = booking;
            this.$post(`schedules/${booking.id}/send-confirmation-email`, {
                    email_to: 'guest'
                })
                .then(response => {
                    this.$handleSuccess(response.message);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                    this.getAdditionalData(booking.id);
                });
        },
        handleDataUpdated(data) {
            if (this.booking) {
                this.booking[data.key] = data.value;
            }
        }
    },
    mounted() {
        this.fetchGuests();
    }
}
</script>
