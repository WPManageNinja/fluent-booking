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
                            <SourceDetailsSection v-if="scope.row.source != 'web'" :booking="scope.row"/>
                            <PaymentLogs v-if="scope.row.payment_order" :booking="scope.row" />
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
                        <el-dropdown trigger="click" popper-class="fcal_select">
                            <span class="el-dropdown-link">
                                    <el-icon><MoreFilled /></el-icon>
                            </span>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item><el-icon><Close /></el-icon> {{ $t('Cancel') }}</el-dropdown-item>
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
import { MoreFilled, Close, Download, Search } from '@element-plus/icons-vue';
import Pagination from "../../../Pieces/Pagination.vue";
import SourceDetailsSection from './SourceDetailsSection';
import PaymentLogs from "./PaymentLogs";
export default {
    name: "GroupBookingGuests",
    props: ['group_id'],
    components: {
        SourceDetailsSection,
        PaymentLogs,
        Pagination,
        MoreFilled,
        Close,
        Download,
        Search
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
            },
            search: ''
        }
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
        exportHosts() {
            location.href = window.ajaxurl + '?' + jQuery.param({
                action: 'fluent_booking_export_hosts',
                group_id: this.group_id
            });
        },
        bookedAtHandler(date) {
            const day   = this.$t(this.toCurrentTimezone(date, 'DD'));
            const month = this.$t(this.toCurrentTimezone(date, 'MMM'));
            const year  = this.$t(this.toCurrentTimezone(date, 'YYYY'));
            const hour  = this.$t(this.toCurrentTimezone(date, 'hh'));
            const min   = this.$t(this.toCurrentTimezone(date, 'mm'));
            const a     = this.$t(this.toCurrentTimezone(date, 'a'));
            return day + ' ' + month + ' ' + year +', ' + hour +':' + min + a;
        }
    },
    mounted() {
        this.fetchGuests();
    }
}
</script>
