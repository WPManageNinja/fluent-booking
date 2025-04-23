<template>
    <div class="fcal_calendar_view">
        <el-calendar v-model="selectedDate" :class="{ 'monthly_view': isMonthlyView, 'weekly_view': isWeeklyOrDailyView }">
            <template #header="{ date }">
                <el-radio-group v-model="viewMode" @change="changeViewMode" class="fcal_radio_switch">
                    <el-radio-button label="month">{{ $t('Month') }}</el-radio-button>
                    <el-radio-button label="week">{{ $t('Week') }}</el-radio-button>
                    <el-radio-button label="day">{{ $t('Day') }}</el-radio-button>
                </el-radio-group>
                <div class="header_date_picker" @click="$refs.datePicker.focus()">
                    <h3 class="header_date">{{ date }}</h3>
                    <el-date-picker
                        v-model="selectedRange"
                        :type="datePickerType"
                        class="date_picker_input"
                        ref="datePicker">
                    </el-date-picker>
                </div>
                <el-button-group>
                    <el-button @click="selectDate('prev')">
                        <el-icon><ArrowLeftBold /></el-icon>
                    </el-button>
                    <el-button @click="selectDate('today')">
                        {{ $t('Today') }}
                    </el-button>
                    <el-button @click="selectDate('next')">
                        <el-icon><ArrowRightBold /></el-icon>
                    </el-button>
                </el-button-group>
            </template>
            <template v-if="isMonthlyView" #date-cell="{ data }">
                <span class="day_number">
                    <el-button text @click="selectCurrentDay(data.day)">
                        {{ data.day.split('-')[2] }}
                    </el-button>
                </span>
                <template v-if="hasSchedulesForDay(data.day)">
                    <div v-for="schedule in firstThreeSchedules(data.day)" :key="schedule.id" class="fcal_booking_wrap">
                        <span class="booking_color" :style="{ background: schedule.calendar_event?.color_schema }"></span>
                        <el-popover
                            :width="420"
                            trigger="click"
                            :placement="popoverPlacement"
                            :persistent="false"
                            popper-class="schedule_details_popover"
                            :visible="isVisible(schedule)">
                            <template #reference>
                                <div class="booking_info" :class="schedule.status" @click="showDetails(schedule)">
                                    <span class="booking_time">{{ formatTime(schedule.start_time) }}</span>
                                    <p class="booking_title" v-html="schedule.title" :title="htmlTitle(schedule.title)"></p>
                                </div>
                            </template>
                            <template #default>
                                <BookingDetails
                                    :schedule="schedule" 
                                    @update="updateSchedule"
                                    @close="closeBookingModal" 
                                />
                            </template>
                        </el-popover>
                    </div>
                    <el-popover
                        v-if="showMore(data.day)"
                        :width="250"
                        trigger="click"
                        placement="right"
                        :persistent="false"
                        popper-class="all_schedules_popover"
                        :visible="isAllVisible(data.day)">
                        <template #reference>
                            <div class="fcal_more_wrap" @click="showAllSchedules(data.day)">
                                <el-icon><CirclePlus /></el-icon>
                                <div class="booking_info more_schedules">
                                    {{ formattedSchedules[data.day].length - 3 }} {{ $t('more') }}
                                </div>
                            </div>
                        </template>
                        <template #default>
                            <div class="fcal_all_schedules">
                                <div class="fcal_all_schedules_header">
                                    <el-button text @click="selectCurrentDay(data.day)">
                                        {{ this.toDateFormat(data.day, 'ddd, D MMMM') }}
                                    </el-button>
                                    <el-icon @click="closeAllSchedules(data.day)">
                                        <Close />
                                    </el-icon>
                                </div>
                                <div class="fcal_all_schedules_body">
                                    <div v-for="booking in formattedSchedules[data.day]" :key="booking.id" class="fcal_all_schedules_wrap">
                                        <span class="schedule_color" :style="{ background: booking.calendar_event?.color_schema }"></span>
                                        <el-popover
                                            :width="420"
                                            trigger="click"
                                            placement="right"
                                            :persistent="false"
                                            popper-class="schedule_details_popover"
                                            :visible="isDetailsVisible(booking)">
                                            <template #reference>
                                                <div class="schedule_info" :class="booking.status" @click="showDetailsSchedule(booking)">
                                                    <span class="schedule_time">{{ formatTime(booking.start_time) }}</span>
                                                    <p class="schedule_title" v-html="booking.title" :title="htmlTitle(booking.title)"></p>
                                                </div>
                                            </template>
                                            <template #default>
                                                <BookingDetails
                                                    :schedule="booking"
                                                    @update="updateSchedule"
                                                    @close="closeBookingModal"
                                                />
                                            </template>
                                        </el-popover>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </el-popover>
                </template>
            </template>
        </el-calendar>
        <div v-if="isWeeklyOrDailyView" class="weekly_calendar_view">
            <div class="weekly_header">
                <div class="time_column_header"></div>
                <div v-for="day in formattedWeekDays" :key="day.date" class="day_column_header">
                    <div class="day_name">{{ day.dayName }}</div>
                    <div class="day_number">
                        <el-button text @click="selectCurrentDay(day.date)">
                            {{ day.dayNumber }}
                        </el-button>
                    </div>
                </div>
            </div>
            <div class="weekly_grid" ref="weeklyGrid">
                <div class="time_column">
                    <div v-for="(hour, index) in formattedTimeSlots"
                        :key="hour"
                        class="time_slot">
                        <span v-if="index != 0">
                            {{ formatHour(hour) }}
                        </span>
                    </div>
                </div>
                <div class="days_grid">
                    <div v-for="day in formattedWeekDays" :key="day.date" class="day_column">
                        <div v-for="hour in formattedTimeSlots" :key="hour" class="hour_cell"></div>
                        <div v-for="event in getEventsForDay(day.date)"
                            :key="event.id"
                            :style="getEventStyle(event)"
                            @click="showDetails(event)"
                            class="calendar_event">
                            <el-popover
                                :width="420"
                                trigger="click"
                                :placement="popoverPlacement"
                                :persistent="false"
                                popper-class="schedule_details_popover"
                                :visible="isVisible(event)">
                                <template #reference>
                                    <div class="event_content" :class="event.status" @click="showDetails(event)">
                                        <span class="event_time">{{ formatTime(event.start_time) }}</span>
                                        <div class="event_title" v-html="event.title" :title="htmlTitle(event.title)"></div>
                                    </div>
                                </template>
                                <template #default>
                                    <BookingDetails
                                        :schedule="event"
                                        @update="updateSchedule"
                                        @close="closeBookingModal"
                                    />
                                </template>
                            </el-popover>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ArrowLeftBold, ArrowRightBold, CirclePlus, Close } from '@element-plus/icons-vue';
import BookingDetails from './_BookingDetails.vue';
import each from 'lodash/each';

export default {
    name: 'CalendarView',
    props: {
        schedules: {
            type: Array,
            required: true
        }
    },
    components: {
        ArrowLeftBold,
        ArrowRightBold,
        CirclePlus,
        Close,
        BookingDetails
    },
    data() {
        return {
            viewMode: '',
            selectedDate: null,
            selectedRange: null,
            selectedSchedule: null,
            selectedAllSchedules: null,
            selectedDetailsSchedule: null,
            currentSchedules: []
        };
    },
    watch: {
        selectedDate(newVal, oldVal) {
            const compareDate = this.isMonthlyView ? 'YYYY-MM' : 'YYYY-MM-DD';
            const newDate = this.toCurrentTimezone(newVal, compareDate);
            const oldDate = this.toCurrentTimezone(oldVal, compareDate);
            if (newDate !== oldDate) {
                this.$emit('dateUpdated', newDate, this.viewMode);
            }
        },
        selectedRange(newVal) {
            this.selectedDate = newVal;
        },
        schedules: {
            handler(newSchedules) {
                this.currentSchedules = newSchedules;
            },
            deep: true
        }
    },
    computed: {
        isMonthlyView() {
            return this.viewMode == 'month';
        },
        isWeeklyView() {
            return this.viewMode == 'week';
        },
        isDailyView() {
            return this.viewMode == 'day';
        },
        isWeeklyOrDailyView() {
            return ['day', 'week'].includes(this.viewMode);
        },
        datePickerType() {
            if (this.isDailyView) {
                return 'date';
            }
            return this.viewMode || 'month';
        },
        isVisible() {
            return (schedule) => {
                return this.selectedSchedule?.id === schedule.id;
            }
        },
        isAllVisible() {
            return (date) => {
                return this.selectedAllSchedules === date;
            }
        },
        isDetailsVisible() {
            return (booking) => {
                return this.selectedDetailsSchedule?.id === booking.id;
            }
        },
        showMore() {
            return (date) => {
                return this.formattedSchedules[date]?.length > 3;
            }
        },
        firstThreeSchedules() {
            return (date) => {
                return this.formattedSchedules[date].slice(0, 3);
            }
        },
        formattedSchedules() {
            const schedules = {};
            each(this.currentSchedules, (schedule) => {
                const date = this.toCurrentTimezone(schedule.start_time, 'YYYY-MM-DD');
                schedules[date] = schedules[date] || [];
                schedules[date].push(schedule);
            });
            for (const date in schedules) {
                schedules[date].sort((a, b) => 
                    new Date(a.start_time) - new Date(b.start_time)
                );
            }
            return schedules;
        },
        formattedWeekDays() {
            const days = [];
            const range = this.isDailyView ? 1 : 7;
            const startDate = dayjs(this.selectedDate).startOf(this.viewMode);
            for (let i = 0; i < range; i++) {
                const date = startDate.clone().add(i, 'days');
                days.push({
                    date: date.format('YYYY-MM-DD'),
                    dayName: date.format('ddd'),
                    dayNumber: date.format('D')
                });
            }
            return days;
        },
        formattedTimeSlots() {
            const slots = [];
            for (let i = 0; i < 24; i++) {
                slots.push(i);
            }
            return slots;
        },
        getEventsForDay() {
            return (date) => {
                return this.formattedSchedules[date] || [];
            }
        },
        popoverPlacement() {
            if (this.isDailyView) {
                return 'top-start';
            }
            return window.innerWidth <= 485 ? 'top' : 'right';
        },
        getEventStyle() {
            return (event) => {
                const startTime = this.convertToCurrentTimezone(event.start_time);
                const endTime = this.convertToCurrentTimezone(event.end_time);

                const topPosition = this.calculateMinutesSinceMidnight(startTime);
                const duration = endTime.diff(startTime, 'minutes');
                const top = (topPosition / 60) * 60; // 60px per hour
                const height = (duration / 60) * 60;
                const bgColor = event.calendar_event?.color_schema || 'rgb(10, 232, 240)';

                const style = {
                    background: bgColor,
                    top: `${top}px`,
                    height: `${height}px`
                };

                if (duration < 30) {
                    style.lineHeight = '1.2';
                    style.paddingTop = '0';
                    style.paddingBottom = '0';
                }

                if (event.status == 'cancelled') {
                    style.textDecoration = 'line-through';
                }

                return style;
            }
        }
    },
    methods: {
        formatTime(time) {
            return this.toCurrentTimezone(time, 'HH:mm');
        },
        htmlTitle(htmlTitle) {
            const div = document.createElement('div');
            div.innerHTML = htmlTitle;
            return div.textContent || div.innerText || '';
        },
        changeViewMode(newVal) {
            this.$emit('dateUpdated', this.selectedDate, newVal);
            localStorage.setItem('fcal_calendar_view_mode', newVal);
        },
        hasSchedulesForDay(date) {
            const hasSchedules = this.formattedSchedules[date]?.length;
            const currentMonth = this.toCurrentTimezone(this.selectedDate, 'YYYY-MM');
            const scheduleMonth = this.toCurrentTimezone(date, 'YYYY-MM');
            return hasSchedules && currentMonth === scheduleMonth;
        },
        selectDate(action) {
            if (action === 'today') {
                this.selectedDate = new Date();
                return;
            }
            const isPrev = action === 'prev';
            const navStep = isPrev ? -1 : 1;
            const currentDate = dayjs(this.selectedDate);
            this.selectedDate = currentDate.add(navStep, this.viewMode).toDate();
        },
        selectCurrentDay(date) {
            this.viewMode = 'day';
            this.selectedDate = dayjs(date).toDate();
        },
        showDetails(schedule) {
            this.selectedSchedule = schedule;
            this.selectedAllSchedules = null;
            this.selectedDetailsSchedule = null;
        },
        closeBookingModal() {
            this.selectedSchedule = null;
            this.selectedDetailsSchedule = null;
        },
        showAllSchedules(date) {
            this.selectedAllSchedules = date;
            this.selectedSchedule = null;
            this.selectedDetailsSchedule = null;
        },
        closeAllSchedules() {
            this.selectedAllSchedules = null;
        },
        showDetailsSchedule(booking) {
            this.selectedDetailsSchedule = booking;
            this.selectedSchedule = null;
        },
        updateSchedule() {
            this.$emit('updateSchedule');
        },
        calculateMinutesSinceMidnight(time) {
            const midnight = dayjs(time).startOf('day');
            return time.diff(midnight, 'minutes');
        },
        formatHour(hour) {
            return dayjs().startOf('hour').hour(hour).format('h A');
        },
        convertToCurrentTimezone(time) {
            return dayjs(time).utc('z').local().tz(this.currentTimezone);
        }
    },
    mounted() {
        this.selectedDate = new Date();
        this.viewMode = localStorage.getItem('fcal_calendar_view_mode') || 'month';
    }
};
</script>