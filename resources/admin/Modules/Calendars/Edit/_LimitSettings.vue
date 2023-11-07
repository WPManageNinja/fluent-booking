<template>
    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2> <el-icon><Clock /></el-icon> {{ $t('Limits') }} </h2>
        </div>
        <div class="fcal_create_calendar_form_body">
            <el-form label-position="top">
                <el-form-item>
                    <div class="fcal_time_limit_wrap">                        
                        <div class="fcal_buffer_time_wrap">
                            <div class="fcal_buffer_time">
                                <span class="sub-label">{{ $t('Before Event') }}</span>
                                <el-select v-model="slot.settings.buffer_time_before" :placeholder="$t('Select')"
                                        :no-match-text="$t('No Data match')"
                                        :no-data-text="$t('No Data')" popper-class="fcal_select">
                                    <el-option
                                        v-for="time in bufferTimes"
                                        :key="time.value"
                                        :label="time.label"
                                        :value="time.value"
                                    />
                                </el-select>
                            </div>
                            <div class="fcal_buffer_time">
                                <span class="sub-label">{{ $t('After Event') }}</span>
                                <el-select v-model="slot.settings.buffer_time_after" :placeholder="$t('Select')"
                                        :no-match-text="$t('No Data match')"
                                        :no-data-text="$t('No Data')" popper-class="fcal_select">
                                    <el-option
                                        v-for="time in bufferTimes"
                                        :key="time.value"
                                        :label="time.label"
                                        :value="time.value"
                                    />
                                </el-select>
                            </div>
                        </div>
                        <div class="fcal_slot_condition_wrap">
                            <div class="fcal_slot_condition_time">
                                <span class="sub-label">{{ $t("Minimum Notice") }}</span>
                                <SchedulingConditions :settings="slot.settings"/>
                            </div>
                            <div class="fcal_slot_condition_time">
                                <span class="sub-label">{{ $t("Time-slot intervals") }}</span>
                                <el-select v-model="slot.settings.slot_interval" :placeholder="$t('Select')"
                                    :no-match-text="$t('No Data match')"
                                    :no-data-text="$t('No Data')" popper-class="fcal_select">
                                    <el-option
                                        v-for="time in slotIntervals"
                                        :key="time.value"
                                        :label="time.label"
                                        :value="time.value"
                                    />
                                </el-select>
                            </div>
                        </div>
                    </div>
                </el-form-item>

                <el-form-item>
                    <div class="fcal_booking_limit_card">
                        <div class="card_contents">
                            <span class="sub-label card-title">{{ $t("LimitSettings/booking_frequency") }}</span>
                            <span>{{ $t("LimitSettings/booking_frequency_description") }}</span>
                        </div>
                        <div class="card_action">
                            <el-switch v-model="slot.settings.booking_frequency"/>
                        </div>
                    </div>
                    <div class="fcal_booking_limit_child_card" v-if="slot.settings.booking_frequency">
                        <div v-for="(frequency, index) in booking_frequencies" :key="index" class="fcal_inline_items">
                            <el-input type="text" v-model="frequency.value" @input="validateInput(frequency)"/>
                            <el-select v-model="frequency.unit" @change="validateInput(frequency)" :placeholder="$t('Select Unit')" popper-class="fcal_select">
                                <el-option value="per_day" :label="$t('Per Day')"></el-option>
                                <el-option value="per_week" :label="$t('Per Week')"></el-option>
                            </el-select>
                            <el-link v-if="isRemovable(booking_frequencies)" type="danger" :title="$t('Remove')"
                                :icon="CloseBoldIcon"
                                :underline="false"
                                @click="removeBookingFequency(index)">
                            </el-link>
                        </div>
                        <el-link type="primary" :underline="false" @click="addBookingFrequency">
                            {{ $t('Add Another Limit') }}
                        </el-link>
                    </div>
                </el-form-item>

                <el-form-item>
                    <div class="fcal_booking_limit_card">
                        <div class="card_contents">
                            <span class="sub-label card-title">{{ $t("LimitSettings/booking_duration") }}</span>
                            <span>{{ $t("LimitSettings/booking_duration_description") }}</span>
                        </div>
                        <div class="card_action">
                            <el-switch v-model="slot.settings.booking_duration"/>
                        </div>
                    </div>
                    <div class="fcal_booking_limit_child_card" v-if="slot.settings.booking_duration">
                        <div v-for="(duration, index) in booking_durations" :key="index" class="fcal_inline_items">
                            <el-input type="text" v-model="duration.value" @input="validateInput(duration)"/>
                            <el-select v-model="duration.unit" @change="validateInput(duration)" :placeholder="$t('Select Unit')" popper-class="fcal_select">
                                <el-option value="per_day" :label="$t('Per Day')"></el-option>
                                <el-option value="per_week" :label="$t('Per Week')"></el-option>
                            </el-select>
                            <el-link v-if="isRemovable(booking_durations)" type="danger" :title="$t('Remove')"
                                :icon="CloseBoldIcon"
                                :underline="false"
                                @click="removeBookingDuration(index)">
                            </el-link>
                        </div>
                        <el-link type="primary" :underline="false" @click="addBookingDuration">
                            {{ $t('Add Another Limit') }}
                        </el-link>
                    </div>
                </el-form-item>

                <el-form-item>
                    <div class="fcal_booking_limit_card">
                        <div class="card_contents">
                            <span class="sub-label card-title">{{ $t("LimitSettings/future_booking") }}</span>
                            <span>{{ $t("LimitSettings/future_booking_description") }}</span>
                        </div>
                        <div class="card_action">
                            <el-switch v-model="slot.settings.future_booking"/>
                        </div>
                    </div>
                    <div class="fcal_booking_limit_child_card" v-if="slot.settings.future_booking">
                        <el-radio-group v-model="slot.settings.range_type" class="fcal_date_range_radio">
                            <div class="fcal_date_range_radio_item">
                                <el-radio label="range_days" size="large">{{ $t('Within future days') }}</el-radio>

                                <div v-if="slot.settings.range_type == 'range_days'" class="fcal_date_range_radio_condition">
                                    <el-input v-model="slot.settings.range_days" type="number">
                                        <template #append>{{ $t('Days into the future') }}</template>
                                    </el-input>
                                </div>
                            </div>
                            <div class="fcal_date_range_radio_item">
                                <el-radio label="range_date_between" size="large">{{ $t('Within a date range') }}</el-radio>

                                <div v-if="slot.settings.range_type == 'range_date_between'" class="fcal_date_range_radio_condition">
                                    <el-date-picker
                                        v-model="slot.settings.range_date_between"
                                        type="daterange"
                                        :disabled-date="disabledDate"
                                        value-format="YYYY-MM-DD"
                                        :range-separator="$t('To')"
                                        :start-placeholder="$t('Start date')"
                                        :end-placeholder="$t('End date')"
                                        popper-class="fcal_daterange_popover"
                                    />
                                </div>
                            </div>
                        </el-radio-group>
                    </div>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script>
import WeeklySchedules from "../parts/WeeklySchedules";
import DateOverRides from "./_DateOverRides";
import SchedulingConditions from "./__SchedulingConditions";
import ExistingSchedule from './_ExistingSchedule';
import ScheduleIcon from "../../../Components/Icons/ScheduleIcon";
import TimezoneIcon from "../../../Components/Icons/TimezoneIcon";
import { Clock, CloseBold } from '@element-plus/icons-vue';
import { markRaw } from "vue";

export default {
    name: '_ScheduleSettings',
    components: {
        SchedulingConditions,
        DateOverRides,
        WeeklySchedules,
        ExistingSchedule,
        ScheduleIcon,
        TimezoneIcon,
        CloseBold,
        Clock
    },
    props: ['slot'],
    data() {
        return {
            bufferTimes: this.appVars.buffer_times,
            slotIntervals: this.appVars.slot_intervals,
            CloseBoldIcon: markRaw(CloseBold),
            booking_frequencies: [
                {
                    value: 1,
                    unit: 'per_day'
                }
            ],
            booking_durations: [
                {
                    value: 1,
                    unit: 'per_day'
                }
            ]
        }
    },
    computed: {
        isRemovable() {
            return (data) => {
                return data.length > 1;
            }
        }
    },
    methods: {
        addBookingFrequency() {
            this.booking_frequencies.push({
                value: 1,
                unit: 'per_week'
            });
        },
        removeBookingFrequency(index) {
            this.booking_frequencies.splice(index, 1);
        },
        addBookingDuration() {
            this.booking_durations.push({
                value: 1,
                unit: 'per_week'
            });
        },
        removeBookingDuration(index) {
            this.booking_durations.splice(index, 1);
        },
        validateMaxBookInput() {
            const maxBookPerDay = this.slot.settings.max_book_per_day;
            if (maxBookPerDay && maxBookPerDay < 1) {
                this.slot.settings.max_book_per_day = 1;
            } else if (maxBookPerDay > 100){
                this.slot.settings.max_book_per_day = 100;
            }
        },
        validateInput(frequency) {
            if (isNaN(frequency.value) || frequency.value < 1) {
                frequency.value = '';
            }
        }
    }
}
</script>
