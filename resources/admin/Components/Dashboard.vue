<template>
    <div class="fcal_dashboard fcal_section fcal_section_narrow">
        <div class="fcal_dashboard_overview fcal_dashboard_box">
            <div class="fcal_section_header">
                <div class="fcal_title">
                    <h3>{{ $t('Overview') }}</h3>
                </div>
                <div class="fcal_actions">
                    <el-date-picker
                        v-model="filterDate"
                        type="daterange"
                        unlink-panels
                        clearable
                        range-separator="-"
                        :start-placeholder="$t('Start date')"
                        :end-placeholder="$t('End date')"
                        :shortcuts="shortcuts"
                        popper-class="fcal_daterange_popover"
                        :disabled-date="disabledDate"
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
                    :class="['overview-widget', { navigation: widget.period }]">
                    <h1 v-html="widget.number"></h1>
                    <p @click="goToBookingLists(widget.period)">{{ widget.title }}</p>
                    <h3><span :class="{'stat': true, 'down': widget.stat < 0}"><el-icon><Top /></el-icon>
                        {{ widget.stat }}%</span>{{ widget.content }}</h3>
                    <span class="icon" v-html="widget.icon"></span>
                </div>
            </div>
        </div>

        <div class="fcal_dashboard_chat_wrap">
            <ReportChat/>

            <div class="fcal_dashboard_report_sidebar">
                <div class="fcal_schedule_event_infos">
                    <div class="fcal_schedule_details_header">
                        <h1 class="fcal_header_title">
                            {{ $t('Next Meetings') }}
                        </h1>
                    </div>

                    <div v-if="!loading" class="fcal_booking_activities_list">
                        <div v-if="nextMeetings.length" class="fcal_booking_activity" v-for="(schedule, i) in nextMeetings" :key="i">
                            <el-icon class="fcal_activity_complete_icon"></el-icon>

                            <span class="timing">
                                {{ formattedTimeRange(schedule.start_time, schedule.end_time) }}
                            </span>
                            <div class="description_and_link">
                                <span class="title" v-html="scheduleTitle(schedule)"></span>
                                <el-link type="primary" @click=viewMeetingDetails(schedule.id)>{{ $t('View') }}</el-link>
                            </div>
                        </div>
                        <div v-else class="fcal_no_activities">
                            <p>{{ $t('Next meeting not available') }}</p>
                        </div>
                    </div>
                    <div v-else>
                        <el-skeleton :row="5" animated/>
                    </div>
                </div>

                <div class="fcal_schedule_event_infos">
                    <div class="fcal_schedule_details_header">
                        <h1 class="fcal_header_title">
                            {{ $t('Latest Booked Meetings') }}
                        </h1>
                    </div>

                    <div class="fcal_schedule_event_infos_body">
                        <div v-if="!loading" class="fcal_booking_activities_list">
                            <div v-if="latestBookedLists.length" class="fcal_booking_activity" v-for="(schedule, i) in latestBookedLists" :key="i">
                                <el-icon class="fcal_activity_complete_icon"></el-icon>

                                <div class="description_and_link">
                                    <span class="description" v-html="bookingTitle(schedule)"></span><el-link type="primary" @click=viewMeetingDetails(schedule.id)>{{ $t('View') }}</el-link>
                                </div>
                            </div>
                            <div v-else class="fcal_no_activities">
                                <p>{{ $t('No Latest Booked Event Found') }}</p>
                            </div>
                        </div>
                        <div v-else>
                            <el-skeleton :row="5" animated/>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</template>

<script>
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
            filterChartsDate: '',
            shortcuts: [
                {
                    text: this.$t('Last week'),
                    value: () => {
                        const end = new Date()
                        const start = new Date()
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 7)
                        return [start, end]
                    },
                },
                {
                    text: this.$t('Last month'),
                    value: () => {
                        const end = new Date()
                        const start = new Date()
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 30)
                        return [start, end]
                    },
                },
                {
                    text: this.$t('Last 3 months'),
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
                const startTime = this.toCurrentTimezone(start_time, this.appVars.time_format);
                const endTime = this.toCurrentTimezone(end_time, this.appVars.time_format);
                return `${startTime} - ${endTime}, ${this.toCurrentTimezone(start_time, this.appVars.date_format)}`;
            }
        },
        bookingTitle() {
            return (schedule) => {
                const guestName = schedule.first_name + ' ' + schedule.last_name;
                const createdAt = this.convertDate(schedule.created_at)
                return '<b>' + guestName + '</b>' + ' ' + this.$t('booked a new meeting at') + ' ' + this.toCurrentTimezone(createdAt, this.appVars.date_time_formatter);
            }
        },
        scheduleTitle() {
            return (schedule) => {
                if (['group', 'group_event'].includes(schedule.event_type)) {
                    return schedule.booked_count + ' ' + this.$t('guests with') + ' ' + schedule.author.name + ' ' + this.$t('as group booking type');
                }
                return schedule.title;
            }
        }
    },
    methods: {
        disabledDate(time) {
            return time.getTime() > Date.now();
        },
        convertDate(date) {
            if (!date) {
                return '';
            }
            return this.toCurrentTimezone(date, 'YYYY-MM-DD HH:MM:ss')
        },
        viewMeetingDetails(scheduleId) {
            this.$router.push({
                name: 'scheduled_events',
                query:{booking_id: scheduleId}
            })
        },
        goToBookingLists($period) {
            if ($period) {
                this.$router.push({
                    name: 'scheduled_events',
                    query: {period: $period}
                })
            }
        },
        fetchReports() {
            this.loading = true;
            const startDate = this.filterDate ? this.filterDate[0] : '';
            const endDate = this.filterDate ? this.filterDate[1] : '';
            this.$get('reports', {
                    startTime: this.convertDate(startDate),
                    endTime: this.convertDate(endDate)
                })
                .then(response => {
                    this.widgets = response.overview;
                    this.latestBookedLists = response.latest_books;
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
        this.$changeTitle(this.$t('Dashboard'));
        this.fetchReports();
    }
};
</script>
