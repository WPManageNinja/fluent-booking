<template>
    <div class="fcal_create_calendar_body">
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
                                    <el-select v-model="calendar_event.settings.buffer_time_before" :placeholder="$t('Select')"
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
                                    <el-select v-model="calendar_event.settings.buffer_time_after" :placeholder="$t('Select')"
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
                                    <SchedulingConditions :settings="calendar_event.settings"/>
                                </div>
                                <div class="fcal_slot_condition_time">
                                    <span class="sub-label">{{ $t("Time-slot intervals") }}</span>
                                    <el-select v-model="calendar_event.settings.slot_interval" :placeholder="$t('Select')"
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
                                <el-switch v-model="calendar_event.settings.booking_frequency"/>
                            </div>
                        </div>
                        <div class="fcal_booking_limit_child_card" v-if="calendar_event.settings.booking_frequency">
                            <div v-for="(frequency, index) in booking_frequencies" :key="index" class="fcal_inline_items">
                                <el-input type="text" v-model="frequency.value" @input="validateInput(frequency)"/>
                                <el-select v-model="frequency.unit" @change="validateInput(frequency)" :placeholder="$t('Select Unit')" popper-class="fcal_select">
                                    <el-option value="per_day" :label="$t('Per Day')"></el-option>
                                    <el-option value="per_week" :label="$t('Per Week')"></el-option>
                                </el-select>
                                <el-link v-if="isRemovable(booking_frequencies)" type="danger" :title="$t('Remove')"
                                    :icon="CloseBoldIcon"
                                    :underline="false"
                                    @click="removeBookingFrequency(index)">
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
                                <el-switch v-model="calendar_event.settings.booking_duration"/>
                            </div>
                        </div>
                        <div class="fcal_booking_limit_child_card" v-if="calendar_event.settings.booking_duration">
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

                </el-form>
            </div>
            <div class="fcal_create_calendar_form_footer">
                <SaveButton :saving="saving" :label="$t('Save Changes')" @click="saveSettings"/>
            </div>
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
import SaveButton from "@/Components/Buttons/SaveButton";
import { Clock, CloseBold } from '@element-plus/icons-vue';
import { markRaw } from "vue";

export default {
    name: '_LimitSettings',
    components: {
        SchedulingConditions,
        DateOverRides,
        WeeklySchedules,
        ExistingSchedule,
        ScheduleIcon,
        TimezoneIcon,
        SaveButton,
        CloseBold,
        Clock
    },
    props: ['calendar_event'],
    data() {
        return {
            saving: false,
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
            const maxBookPerDay = this.calendar_event.settings.max_book_per_day;
            if (maxBookPerDay && maxBookPerDay < 1) {
                this.calendar_event.settings.max_book_per_day = 1;
            } else if (maxBookPerDay > 100){
                this.calendar_event.settings.max_book_per_day = 100;
            }
        },
        validateInput(frequency) {
            if (isNaN(frequency.value) || frequency.value < 1) {
                frequency.value = '';
            }
        },
        saveSettings() {
            this.saving = true;
            this.$post('calendars/' + this.calendar_event.calendar.id + '/events/' + this.calendar_event.id + '/limits', {
                settings: this.calendar_event.settings
            })
                .then(response => {
                    this.$handleSuccess(response);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        }
    }
}
</script>
