<template>
    <div class="fcal_override_date">
        <h2 v-if="title" class="fcal_availability_title">{{ title }}</h2>
        <div class="fcal_override_date_inner">
            <p v-if="!overrides.length">{{ $t('DateOverRides/description') }}</p>
            <div class="fcal_override_dropdown_wrap" @click.self="modal_visible = false">
                <el-button class="fcal_primary_btn2" @click="toggleDateOverRide">
                    {{ $t('Add a date override') }}
                </el-button>

                <el-dialog
                    v-model="modal_visible"
                    :title="$t('Add date overrides')"
                    class="fcal_dialog fcal_override_dropdown"
                >
                    <el-calendar v-model="current_date" ref="calendar">
                        <template #header="{ date }">
                            <span>{{ date }}</span>
                            <el-button-group>
                                <el-button size="small" @click="selectDate('prev-month')">
                                    <el-icon><ArrowLeft /></el-icon>
                                </el-button>
                                <el-button size="small" @click="selectDate('next-month')">
                                    <el-icon><ArrowRight /></el-icon>
                                </el-button>
                            </el-button-group>
                        </template>
                        <template #date-cell="{ data }">
                            <p @click="toggleSelect(data)" :class="{ 'is-selected': current_selects.indexOf(data.day) > -1, 'fcal_date_disabled': isPastDate(data.date) }">
                                {{ data.date.getDate() }}
                                <span v-if="(current_selects.indexOf(data.day) > -1)" class="check-icon"></span>
                            </p>
                        </template>
                    </el-calendar>
                    <div class="fcal_override_calendar_footer">
                        <div v-if="current_selects.length" class="fcal_override_calendar_available_hour">
                            <h3>{{ $t('What hours are you available?') }}</h3>
                            <div class="fcal_weekly_schedules">
                                <DayOverRideConfig :isUnavailable="isUnavailableDate" day_label="" :slots="slots" />
                            </div>
                        </div>

                        <div class="fcal_override_calendar_unavailable_check">
                            <el-checkbox v-model="isUnavailableDate" :label="$t('Mark to Unavailable')" />
                        </div>

                        <div class="fcal_override_calendar_footer_action">
                            <el-button
                                class="fcal_plain_btn"
                                @click="modal_visible = false">
                                {{ $t('Cancel') }}
                            </el-button>
                            <el-button
                                :disabled="!current_selects.length"
                                class="fcal_primary_btn"
                                @click="addOverRides()">
                                {{ $t('Apply') }}
                            </el-button>
                        </div>
                    </div>
                </el-dialog>
            </div>

            <div class="fcal_override_table">
                <table class="fcal_table_compact fcal_table_stripe">
                    <tbody>
                        <tr v-for="item in overrides" style="cursor: pointer;">
                            <td @click="showSlotEdit(item)">
                                <span class="date">{{item.label}}</span>
                            </td>
                            <td @click="showSlotEdit(item)" style="text-align: right;">
                                <ul class="fcal_slots_list">
                                    <li v-for="slot in item.slots">
                                        {{ overriddenDate(slot) }}
                                    </li>
                                </ul>
                            </td>
                            <td class="action">
                                <el-button @click="deleteOverRide(item)" size="small" :icon="DeleteIcon" text></el-button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import { Delete, ArrowRight, ArrowLeft } from '@element-plus/icons-vue';
import DayOverRideConfig from './__DayOverRideConfig.vue';
import each from 'lodash/each';
import { markRaw } from 'vue';


export default {
    name: 'DateOverRides',
    props: ['settings', 'title'],
    emits: ['overridesUpdated'],
    components: {
        ArrowRight,
        ArrowLeft,
        DayOverRideConfig
    },
    data() {
        return {
            current_selects: [],
            modal_visible: false,
            slots: [{
                start: '',
                end: ''
            }],
            DeleteIcon: markRaw(Delete),
            current_date: new Date(),
            existing_dates: [],
            isUnavailableDate: false
        }
    },
    computed: {
        overrides() {
            const overrides = JSON.parse(JSON.stringify(this.settings.date_overrides));
            const formatted = [];
            let firstDay = false;

            let dateGroups = [];

            // group the overrides by consecutive days and if the values are same. The keys are date like 2021-01-01
            each(overrides, (slot, day) => {
                if(!firstDay) {
                    firstDay = day;
                }

                dateGroups.push(day);

                let nextDay = new Date(day);
                nextDay.setDate(nextDay.getDate() + 1);

                nextDay = nextDay.toISOString().slice(0, 10);

                if(JSON.stringify(overrides[day]) === JSON.stringify(overrides[nextDay])) {
                    return;
                }

                let label = this.toDateFormat(day, 'D MMM, YYYY ');

                if(firstDay != day) {
                    label = this.toDateFormat(firstDay, 'D MMM ') + ' - ' + label;
                }

                formatted.push({
                    dates: dateGroups,
                    label: label,
                    slots: overrides[day]
                });
                firstDay = false;
                dateGroups = [];
            });

            return formatted;
        },
        overriddenDate() {
            return (slot) => {
                if (slot.start === "00:00" && slot.end === "00:00") {
                    return this.$t('Unavailable');
                }
                return this.toDateFormat('2022-12-12 ' + slot.start, 'HH:mma') + ' - ' + this.toDateFormat('2022-12-12 ' + slot.end, 'HH:mma');
            }
        },
    },
    methods: {
        showModal() {
            this.slots = [{
                start: '',
                end: ''
            }];
            this.current_selects = [];
            this.existing_dates = [];
            this.isUnavailableDate = false;
            this.current_date = new Date();
            this.modal_visible = true;
        },
        toggleSelect(data) {
            const day = data.day
            if (this.isPastDate(data.date)) {
                return;
            }
            const index = this.current_selects.indexOf(day);
            if (index > -1) {
                this.current_selects.splice(index, 1);
            } else {
                this.current_selects.push(day);
            }
        },
        isPastDate(date) {
            // check if it's yesterday or earlier
            return date < new Date(new Date().setDate(new Date().getDate() - 1));
        },
        selectDate(val) {
            this.$refs.calendar.selectDate(val);
        },
        addOverRides() {
            if (!this.current_selects.length) {
                this.$notify.error(this.$t('Please select a date first'));
                return;
            }

            each(this.existing_dates, (date) => {
                delete this.settings.date_overrides[date];
            });

            each(this.current_selects, (day) => {
                this.settings.date_overrides[day] = JSON.parse(JSON.stringify(this.slots));
            });

            this.settings.date_overrides = Object.keys(this.settings.date_overrides).sort().reduce(
                (obj, key) => {
                    obj[key] = this.settings.date_overrides[key];
                    return obj;
                },
                {}
            );
            this.resetOverRide();
            this.$emit('overridesUpdated');
        },
        toggleDateOverRide() {
            if (!this.modal_visible) {
                this.showModal()
            } else {
                this.modal_visible = false;
            }
        },
        resetOverRide() {
            this.modal_visible = false;
            this.isUnavailableDate = false;
            this.current_selects = [];
            this.slots = [{
                start: '',
                end: ''
            }];
        },
        deleteOverRide(item) {
            each(item.dates, (date) => {
               delete this.settings.date_overrides[date];
            });
        },
        updateUnavailable(item) {
            const [firstSlot] = item.slots;
            if (firstSlot.start === "00:00" && firstSlot.end === "00:00") {
                this.isUnavailableDate = true;
            } else {
                this.isUnavailableDate = false;
            }
        },
        showSlotEdit(item) {
            this.updateUnavailable(item);
            this.current_selects = JSON.parse(JSON.stringify(item.dates));
            this.existing_dates = JSON.parse(JSON.stringify(item.dates));
            this.slots = JSON.parse(JSON.stringify(item.slots));
            this.modal_visible = true;
            this.current_date = this.current_selects[0];
        }
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
