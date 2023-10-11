<template>
    <div :class="{ fcal_showing_details: booking_id }" class="fcal_section fcal_schedlues fcal_section_narrow">
        <div class="fcal_section_header">
            <div class="fcal_title">
                <div v-if="booking_id" class="fcal_back_btn">
                    <el-breadcrumb separator="/">
                        <el-breadcrumb-item  @click="goBackToList">Bookings</el-breadcrumb-item>
                        <el-breadcrumb-item>{{ filters.period }}</el-breadcrumb-item>
                        <el-breadcrumb-item>{{ current_schedule?.slot?.title }}</el-breadcrumb-item>
                    </el-breadcrumb>
                </div>
                <template v-else>
                    <h3>Bookings</h3>
                </template>
            </div>
        </div>

        <template v-if="!booking_id">
            <div style="margin-bottom: 0;" class="fcal_section_header">
                <div class="fcal_section_filters">
                    <el-radio-group @change="handlePeriodChange()" class="fcal_radio_switch" v-model="filters.period">
                        <el-radio-button v-for="(label, status) in statusFilters" :key="status" :label="status">{{ label }}</el-radio-button>
                    </el-radio-group>
                </div>
                <div class="fcal_section_actions">
                    <el-select
                        v-model="filters.author"
                        class="fcal_select"
                        popper-class="fcal_select"
                        @change="handlePeriodChange()">
                        <el-option value="me" label="My Meetings"></el-option>
                        <template v-if="all_hosts.length">
                            <el-option value="all" label="All Meetings" />
                            <el-option v-for="host in all_hosts" :key="host.id" :value="host.id" :label="host.label"></el-option>
                        </template>
                    </el-select>
                </div>
            </div>
            <div v-if="false" class="fcal_schedule_meetings_header">
                <div class="fcal_schedule_meetings_header_actions">
                    <div class="fcal_schedule_meetings_nav">
                        <ul class="fcal_secendary_nav_items">
                            <li @click="changePeriod('upcoming')"
                                :class="{fcal_active : filters.period == 'upcoming' }">Upcoming
                            </li>
                            <li @click="changePeriod('past')" :class="{fcal_active : filters.period == 'past' }">Past
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-if="showAdvancedFilter" class="fcal_schedule_meetings_header_filters">
                    <div class="fcal_schedule_meetings_header_filters_inner">
                        <el-select
                            v-model="query.eventType"
                            class="fcal_select"
                            placeholder="Event Type"
                            popper-class="fcal_select">
                            <el-option value="single">Single</el-option>
                            <el-option value="group">Group</el-option>
                        </el-select>
                        <el-select
                            v-model="query.status"
                            class="fcal_select"
                            placeholder="Status"
                            popper-class="fcal_select">
                            <el-option value="scheduled">Scheduled</el-option>
                            <el-option value="completed">Completed</el-option>
                            <el-option value="cancelled">Cancelled</el-option>
                        </el-select>
                        <el-button
                            v-if="query.eventType || query.status"
                            class="fcal_primary_btn2 danger"
                            @click="handleDiscard">
                            <el-icon>
                                <CircleClose/>
                            </el-icon>
                            Discard
                        </el-button>
                        <el-button class="fcal_primary_btn" @click="fetchSchedules">
                            Submit
                        </el-button>
                    </div>
                </div>
            </div>
        </template>

        <div class="fcal_schedule_meetings_body">
            <div v-if="!loading" class="fcal_section_body" style="padding: 0;" :class="isHideSidebar ? 'hide_sidebar' : ''">
                <div v-if="schedules" :class="{ fcal_showing_details: booking_id }" class="fcal_all_schediles">
                    <el-button class="fcal_hide_schedule_sidebar" @click="hideSidebar">
                        <el-icon><ArrowLeft /></el-icon>
                    </el-button>

                    <div class="fcal_schedules" :class="isHideSidebar ? 'hide_sidebar' : ''">
                        <div class="fcal_schedule_wrapper">
                            <div v-if="schedulesLength" v-for="(daySchedules, scheduleDate) in formattedSchedules"
                                 :key="scheduleDate" class="fcal_schedule">
                                <div class="fcal_schedule_header">
                                    <h3 class="fcal_schedule_data">{{ formattedDate(scheduleDate) }}</h3>
                                </div>
                                <div class="fcal_schedule_items">
                                    <div
                                        v-for="schedule in daySchedules"
                                        :key="schedule.id"
                                        :class="{ fcal_is_current: schedule.id == booking_id }"
                                        class="fcal_each_spot">
                                        <booking-card :multi_host="filters.author != 'me'"
                                                       @showDetails="showDetails(schedule)" :booking="schedule"/>
                                    </div>
                                </div>
                            </div>
                            <el-empty v-else description="No bookings found based on your filter"/>
                        </div>
                        <div class="fcal_right fcal_tm20">
                            <pagination :pagination="pagination" @fetch="fetchSchedules"/>
                        </div>
                    </div>
                    <div v-if="booking_id" class="fcal_spot_details">
                        <schedule-booking-details @bookingFetched="(data) => { current_schedule = data; }" :booking="current_schedule" :booking_id="booking_id"/>
                    </div>
                </div>
            </div>
            <el-skeleton v-else :rows="5" animated/>
            <p>All dates are shown in {{ currentTimezone }} timezone</p>
        </div>
    </div>
</template>

<script type="text/babel">
import Pagination from "../../Pieces/Pagination.vue";
import BookingCard from "./parts/BookingCard.vue";
import ScheduleBookingDetails from './parts/ScheduleBookingDetails.vue';
import each from 'lodash/each';
import {Back, Filter, CircleClose, ArrowLeft} from '@element-plus/icons-vue';

export default {
    name: 'AllSchedules',
    components: {
        BookingCard,
        Pagination,
        ScheduleBookingDetails,
        Filter,
        Back,
        CircleClose,
        ArrowLeft
    },
    data() {
        return {
            schedules: [],
            loading: true,
            filters: {
                period: 'upcoming',
                author: 'me'
            },
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 10
            },
            booking_id: false,
            current_schedule: null,
            loadingHosts: false,
            all_hosts: [],
            showAdvancedFilter: false,
            query: {
                date_to_date: '',
                eventType: '',
                status: ''
            },
            statusFilters: {
                upcoming: 'Upcoming',
                completed: 'Completed',
                cancelled: 'Cancelled',
                all: 'All'
            },
            isHideSidebar: false,
            currentEventTitle: ''
        }
    },
    computed: {
        formattedDate() {
            return (date) => {
                if (this.isToday(date)) {
                    return 'Today';
                } else if (this.isYesterday(date)) {
                    return 'Yesterday';
                } else if (this.isTomorrow(date)) {
                    return 'Tomorrow';
                }
                return date;
            }
        },
        formattedSchedules() {
            const items = {};
            each(this.schedules, (schedule) => {
                const startTime = schedule.start_time;
                let date = this.toCurrentTimezone(startTime, 'MMMM D, YYYY');
                items[date] = items[date] || [];
                items[date].push(schedule);
            });

            const sortedSchedules = {};
            Object.keys(items)
                .sort((a, b) => new Date(a) - new Date(b))
                .forEach((date) => {
                    sortedSchedules[date] = items[date];
                });
            return sortedSchedules;
        },
        schedulesLength() {
            return Object.keys(this.formattedSchedules).length;
        }
    },
    methods: {
        fetchSchedules() {
            this.loading = true;
            this.booking_id = false;
            this.current_schedule = null;

            this.$get('schedules', {
                per_page: this.pagination.per_page,
                page: this.pagination.current_page,
                filters: this.filters
            })
                .then(response => {
                    this.schedules = response.schedules.data;
                    this.pagination.total = response.schedules.total;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        fetchHosts() {
            this.loadingHosts = true;
            this.$get('admin/other-hosts')
                .then(response => {
                    this.all_hosts = response.hosts;
                });
        },
        changePeriod(period) {
            if (this.filters.period != period) {
                this.$router.push({query: {period}});
                this.filters.period = period;
                this.fetchSchedules();
            }
        },
        showDetails(schedule) {
            this.$router.push({query: {period: this.filters.period, booking_id: schedule.id}});
            this.current_schedule = schedule;
            this.booking_id = schedule.id;
            this.currentEventTitle = schedule.slot.title;
        },
        handleDiscard() {
            this.query.eventType = '';
            this.query.status = '';
            this.showAdvancedFilter = false;
            this.fetchSchedules();
        },
        goBackToList() {
            this.booking_id = null;
            this.current_schedule = null;
            this.$router.push({
                name: 'scheduled_events',
                query: {period: this.filters.period}
            })
        },
        handlePeriodChange() {
            this.$router.push({query: this.filters});
            this.fetchSchedules();
        },
        hideSidebar() {
            const hideSidebarVar = localStorage.getItem("hide_schedule_details_sidebar");
            this.isHideSidebar = !this.isHideSidebar;
            // if (hideSidebarVar == 'true') {
            //     this.isHideSidebar = true;
            // } else {
            //     this.isHideSidebar = false;
            // }
            localStorage.setItem("hide_schedule_details_sidebar", this.isHideSidebar);
        }
    },
    mounted() {
        if (this.$route.query.period) {
            this.filters.period = this.$route.query.period;
        }
        this.$changeTitle('Schedules');
        this.fetchSchedules();
        if (this.$route.query.booking_id) {
            this.booking_id = this.$route.query.booking_id;
        }

        if (this.hasSupport('multi_users')) {
            this.fetchHosts();
        }

        const hideSidebarVar = localStorage.getItem("hide_schedule_details_sidebar");
        if (hideSidebarVar == 'true') {
            this.isHideSidebar = true;
        } else {
            this.isHideSidebar = false;
        }

    }
}
</script>
