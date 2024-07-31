<template>
    <div :class="{ fcal_showing_details: booking_id }" class="fcal_section fcal_schedlues fcal_section_narrow">
        <div class="fcal_section_header">
            <div class="fcal_title">
                <div v-if="booking_id" class="fcal_back_btn">
                    <el-breadcrumb separator="/">
                        <el-breadcrumb-item  @click="goBackToList">{{ $t('Bookings') }}</el-breadcrumb-item>
                        <el-breadcrumb-item>{{ statusFilters[filters.period] || filters.period }}</el-breadcrumb-item>
                        <el-breadcrumb-item>{{ current_schedule?.calendar_event?.title ?? '...' }}</el-breadcrumb-item>
                    </el-breadcrumb>
                </div>
                <template v-else>
                    <h3>{{ $t('Bookings') }}</h3>
                </template>
            </div>
            <div v-if="!booking_id" class="fcal_actions">
                <el-dropdown trigger="click" popper-class="fcal_select">
                    <span class="el-dropdown-link">
                        <el-icon><MoreFilled/></el-icon>
                    </span>
                    <template #dropdown>
                        <el-dropdown-menu>
                            <el-dropdown-item 
                                @click="isNewBookingOpen = true">
                                {{ $t('Create Booking Manually') }}
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
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

                    <el-select
                        v-if="filters.author == appVars.me.calendar_id"
                        v-model="filters.event_type"
                        class="fcal_select"
                        :aria-placeholder="$t('Select Event Types')"
                        popper-class="fcal_select"
                        @change="handlePeriodChange()"
                        placement="bottom"
                    >
                        <el-option value="all" :label="$t('All Event Types')"/>
                        <el-option value="single" :label="$t('One-to-One')"/>
                        <el-option value="group" :label="$t('Group')"/>
                        <el-option value="round_robin" :label="$t('Round Robin')"/>
                    </el-select>

                    <el-select
                        v-if="filters.author == appVars.me.calendar_id"
                        v-model="filters.event"
                        class="fcal_select"
                        :aria-placeholder="$t('Select Event')"
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
                        <el-option :value="appVars.me.calendar_id" :label="$t('My Meetings')" />
                        <el-option v-if="hasAllBookingAccess" value="all" :label="$t('All Meetings')" />
                        <template v-if="Object.keys(calendarEventLists).length">
                            <el-option
                                v-for="calendar in filteredCalendarEventLists"
                                :key="calendar.id"
                                :value="calendar.id"
                                :label="calendar.title">
                            </el-option>
                        </template>
                    </el-select>
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
                                        <booking-card 
                                            :period="filters.period"
                                            :showing_id="booking_id"
                                            :multi_host="filters.author != 'me'"
                                            :booking="schedule"
                                            @showDetails="showDetails(schedule)">
                                        </booking-card>
                                    </div>
                                </div>
                            </div>
                            <el-empty v-if="!schedulesLength" :description="$t('No bookings found based on your filter')"/>
                        </div>
                        <div v-if="!booking_id" class="fcal_right fcal_tm20">
                            <pagination popper-class="fcal_select" :pagination="pagination" @fetch="fetchSchedules"/>
                        </div>
                    </div>
                    <div v-if="booking_id" class="fcal_spot_details">
                        <schedule-booking-details @bookingFetched="updateCurrentSchedule" :booking="current_schedule" :booking_id="booking_id" :calendarEventLists="calendarEventLists"/>
                    </div>
                </div>
            </div>
            <el-skeleton v-else :rows="5" animated/>
            <p>{{ $t('All dates are shown in') }} {{ currentTimezone }} {{ $t('timezone') }}</p>
        </div>
        <AddNewBookingModal
            v-if="isNewBookingOpen"
            :showModal="isNewBookingOpen"
            :calendarEventLists="calendarEventLists"
            @closeModal="closeModal"
            @addNewBooking="fetchSchedules"
        />
    </div>
</template>

<script>
import Pagination from "../../Pieces/Pagination";
import BookingCard from "./parts/BookingCard";
import AddNewBookingModal from "./parts/_AddNewBookingModal";
import ScheduleBookingDetails from './parts/ScheduleBookingDetails';
import each from 'lodash/each';
import { Back, Filter, CircleClose, ArrowLeft, Search, MoreFilled } from '@element-plus/icons-vue';

export default {
    name: 'AllSchedules',
    components: {
        BookingCard,
        Pagination,
        ScheduleBookingDetails,
        AddNewBookingModal,
        Filter,
        Back,
        CircleClose,
        ArrowLeft,
        Search,
        MoreFilled
    },
    data() {
        return {
            schedules: [],
            loading: true,
            filters: {
                period: 'upcoming',
                author: this.appVars.me.calendar_id,
                event: 'all',
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
            calendarEventLists: {},
            isHideSidebar: false,
            currentEventTitle: '',
            search: '',
            isNewBookingOpen: false
        }
    },
    computed: {
        formattedDate() {
            return (date) => {
                if (this.isToday(date)) {
                    return this.$t('Today');
                } else if (this.isYesterday(date)) {
                    return this.$t('Yesterday');
                } else if (this.isTomorrow(date)) {
                    return this.$t('Tomorrow');
                }
                return date;
            }
        },
        formattedSchedules() {
            const items = {};
            if(this.filters.period == 'latest_bookings') {
                if (this.schedules.length) {
                    items[this.$t('Sorted by booked at date time')] = this.schedules;
                }
                return items;
            }

            each(this.schedules, (schedule) => {
                const startTime = schedule.start_time;
                let date = this.toCurrentTimezone(startTime, this.appVars.date_format);
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
        hasAllBookingAccess() {
            return this.hasAccess('manage_all_bookings') || this.hasAccess('read_all_bookings');
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
        },
        filteredCalendarEventLists() {
            const eventLists = Object.values(this.calendarEventLists);
            return eventLists.filter(calendar => calendar.id != this.appVars.me.calendar_id);
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
                    this.calendarEventLists = response.calendar_event_lists;
                    if(response.pending_count) {
                        this.pendingCount = response.pending_count;
                    }
                    if(response.cancelled_count) {
                        this.cancelledCount = response.cancelled_count;
                    }
                    if(response.no_show_count) {
                        this.noShowCount = response.no_show_count;
                    }
                    if(response.slot_options) {
                        this.event_types = response.slot_options;
                    }
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
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
            this.isHideSidebar = !this.isHideSidebar;
            localStorage.setItem("hide_schedule_details_sidebar", this.isHideSidebar);
        },
        updateCurrentSchedule(newSchedule) {
            if (!newSchedule) {
                this.goBackToList();
            }
            this.current_schedule = newSchedule;
        },
        closeModal() {
            this.isNewBookingOpen = false;
        }
    },
    mounted() {
        if (this.$route.query.period) {
            this.filters.period = this.$route.query.period;
        }
        this.fetchSchedules();
        if (this.$route.query.booking_id) {
            this.booking_id = this.$route.query.booking_id;
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
