<template>
    <div class="fcal_calendar_view">
        <el-calendar v-model="selectedDate">
            <template #header="{ date }">
                <span class="header_date">{{ date }}</span>
                <el-button-group>
                    <el-button @click="selectDate('prev-month')">
                        <el-icon><ArrowLeftBold /></el-icon>
                    </el-button>
                    <el-button @click="selectDate('today')">
                        {{ $t('Today') }}
                    </el-button>
                    <el-button @click="selectDate('next-month')">
                        <el-icon><ArrowRightBold /></el-icon>
                    </el-button>
                </el-button-group>
            </template>
            <template #date-cell="{ data }">
                <span class="day_number">{{ data.day.split('-')[2] }}</span>
                <template v-if="hasSchedulesForDay(data.day)">
                    <div v-for="schedule in firstThreeSchedules(data.day)" :key="schedule.id" class="fcal_booking_wrap">
                        <span class="booking_color" :style="{ background: schedule.calendar_event?.color_schema }"></span>
                        <el-popover
                            :width="420"
                            trigger="click"
                            placement="right"
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
                                    <el-button text>{{ dayjs(data.day).format('ddd, D MMMM') }}</el-button>
                                    <el-icon @click="closeAllSchedules(data.day)"><Close /></el-icon>
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
            selectedDate: null,
            selectedSchedule: null,
            selectedAllSchedules: null,
            selectedDetailsSchedule: null,
            currentSchedules: []
        };
    },
    watch: {
        selectedDate(newVal, oldVal) {
            const newDate = this.toCurrentTimezone(newVal, 'YYYY-MM-DD');
            const oldDate = this.toCurrentTimezone(oldVal, 'YYYY-MM-DD');
            if (newDate !== oldDate) {
                this.$emit('dateUpdated', newDate);
            }
        },
        schedules: {
            handler(newSchedules) {
                this.currentSchedules = newSchedules;
            },
            deep: true
        }
    },
    computed: {
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
                return this.formattedSchedules[date].length > 3;
            }
        },
        formattedSchedules() {
            const events = {};
            each(this.currentSchedules, (schedule) => {
                const date = this.toCurrentTimezone(schedule.start_time, 'YYYY-MM-DD');
                events[date] = events[date] || [];
                events[date].push(schedule);
            });
            return events;
        },
        firstThreeSchedules() {
            return (date) => {
                return this.formattedSchedules[date].slice(0, 3);
            }
        }
    },
    methods: {
        formatTime(time) {
            return dayjs(time).format('HH:mm');
        },
        htmlTitle(htmlTitle) {
            const div = document.createElement('div');
            div.innerHTML = htmlTitle;
            return div.textContent || div.innerText || '';
        },
        hasSchedulesForDay(date) {
            const hasSchedules = this.formattedSchedules[date]?.length;
            const currentMonth = dayjs(this.selectedDate).format('YYYY-MM');
            const scheduleMonth = dayjs(date).format('YYYY-MM');
            return hasSchedules && currentMonth === scheduleMonth;
        },
        selectDate(action) {
            const currentDate = new Date(this.selectedDate);
            if (action === 'prev-month') {
                currentDate.setMonth(currentDate.getMonth() - 1);
            } else if (action === 'next-month') {
                currentDate.setMonth(currentDate.getMonth() + 1);
            } else {
                currentDate.setTime(Date.now());
            }
            this.selectedDate = currentDate;
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
        formatSchedules(schedules) {
            const events = {};
            each(schedules, (schedule) => {
                const date = this.toCurrentTimezone(schedule.start_time, 'YYYY-MM-DD');
                events[date] = events[date] || [];
                events[date].push(schedule);
            });
            return events;
        }
    },
    mounted() {
        this.selectedDate = new Date();
    }
};
</script>