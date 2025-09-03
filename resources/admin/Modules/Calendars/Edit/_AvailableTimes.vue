<template>
    <div :class="['fcal_event_availability', { 'date_field': !showAddTime }]">
        <h2 v-if="title" class="fcal_availability_title">{{ title }}</h2>
        <div>
            <p v-if="showAddTime">{{ $t('AvailableTimes/description') }}</p>
            <div class="fcal_override_dropdown_wrap" @click.self="modalVisible = false">
                <el-button v-if="showAddTime" class="fcal_primary_btn2" @click="toggleAvailableTimes">
                    {{ $t('Add Available Times') }}
                </el-button>

                <el-dialog
                    v-model="modalVisible"
                    :title="$t('Add Available Times')"
                    class="fcal_dialog fcal_override_dropdown fcal_times">
                    <div class="fcal_left_part">
                        <el-calendar v-model="currentDate" ref="calendar">
                            <template #header="{ date }">
                                <span>{{ date }}</span>
                                <el-button-group>
                                    <el-button size="small" :disabled="isPrevDisabled()" @click="prevMonth">
                                        <el-icon><ArrowLeft /></el-icon>
                                    </el-button>
                                    <el-button size="small" @click="nextMonth">
                                        <el-icon><ArrowRight /></el-icon>
                                    </el-button>
                                </el-button-group>
                            </template>
                            <template v-if="!loading" #date-cell="{ data }">
                                <p
                                    @click="toggleSelectDate(data)"
                                    :class="{'is-selected': isAlreadySelected(data.day), 'fcal_date_disabled': isInvalidDate(data)}">
                                    {{ data.date.getDate() }}
                                    <span v-if="isDateSelected(data.day)" class="check-icon"></span>
                                </p>
                            </template>
                        </el-calendar>
                        <el-divider/>
                        <div class="fcal_action">
                            <el-button
                                class="fcal_primary_btn"
                                @click="addAvailableTimes()">
                                {{ $t('Apply') }}
                            </el-button>
                            <el-button
                                class="fcal_plain_btn"
                                @click="modalVisible = false">
                                {{ $t('Cancel') }}
                            </el-button>
                        </div>
                    </div>
                    <div class="fcal_override_calendar_footer">
                        <div class="fcal_override_calendar_available_hour">
                            <div class="fcal_available_time_header">
                                <h3>{{ $t('Which slots are you available?') }}</h3>
                                <el-checkbox v-if="!isGroupEvent" v-model="selectAll[selectedDate]" @change="toggleSelectAll">
                                    {{ $t('Select All') }}
                                </el-checkbox>
                            </div>
                            <div v-if="!loading">
                                <div v-if="daySlots.length" class="fcal_weekly_schedules">
                                    <div class="fcal_available_time_picker">
                                        <div class="fcal_available_times">
                                            <div
                                                v-for="day in daySlots"
                                                :key="day.start"
                                                @click="toggleSelectSlot(day)"
                                                :class="{'fcal_time': true, 'fcal_spot_selected': isSlotSelected(day)}">
                                                <div
                                                    role="button"
                                                    aria-label="Select Time"
                                                    class="fcal_spot_name">
                                                    <div>{{ formattedSlot(day) }}</div>
                                                    <el-icon class="fcal_selected_time_icon" v-if="isSlotSelected(day)"><Check /></el-icon>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <p>{{ $t('Please select a date first') }}</p>
                                </div>
                            </div>
                            <el-skeleton v-else animated :rows="6"/>
                        </div>
                    </div>
                </el-dialog>
            </div>

            <div class="fcal_availability_times">
                <table class="fcal_table_stripe">
                    <tbody>
                        <tr v-for="item in availableTimes">
                            <td @click="showAvailableTime(item.date)">
                                <span class="date">{{item.label}}</span>
                            </td>
                            <td @click="showAvailableTime(item.date)">
                                <ul class="fcal_slots_list">
                                    <li v-for="(slot, indx) in item.slots">
                                        {{ slot.start }}<span v-if="!isLastSlot(item.slots, indx)">,</span>
                                    </li>
                                </ul>
                            </td>
                            <td class="action">
                                <el-button v-if="isSingleEvent"
                                    @click="deleteAvailableTime(item)" size="small" :icon="DeleteIcon" text>
                                </el-button>
                                <el-button v-else
                                    @click="showAvailableTime(item.date)" size="small" :icon="EditIcon" text>
                                </el-button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import { ArrowRight, ArrowLeft, Delete, Check, Edit } from '@element-plus/icons-vue';
import { markRaw } from "vue";

export default {
    name: 'AvailableTimes',
    props: ['title', 'calendar_event', 'settings'],
    emits: ['saveSettings'],
    components: {
        ArrowRight,
        ArrowLeft,
        Check
    },
    data() {
        return {
            loading: false,
            daySlots: [],
            selectedSlots: [],
            modalVisible: false,
            availableSlots: [],
            selectAll: [],
            selectedDate: null,
            minLookupDate: null,
            currentDate: new Date(),
            eventYear: new Date().getFullYear(),
            eventMonth: new Date().getMonth(),
            calendarId: this.calendar_event?.calendar_id,
            EditIcon: markRaw(Edit),
            DeleteIcon: markRaw(Delete)
        }
    },
    watch: {
        selectedDate(newDate) {
            this.updateSelectAll(newDate);
        }
    },
    computed: {
        isSingleEvent() {
            return this.calendar_event.event_type === 'single_event';
        },
        isGroupEvent() {
            return this.calendar_event.event_type === 'group_event';
        },
        showAddTime() {
            return this.isSingleEvent || (this.isGroupEvent && !Object.keys(this.settings.available_times).length);
        },
        isLastSlot() {
            return (slots, indx) => {
                return indx === slots.length - 1;
            }
        },
        formattedSlot() {
            return (slot) => {
                return this.toDateFormat(slot.start, 'HH:mm');
            }
        },
        availableTimes() {
            return Object.entries(this.settings.available_times).map(([date, times]) => {
                return {
                    label: this.toDateFormat(date, 'D MMM, YYYY'),
                    date: date,
                    slots: times.map((time) => {
                        return {
                            start: this.toDateFormat('2022-12-12 ' + time, 'hh:mma')
                        }
                    })
                }
            });
        }
    },
    methods: {
        showModal() {
            this.selectedDate = this.todayDate();
            this.modalVisible = true;
        },
        showAvailableTime(date) {
            this.selectedDate = date;
            this.modalVisible = true;
            this.updateDaySlots();
        },
        toggleSelectAll() {
            if (this.selectAll[this.selectedDate]) {
                this.selectAllSlot();
            } else {
                this.resetAllSlot();
            }
        },
        selectAllSlot() {
            this.selectedSlots[this.selectedDate] = this.daySlots.map((slot) => this.toDateFormat(slot.start, 'HH:mm'));
        },
        resetAllSlot() {
            this.selectedSlots[this.selectedDate] = [];
        },
        updateSelectAll(date) {
            this.selectAll[date] = this.daySlots.length && this.selectedSlots[date]?.length === this.daySlots.length;
        },
        isAlreadySelected(day) {
            return this.isDateSelected(day) || (this.selectedSlots[day] && this.selectedSlots[day].length);
        },
        isDateSelected(day) {
            return this.selectedDate == day;
        },
        toggleSelectDate(data) {
            if (this.isInvalidDate(data)) {
                return;
            }
            this.selectedDate = data.day;
            this.updateDaySlots();
        },
        isSlotSelected(slot) {
            const slotDate = this.toDateFormat(slot.start, 'YYYY-MM-DD');
            const slotStart = this.toDateFormat(slot.start, 'HH:mm');
            return this.selectedSlots[slotDate]?.includes(slotStart);
        },
        toggleSelectSlot(slot) {
            const slotDate = this.toDateFormat(slot.start, 'YYYY-MM-DD');
            const slotStart = this.toDateFormat(slot.start, 'HH:mm');
            if (this.isGroupEvent) {
                this.selectedSlots = [];
            }
            let currentSelected = this.selectedSlots[slotDate] ?? [];
            if (currentSelected.includes(slotStart)) {
                currentSelected = currentSelected.filter((item) => item !== slotStart);
            } else {
                currentSelected.push(slotStart);
            }
            this.selectedSlots[slotDate] = currentSelected;
        },
        updateCurrentSlots() {
            Object.entries(this.settings.available_times).forEach(([date, times]) => {
                this.selectedSlots[date] = times;
            });
        },
        isInvalidDate(data) {
            return this.isPastDate(data.date) || !this.availableSlots[data.day];
        },
        isPastDate(date) {
            return date < new Date(new Date().setDate(new Date().getDate() - 1));
        },
        selectMonth(monthValue) {
            this.$refs.calendar.selectDate(monthValue);
        },
        todayDate() {
            return this.toDateFormat(new Date(), 'YYYY-MM-DD');
        },
        addAvailableTimes() {
            if (!Object.keys(this.selectedSlots).length) {
                return;
            }
            const newAvailableTimes = {};
            Object.entries(this.selectedSlots).forEach(([date, times]) => {
                if (times.length) {
                    newAvailableTimes[date] = times;
                }
            });
            // Sort available times by date
            const sortedAvailableTimes = Object.keys(newAvailableTimes).sort().reduce(
                (obj, key) => {
                    obj[key] = newAvailableTimes[key];
                    return obj;
                },{}
            );

            this.settings.available_times = sortedAvailableTimes;
            this.$emit('saveSettings');
            this.resetAvailableTime();
        },
        deleteAvailableTime(item) {
            delete this.settings.available_times[item.date];
            this.selectedSlots[item.date] = [];
            this.updateSelectedDate();
        },
        updateSelectedDate() {
            this.selectedDate = Object.keys(this.availableSlots)[0] ?? this.todayDate();
        },
        updateDaySlots() {
            this.daySlots = this.availableSlots[this.selectedDate] ?? [];
        },
        toggleAvailableTimes() {
            if (!this.modalVisible) {
                this.showModal()
            } else {
                this.modalVisible = false;
            }
        },
        resetAvailableTime() {
            this.selectedDate = null;
            this.modalVisible = false;
        },
        isPrevDisabled() {
            if (!this.minLookupDate) {
                return false;
            }
            const firstDayOfPrevMonth = new Date(this.eventYear, this.eventMonth, 1);
            const minLookupDate = new Date(this.minLookupDate);
            return firstDayOfPrevMonth.getTime() < minLookupDate.getTime();
        },
        nextMonth() {
            this.eventMonth = (this.eventMonth + 1) % 12;
            this.eventYear += this.eventMonth === 0 ? 1 : 0;
            this.selectMonth('next-month');
            this.loadAvailableSlots();
        },
        prevMonth() {
            const prevMonth = (this.eventMonth === 0) ? 11 : (this.eventMonth - 1);
            const prevYear = (this.eventMonth === 0) ? (this.eventYear - 1) : this.eventYear;
            this.eventMonth = prevMonth;
            this.eventYear = prevYear;
            this.selectMonth('prev-month');
            this.loadAvailableSlots();
        },
        loadAvailableSlots() {
            this.loading = true;
            const formattedDate = this.toDateFormat(`${this.eventYear}-${this.eventMonth+1}-01`, 'YYYY-MM-DD');
            this.$get('calendars/' + this.calendarId + '/events/' + this.calendar_event.id + '/event-available-slots', {
                calendar_id : this.calendarId,
                start_date: formattedDate
            })
                .then(response => {
                    this.availableSlots = response.available_slots;
                    this.minLookupDate = response.min_lookup_date;
                    this.updateSelectedDate();
                    this.updateDaySlots();
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    mounted() {
        this.loadAvailableSlots();
        this.updateCurrentSlots();
        this.updateSelectAll(this.selectedDate);
    }
}
</script>

<style lang="scss">
.fcal_cal_wrapper {
    max-width: 100%;
    width: 500px;
    .el-calendar-table .el-calendar-day {
        height: 40px;
    }
}

.el-calendar-day:has(>.fcal_date_disabled) {
    color: #b8b6b6;
    cursor: not-allowed !important;
}
</style>
