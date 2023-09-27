<template>
    <div :class="{ fcal_showing_details: spot_id }" class="fcal_section fcal_schedlues fcal_section_narrow">
        <div class="fcal_section_header">
            <div class="fcal_title">
                <h3>Scheduled Meetings</h3>
            </div>
        </div>

        <div class="fcal_schedule_meetings_header">
            <div class="fcal_schedule_meetings_nav">
                <ul class="fcal_secendary_nav_items">
                    <li @click="changePeriod('upcoming')" :class="{fcal_active : filters.period == 'upcoming' }">Upcoming</li>
                    <li @click="changePeriod('past')" :class="{fcal_active : filters.period == 'past' }">Past</li>
                </ul>
            </div>
        </div>
        <div class="fcal_schedule_meetings_header_actions">
            <div class="top">
                <div v-if="all_hosts" class="fcal_head_actions">
                    <el-select
                        v-model="filters.author"
                        class="fcal_select"
                        popper-class="fcal_select"
                        @change="fetchSchedules()">
                        <el-option value="me" label="My Meetings"></el-option>
                        <el-option value="all" label="All Meetings"></el-option>
                        <el-option v-for="host in all_hosts" :key="host.id" :value="host.id" :label="host.label"></el-option>
                    </el-select>
                </div>

                <div class="fcal_schedule_meetings_header_action_right">
                    <el-date-picker
                        v-model="query.date_to_date"
                        type="daterange"
                        start-placeholder="Start Date"
                        range-separator="-"
                        end-placeholder="End Date"
                        popper-class="fcal_daterange_popover"
                    />
                    <el-button class="fcal_plain_btn" @click="showAdvancedFilter = !showAdvancedFilter">
                        <el-icon><Filter /></el-icon> Filter
                    </el-button>
                </div>
            </div>

            <div v-if="showAdvancedFilter" class="fcal_schedule_meetings_header_filters">
                <div class="fcal_schedule_meetings_header_filters_inner">
                    <el-select
                        v-model="query.host"
                        class="fcal_select"
                        placeholder="Host Name"
                        popper-class="fcal_select">
                        <el-option v-for="host in all_hosts" :key="host.id" :value="host.id" :label="host.label"></el-option>
                    </el-select>
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
                        <el-option value="available">Available</el-option>
                        <el-option value="unavailable">Unavailable</el-option>
                    </el-select>
                    <el-button class="fcal_primary_btn2" @click="showAdvancedFilter = false">
                        <el-icon><CircleClose /></el-icon> Discard
                    </el-button>
                    <el-button class="fcal_primary_btn">
                        Submit
                    </el-button>
                </div>
            </div>
        </div>

        <div class="fcal_schedule_meetings_body">

            <div v-if="schedules" :class="{ fcal_showing_details: spot_id }" class="fcal_all_schedules">
                <div class="fcal_schedule_wrapper">
                    <div class="fcal_schedule_items">
                        <schedule-spots
                            :spots="schedules"
                        />
                    </div>
                </div>
                <div class="fcal_right fcal_tm20">
                    <pagination :pagination="pagination" @fetch="fetchSchedules"/>
                </div>
            </div>

        </div>
    </div>
</template>

<script type="text/babel">
import Pagination from "../../Pieces/Pagination.vue";
import ScheduleSpots from "./parts/ScheduleSpots.vue";
import SpotInfo from './parts/SpotInfo.vue';
import each from 'lodash/each';
import { Filter, CircleClose } from '@element-plus/icons-vue';

export default {
    name: 'AllSchedules',
    components: {
        ScheduleSpots,
        Pagination,
        SpotInfo,
        Filter,
        CircleClose
    },
    data() {
        return {
            schedules: [],
            schedulesLength: 0,
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
            spot_id: false,
            current_spot: null,
            loadingHosts: false,
            all_hosts: null,
            showAdvancedFilter: false,
            query: {
                date_to_date: '',
                host: '',
                eventType: '',
                status: ''
            }
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
                const startTime = schedule[0].start_time;
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
        }
    },
    methods: {
        fetchSchedules() {
            this.loading = true;
            this.spot_id = false;
            this.current_spot = null;

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
            if(this.filters.period != period) {
                this.$router.push({query: {period}});
                this.filters.period = period;
                this.fetchSchedules();
            }
        },
        showDetails(spot) {
            this.$router.push({query: { period: this.filters.period, spot_id: spot[0].event_id }});
            this.current_spot = spot;
            this.spot_id = spot[0].event_id;
        }
    },
    mounted() {
        if (this.$route.query.period) {
           this.filters.period =  this.$route.query.period;
        }
        this.$changeTitle('Schedules');
        this.fetchSchedules();
        if (this.$route.query.spot_id) {
            this.spot_id = this.$route.query.spot_id;
        }

        if(this.hasSupport('multi_users')) {
            this.fetchHosts();
        }
    }
}
</script>
