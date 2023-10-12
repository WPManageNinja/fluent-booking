<template>
    <div class="fcal_schedule_event_infos">
        <div class="fcal_schedule_event_infos_body">
            <div class="fcal_schedule_details_header">
                <h1 class="fcal_header_title">
                    Event Guests
                </h1>
            </div>
            <el-skeleton v-if="!app_loaded" :rows="5" :animated="true" :loading="loading" />

            <el-table v-else v-loading="loading" stripe :data="attendees">
                <el-table-column type="expand">
                    <template #default="scope">
                        <div class="fcal_group_booking_guests_wrap">
                            <div class="fcal_schedule_details_event">
                                <div class="fcal_schedule_details_event_item">
                                    <h3>Message</h3>
                                    <div class="fcal_spot_details_value">
                                        {{ scope.row.message }}
                                    </div>
                                </div>
                                <div class="fcal_schedule_details_event_item">
                                    <h3>Timezone</h3>
                                    <div class="fcal_spot_details_value">
                                        {{ scope.row.person_time_zone }}
                                    </div>
                                </div>
                                <div class="fcal_schedule_details_event_item">
                                    <h3>Booking URL</h3>
                                    <div class="fcal_spot_details_value">
                                        <a :href="scope.row.source_url" target="_blank">{{ scope.row.source_url }}</a>
                                    </div>
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
                            <PaymentLogs v-if="scope.row.order_info" :booking="scope.row" />
                        </div>
                    </template>
                </el-table-column>

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
                <el-table-column label="Status" width="120">
                    <template #default="scope">
                        {{ scope.row.status }}
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
                                    <el-dropdown-item><el-icon><Close /></el-icon> Cancel</el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </template>
                </el-table-column>
            </el-table>
            <div class="fcal_right fcal_tm20">
                <pagination :pagination="pagination" @fetch="fetchGuests"/>
            </div>
        </div>
    </div>
</template>

<script>
import { MoreFilled, Close } from '@element-plus/icons-vue';
import Pagination from "../../../Pieces/Pagination.vue";
import PaymentLogs from "./PaymentLogs";
export default {
    name: "GroupBookingGuests",
    props: ['group_id'],
    components: {
        PaymentLogs,
        Pagination,
        MoreFilled,
        Close
    },
    data() {
        return {
            attendees: [],
            loading: false,
            app_loaded: false,
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 20
            }
        }
    },
    methods: {
        fetchGuests() {
            this.loading = true;
            this.$get(`schedules/group-bookings/${this.group_id}/attendees`, {
                per_page: this.pagination.per_page,
                page: this.pagination.current_page
            })
                .then(response => {
                    this.attendees = response.attendees.data;
                    this.pagination.total = response.attendees.total;
                    console.log(response.attendees.data);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                    this.app_loaded = true;
                });
        }
    },
    mounted() {
        this.fetchGuests();
    }
}
</script>
