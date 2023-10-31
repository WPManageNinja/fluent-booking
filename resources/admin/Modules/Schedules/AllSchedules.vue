<template>
    <div :class="{ fcal_showing_details: booking_id }" class="fcal_section fcal_schedlues fcal_section_narrow">
        <div class="fcal_section_header">
            <div class="fcal_title">
                <div v-if="booking_id" class="fcal_back_btn">
                    <el-breadcrumb separator="/">
                        <el-breadcrumb-item  @click="goBackToList">Bookings</el-breadcrumb-item>
                        <el-breadcrumb-item>{{ statusFilters[filters.period] || filters.period }}</el-breadcrumb-item>
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
                    <el-input
                        v-model="filters.search"
                        @keyup.enter="fetchSchedules"
                        clearable
                        :placeholder="$t('Search Booking')"
                        class="fcal_search_input"
                    >
                        <template #append>
                            <el-button @click="fetchSchedules">
                                <el-icon><Search /></el-icon>
                            </el-button>
                        </template>
                    </el-input>

                    <el-select v-if="filters.author == 'me'"
                        v-model="filters.event_type"
                        class="fcal_select"
                        :aria-placeholder="$t('Select Event Types')"
                        popper-class="fcal_select"
                        @change="handlePeriodChange()"
                       placement="bottom"
                    >
                        <template v-if="event_types.length">
                            <el-option value="all" :label="$t('All Events')" />
                            <el-option v-for="event in event_types" :key="event.id" 
                                :value="event.id" :label="event.label">
                            </el-option>
                        </template>
                    </el-select>
                    <el-select
                        v-model="filters.author"
                        class="fcal_select"
                        popper-class="fcal_select"
                        @change="handlePeriodChange()"
                        placement="bottom"
                    >
                        <el-option value="me" :label="$t('My Meetings')"></el-option>
                        <el-option v-if="hasAccess('manage_all_bookings')" value="all" :label="$t('All Meetings')" />
                        <template v-if="all_hosts.length">
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
                                :class="{fcal_active : filters.period == 'upcoming' }">{{ $t('Upcoming') }}
                            </li>
                            <li @click="changePeriod('past')" :class="{fcal_active : filters.period == 'past' }">{{ $t('Past') }}
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-if="showAdvancedFilter" class="fcal_schedule_meetings_header_filters">
                    <div class="fcal_schedule_meetings_header_filters_inner">
                        <el-select
                            v-model="query.eventType"
                            class="fcal_select"
                            :placeholder="$t('Event Type')"
                            popper-class="fcal_select">
                            <el-option value="single">{{ $t('Single') }}</el-option>
                            <el-option value="group">{{ $t('Group') }}</el-option>
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
                            {{ $t('Discard') }}
                        </el-button>
                        <el-button class="fcal_primary_btn" @click="fetchSchedules">
                            {{ $t('Submit') }}
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
                                        <booking-card :period="filters.period" :showing_id="booking_id" :multi_host="filters.author != 'me'"
                                                       @showDetails="showDetails(schedule)" :booking="schedule"/>
                                    </div>
                                </div>
                            </div>
                            <el-empty v-if="!schedulesLength" :description="$t('No bookings found based on your filter')"/>
                        </div>
                        <div v-if="!booking_id" class="fcal_right fcal_tm20">
                            <pagination :pagination="pagination" @fetch="fetchSchedules"/>
                        </div>
                    </div>
                    <div v-if="booking_id" class="fcal_spot_details">
                        <schedule-booking-details @bookingFetched="updateCurrentSchedule" :booking="current_schedule" :booking_id="booking_id"/>
                    </div>
                </div>
            </div>
            <el-skeleton v-else :rows="5" animated/>
            <p>{{ $t('All dates are shown in') }} {{ currentTimezone }} {{ $t('timezone') }}</p>
        </div>
    </div>
</template>

<script type="text/babel">
import Pagination from "../../Pieces/Pagination.vue";
import BookingCard from "./parts/BookingCard.vue";
import ScheduleBookingDetails from './parts/ScheduleBookingDetails.vue';
import each from 'lodash/each';
import {Back, Filter, CircleClose, ArrowLeft, Search} from '@element-plus/icons-vue';

export default {
    name: 'AllSchedules',
    components: {
        BookingCard,
        Pagination,
        ScheduleBookingDetails,
        Filter,
        Back,
        CircleClose,
        ArrowLeft,
        Search
    },
    data() {
        return {
            schedules: [],
            loading: true,
            filters: {
                period: 'upcoming',
                author: 'me',
                event_type: 'all',
                search: ''
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
            event_types: [],
            showAdvancedFilter: false,
            query: {
                date_to_date: '',
                eventType: '',
                status: ''
            },
            pendingCount: 0,
            cancelledCount: 0,
            noShowCount: 0,
            isHideSidebar: false,
            currentEventTitle: '',
            search: ''
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
            if(this.filters.period == 'latest_bookings') {
                if (this.schedules.length) {
                    items['Sorted by booked at date time'] = this.schedules;
                }
                return items;
            }

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
        },
        currentPeriod() {
            const period = this.filters.period;
            return period.charAt(0).toUpperCase() + period.slice(1);
        },
        statusFilters() {
            const statuses = {
                upcoming: this.$t('Upcoming'),
                completed: this.$t('Completed')
            }

            if(this.pendingCount) {
                statuses.pending = this.$t('Pending')+' (' + this.pendingCount + ')';
            }

            if(this.cancelledCount) {
                statuses.cancelled = this.$t('Cancelled');
            }
            if(this.noShowCount) {
                statuses.no_show = this.$t('No Show');
            }

            statuses.latest_bookings = this.$t('Latest Bookings');
            statuses.all = this.$t('All');
            return statuses;
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
                    if(response.pending_count) {
                        this.pendingCount = response.pending_count;
                    }
                    if(response.cancelled_count) {
                        this.cancelledCount = response.cancelled_count;
                    }
                    if(response.no_show_count) {
                        this.noShowCount = response.no_show_count;
                    }
                    if(response.slotOptions) {
                        this.event_types = response.slotOptions;
                    }
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
            this.currentEventTitle = schedule.calendar_event?.title;
            
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
            });
            this.fetchSchedules();
        },
        handlePeriodChange() {
            this.$router.push({query: this.filters});
            this.fetchSchedules();
        },
        hideSidebar() {
            const hideSidebarVar = localStorage.getItem("hide_schedule_details_sidebar");
            this.isHideSidebar = !this.isHideSidebar;
            localStorage.setItem("hide_schedule_details_sidebar", this.isHideSidebar);
        },
        updateCurrentSchedule(newSchedule) {
            if (!newSchedule) {
                this.goBackToList();
            }
            this.current_schedule = newSchedule;
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
