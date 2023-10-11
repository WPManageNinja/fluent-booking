<template>
    <div class="fcal_dashboard fcal_section fcal_section_narrow">
        <div class="fcal_dashboard_overview fcal_dashboard_box">
            <div class="fcal_section_header">
                <div class="fcal_title">
                    <h3>Overview</h3>
                </div>
                <div class="fcal_actions">
                    <el-date-picker
                        v-model="filterDate"
                        type="daterange"
                        unlink-panels
                        clearable
                        range-separator="-"
                        start-placeholder="Start date"
                        end-placeholder="End date"
                        :shortcuts="shortcuts"
                        popper-class="fcal_daterange_popover"
                        @change="fetchReports"
                    />
                </div>
            </div>

            <el-row v-if="loading" :gutter="15">
                <el-col :span="6">
                    <el-skeleton :rows="3" animated />
                </el-col>
                <el-col :span="6">
                    <el-skeleton :rows="3" animated />
                </el-col>
                <el-col :span="6">
                    <el-skeleton :rows="3" animated />
                </el-col>
                <el-col :span="6">
                    <el-skeleton :rows="3" animated />
                </el-col>
            </el-row>
            <div v-else class="overview-widgets">
                <div
                    v-for="(widget, i) in widgets"
                    :key="i"
                    class="overview-widget">
                    <h1>{{ widget.number }}</h1>
                    <p>{{ widget.title }}</p>
                    <h3><span class="stat"><el-icon><Top /></el-icon> {{ widget.stat }}</span>{{ widget.content }}</h3>
                    <span class="icon" v-html="widget.icon"></span>
                </div>
            </div>
        </div>

        <div class="fcal_dashboard_chat_wrap">
            <div class="fcal_dashboard_chat fcal_dashboard_box">
                <div class="fcal_section_header">
                    <div class="fcal_title">
                        <h3>Booking Trends</h3>
                    </div>
                    <div class="fcal_actions">
                        <el-date-picker
                            v-model="filterDate2"
                            type="date"
                            placeholder="Select Date"
                            popper-class="fcal_daterange_popover"
                        />
                    </div>
                </div>
                <ReportChat/>
            </div>

            <div class="fcal_dashboard_report_sidebar">

                <div class="fcal_new_booked_event_widget fcal_dashboard_report_widget">
                    <h1>Today Meeting</h1>
                    <el-skeleton v-if="loading" animated />
                    <div v-else class="fcal_dashboard_widget_body">
                        <ul>
                            <li v-for="(schedule, i) in nextMeetings" :key="i">
                                <span class="timing">
                                    {{ formattedTimeRange(schedule.start_time, schedule.end_time) }}
                                </span>
                                <span class="title">
                                    {{ schedule.slot?.title }}
                                </span>
                                <span class="duration"><b>Duration: </b> {{ schedule.slot_minutes }} Minutes</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="fcal_today_meeting_widget fcal_dashboard_report_widget">
                    <h1>Latest Booked</h1>
                    <el-skeleton v-if="loading" animated />
                    <div v-else class="fcal_dashboard_widget_body">
                        <ul>
                            <li v-for="(schedule, i) in latestBookedLists" :key="i">
                                <span class="timing">
                                    {{ formattedTimeRange(schedule.start_time, schedule.end_time) }}
                                </span>
                                <span class="description">
                                    {{ schedule.event_type === 'group' ? schedule.booked_count : ''}}
                                     guests with
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script type="text/babel">
import { Top } from '@element-plus/icons-vue';
import ReportChat from "../Pieces/_ReportChat";
import BookingCard from "../Modules/Schedules/parts/BookingCard";

export default {
    name: 'Dashboard',
    components: {
        BookingCard,
        ReportChat,
        Top
    },
    data() {
        return {
            filterDate: '',
            filterDate2: '',
            shortcuts: [
                {
                    text: 'Last week',
                    value: () => {
                        const end = new Date()
                        const start = new Date()
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 7)
                        return [start, end]
                    },
                },
                {
                    text: 'Last month',
                    value: () => {
                        const end = new Date()
                        const start = new Date()
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 30)
                        return [start, end]
                    },
                },
                {
                    text: 'Last 3 months',
                    value: () => {
                        const end = new Date()
                        const start = new Date()
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 90)
                        return [start, end]
                    },
                },
            ],
            loading: false,
            widgets: '',
            latestBookedLists: '',
            nextMeetings: ''
        }
    },
    computed: {
        formattedTimeRange() {
            return (start_time, end_time) => {
                const startTime = this.toCurrentTimezone(start_time, 'hh:mma');
                const endTime = this.toCurrentTimezone(end_time, 'hh:mma');
                return `${startTime} - ${endTime}`;
            }
        },
    },
    methods: {
        convertDate(date) {
            if (date) {
                return this.toCurrentTimezone(date, 'YYYY-MM-DD HH:MM:ss')
            }
            return '';
        },
        fetchReports() {
            this.loading = true;
            const startDate = this.filterDate ? this.filterDate[0] : '';
            const endDate = this.filterDate ? this.filterDate[1] : '';
            this.$get('reports', {
                    startDate: this.convertDate(startDate),
                    endDate: this.convertDate(endDate)
                })
                .then(response => {
                    this.widgets = response.overview;
                    this.latestBookedLists = response.latest_booked_lists;
                    this.nextMeetings = response.next_meetings;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
    mounted() {
        this.fetchReports();
    }
};
</script>
