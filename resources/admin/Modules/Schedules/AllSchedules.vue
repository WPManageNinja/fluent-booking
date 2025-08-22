<template>
    <div :class="{ fcal_showing_details: booking_id }" class="fcal_section fcal_section_narrow">
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
                    <el-dropdown trigger="click" popper-class="fcal_select">
                        <span class="fcal_add el-dropdown-link">
                            <el-icon><Plus /></el-icon>
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
                </template>
            </div>
            <div v-if="!booking_id" class="fcal_actions">
                <el-radio-group v-model="viewType" class="fcal_radio_btn_group">
                    <el-radio-button label="list">
                        {{ $t('List View') }}
                    </el-radio-button>
                    <el-radio-button @click="maybeOpenProNotice" label="calendar" :disabled="!appVars.has_pro">
                        {{ $t('Calendar View') }}
                    </el-radio-button>
                </el-radio-group>
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
                        v-model="search"
                        @keyup.enter="fetchSchedules"
                        clearable
                        :placeholder="$t('Search Booking')"
                        class="fcal_search_input">
                        <template #append>
                            <el-button @click="fetchSchedules">
                                <el-icon><Search /></el-icon>
                            </el-button>
                        </template>
                    </el-input>

                    <el-select
                        v-if="filters.author == 'me'"
                        v-model="filters.event_type"
                        class="fcal_select"
                        :aria-placeholder="$t('Select Event Types')"
                        popper-class="fcal_select"
                        @change="handlePeriodChange()"
                        placement="bottom">
                        <el-option value="all" :label="$t('All Event Types')"/>
                        <el-option value="single" :label="$t('One-to-One')"/>
                        <el-option value="group" :label="$t('Group')"/>
                        <el-option value="round_robin" :label="$t('Round Robin')"/>
                        <el-option value="collective" :label="$t('Collective')"/>
                    </el-select>

                    <el-select
                        v-if="filters.author == 'me'"
                        v-model="filters.event"
                        class="fcal_select"
                        :aria-placeholder="$t('Select Event')"
                        popper-class="fcal_select"
                        @change="handlePeriodChange()"
                        placement="bottom">
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
                        placement="bottom">
                        <el-option value="me" :label="$t('My Meetings')" />
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

        <div class="fcal_schedule_meetings_body" v-if="viewType != 'calendar' || booking_id">
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
        </div>
        <div v-else-if="viewType == 'calendar'">
            <CalendarView
                :schedules="schedules"
                @dateUpdated="handleDateUpdated"
                @updateSchedule="fetchSchedules"/>
        </div>
        <p>{{ $t('All dates are shown in') }} {{ currentTimezone }} {{ $t('timezone') }}</p>
        <AddNewBookingModal
            v-if="isNewBookingOpen"
            :showModal="isNewBookingOpen"
            :calendarEventLists="calendarEventLists"
            @closeModal="closeModal"
            @addNewBooking="fetchSchedules"
        />
        <ProNoticeDialog 
            v-if="noticeModal" 
            :openModal="noticeModal" 
            :title="$t('Calendar View')"
            @update:openModal="noticeModal = $event"
        />
    </div>
</template>

<script>
import Pagination from "../../Pieces/Pagination";
import BookingCard from "./parts/BookingCard";
import CalendarView from "./parts/CalendarView";
import AddNewBookingModal from "./parts/_AddNewBookingModal";
import ScheduleBookingDetails from './parts/ScheduleBookingDetails';
import ProNoticeDialog from "@/Components/Common/ProNoticeDialog.vue";
import { Back, Filter, ArrowLeft, Search, Calendar } from '@element-plus/icons-vue';
import each from 'lodash/each';

export default {
    name: 'AllSchedules',
    components: {
        BookingCard,
        Pagination,
        ScheduleBookingDetails,
        AddNewBookingModal,
        ProNoticeDialog,
        CalendarView,
        Filter,
        Back,
        ArrowLeft,
        Search,
        Calendar
    },
    data() {
        return {
            schedules: [],
            loading: true,
            booking_id: false,
            current_schedule: null,
            event_types: [],
            pendingCount: 0,
            cancelledCount: 0,
            noShowCount: 0,
            calendarEventLists: {},
            isHideSidebar: false,
            currentEventTitle: '',
            search: '',
            viewType: 'list',
            noticeModal: false,
            isNewBookingOpen: false,
            pagination: this.initPagination(),
            filters: this.appVars.default_booking_filters || this.initFilters()
        }
    },
    watch: {
        viewType: {
            handler(newVal) {
                if (newVal == 'list') {
                    this.pagination = this.initPagination();
                    delete this.filters.range;
                    this.fetchSchedules();
                }
                localStorage.setItem('fcal_view_type', newVal);
            }
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
            const period = this.filters.period;
            const isDescending = ['completed', 'cancelled'].includes(period);
            if(period == 'latest_bookings') {
                if (this.schedules.length) {
                    items[this.$t('Sorted by booked at date time')] = this.schedules;
                }
                return items;
            }

            each(this.schedules, (schedule) => {
                const time = schedule.start_time;
                let date = this.toCurrentTimezone(time, this.appVars.date_format);
                items[date] = items[date] || [];
                items[date].push(schedule);
            });

            const sortedSchedules = {};
            Object.keys(items)
                .sort((a, b) => isDescending ?
                    new Date(b) - new Date(a) :
                    new Date(a) - new Date(b)
                )
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
            if (this.viewType == 'list') {
                statuses.latest_bookings = this.$t('Latest Bookings');
            }
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
                search: this.search,
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
        initFilters() {
            return {
                period: 'upcoming',
                author: 'me',
                event: 'all',
                event_type: 'all'
            }
        },
        initPagination(perPage = null) {
            const defaultPerPage = this.appVars.default_paginations?.bookings || 10;
            return {
                current_page: 1,
                per_page: perPage || defaultPerPage
            }
        },
        initRange(date, viewMode) {
            return {
                start_date: dayjs(date).startOf(viewMode).format('YYYY-MM-DD'),
                end_date: dayjs(date).endOf(viewMode).format('YYYY-MM-DD'),
                time_zone: this.currentTimezone
            };
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
            const { range, ...filtersWithoutRange } = this.filters;
            this.$router.push({ query: filtersWithoutRange });
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
        handleDateUpdated(date, viewMode) {
            this.pagination = this.initPagination(500);
            this.filters.range = this.initRange(date, viewMode);
            this.fetchSchedules();
        },
        maybeOpenProNotice() {
            if (!this.appVars.has_pro) {
                this.noticeModal = true;
            }
        },
        closeModal() {
            this.isNewBookingOpen = false;
        }
    },
    mounted() {
        Object.assign(this.filters, this.$route.query);
        ['author', 'event'].forEach(key => {
            if (!isNaN(this.filters[key])) {
                this.filters[key] = parseInt(this.filters[key]);
            }
        });
        if (this.appVars.has_pro) {
            this.viewType = localStorage.getItem('fcal_view_type') || 'list';
        }
        if (this.viewType != 'calendar' || this.$route.query.booking_id) {
            this.fetchSchedules();
        }
        if (this.$route.query.booking_id) {
            this.booking_id = this.$route.query.booking_id;
        }
        this.isHideSidebar = localStorage.getItem("hide_schedule_details_sidebar") == 'true';
    }
}
</script>
