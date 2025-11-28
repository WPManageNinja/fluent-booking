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
                                    <el-select v-model="settings.buffer_time_before" :placeholder="$t('Select')"
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
                                    <el-select v-model="settings.buffer_time_after" :placeholder="$t('Select')"
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
                                    <SchedulingConditions :settings="settings"/>
                                </div>
                                <div class="fcal_slot_condition_time">
                                    <span class="sub-label">{{ $t("Time-slot intervals") }}</span>
                                    <el-select v-model="settings.slot_interval" :placeholder="$t('Select')"
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

                    <el-form-item v-if="showBookingLimit">
                        <div class="fcal_event_card fcal_event_card_wrap">
                            <div class="fcal_event_card_header">
                                <div class="card_contents">
                                    <span class="sub-label card-title">{{ $t("LimitSettings/booking_frequency") }}</span>
                                    <span>{{ $t("LimitSettings/booking_frequency_description") }}</span>
                                </div>
                                <div class="card_action">
                                    <el-switch v-model="settings.booking_frequency.enabled"/>
                                </div>
                            </div>
                            <div class="fcal_event_child_card" v-if="settings.booking_frequency.enabled">
                                <div v-for="(frequency, index) in settings.booking_frequency.limits" :key="index" class="fcal_inline_items">
                                    <el-input class="fcal_booking_duration" type="number" v-model="frequency.value" @input="validateInput(frequency)">
                                        <template #append>{{ $t('Bookings') }}</template>
                                    </el-input>
                                    <el-select v-model="frequency.unit" :placeholder="$t('Select Unit')" popper-class="fcal_select">
                                        <el-option :disabled="isDayExist(settings.booking_frequency)" value="per_day" :label="$t('Per day')"></el-option>
                                        <el-option :disabled="isWeekExist(settings.booking_frequency)" value="per_week" :label="$t('Per week')"></el-option>
                                        <el-option :disabled="isMonthExist(settings.booking_frequency)" value="per_month" :label="$t('Per month')"></el-option>
                                    </el-select>
                                    <el-link v-if="isRemovable(settings.booking_frequency)" type="danger" :title="$t('Remove')"
                                             :icon="CloseBoldIcon"
                                             :underline="false"
                                             @click="removeBookingFrequency(index)">
                                    </el-link>
                                </div>
                                <el-link v-if="isInsertable(settings.booking_frequency)" type="primary" :underline="false" @click="insertBookingFrequency">
                                    {{ $t('Add Another Limit') }}
                                </el-link>
                            </div>
                        </div>
                    </el-form-item>

                    <el-form-item v-if="showBookingLimit">
                        <div class="fcal_event_card fcal_event_card_wrap">
                            <div class="fcal_event_card_header">
                                <div class="card_contents">
                                    <span class="sub-label card-title">{{ $t("LimitSettings/booking_duration") }}</span>
                                    <span>{{ $t("LimitSettings/booking_duration_description") }}</span>
                                </div>
                                <div class="card_action">
                                    <el-switch v-model="settings.booking_duration.enabled"/>
                                </div>
                            </div>
                            <div class="fcal_event_child_card" v-if="settings.booking_duration.enabled">
                                <div v-for="(duration, index) in settings.booking_duration.limits" :key="index" class="fcal_inline_items">
                                    <el-input class="fcal_booking_duration" type="number" v-model="duration.value" @input="validateInput(duration)">
                                        <template #append>{{ $t('Minutes') }}</template>
                                    </el-input>
                                    <el-select v-model="duration.unit" :placeholder="$t('Select Unit')" popper-class="fcal_select">
                                        <el-option :disabled="isDayExist(settings.booking_duration)" value="per_day" :label="$t('Per day')"></el-option>
                                        <el-option :disabled="isWeekExist(settings.booking_duration)" value="per_week" :label="$t('Per week')"></el-option>
                                        <el-option :disabled="isMonthExist(settings.booking_duration)" value="per_month" :label="$t('Per month')"></el-option>
                                    </el-select>
                                    <el-link v-if="isRemovable(settings.booking_duration)" type="danger" :title="$t('Remove')"
                                             :icon="CloseBoldIcon"
                                             :underline="false"
                                             @click="removeBookingDuration(index)">
                                    </el-link>
                                </div>
                                <el-link v-if="isInsertable(settings.booking_duration)" type="primary" :underline="false" @click="insertBookingDuration">
                                    {{ $t('Add Another Limit') }}
                                </el-link>
                            </div>
                        </div>
                    </el-form-item>

                    <el-form-item>
                        <div class="fcal_event_card fcal_event_card_wrap">
                            <div class="fcal_event_card_header">
                                <div class="card_contents">
                                    <span class="sub-label card-title">{{ $t("LimitSettings/lock_timezone") }}</span>
                                    <span>{{ $t("LimitSettings/lock_timezone_description") }}</span>
                                </div>
                                <div class="card_action">
                                    <el-switch v-model="settings.lock_timezone.enabled"/>
                                </div>
                            </div>
                            <div class="fcal_event_child_card" v-if="settings.lock_timezone.enabled">
                                <TimeZoneSelector v-model="settings.lock_timezone.timezone"/>
                            </div>
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
import SchedulingConditions from "./__SchedulingConditions";
import TimeZoneSelector from "../parts/TimeZoneSelector.vue";
import SaveButton from "@/Components/Buttons/SaveButton";
import { Clock, CloseBold } from '@element-plus/icons-vue';
import { markRaw } from "vue";

export default {
    name: '_LimitSettings',
    components: {
        SchedulingConditions,
        TimeZoneSelector,
        SaveButton,
        CloseBold,
        Clock
    },
    props: ['calendar_event'],
    data() {
        return {
            saving: false,
            settings: this.calendar_event.settings,
            bufferTimes: this.appVars.buffer_times,
            slotIntervals: this.appVars.slot_intervals,
            CloseBoldIcon: markRaw(CloseBold),
        }
    },
    computed: {
        isRemovable() {
            return (settings) => {
                return settings.limits.length > 1;
            }
        },
        isInsertable() {
            return (settings) => {
                return settings.limits.length < 3;
            }
        },
        showBookingLimit() {
            return !['single_event', 'group_event'].includes(this.calendar_event.event_type);
        }
    },
    methods: {
        isDayExist(settings) {
            return settings.limits.some(limit => limit.unit == 'per_day');
        },
        isWeekExist(settings) {
            return settings.limits.some(limit => limit.unit == 'per_week');
        },
        isMonthExist(settings) {
            return settings.limits.some(limit => limit.unit == 'per_month');
        },
        insertBookingFrequency() {
            const dayExist = this.isDayExist(this.settings.booking_frequency);
            const weekExist = this.isWeekExist(this.settings.booking_frequency);
            const unitValue = dayExist ? (weekExist ? 'per_month' : 'per_week') : 'per_day';
            this.settings.booking_frequency.limits.push({
                unit: unitValue,
                value: 5
            });
        },
        removeBookingFrequency(index) {
            this.settings.booking_frequency.limits.splice(index, 1);
        },
        insertBookingDuration() {
            const dayExist = this.isDayExist(this.settings.booking_duration);
            const weekExist = this.isWeekExist(this.settings.booking_duration);
            const unitValue = dayExist ? (weekExist ? 'per_month' : 'per_week') : 'per_day';
            this.settings.booking_duration.limits.push({
                unit: unitValue,
                value: 120
            });
        },
        removeBookingDuration(index) {
            this.settings.booking_duration.limits.splice(index, 1);
        },
        validateInput(frequency) {
            if (isNaN(frequency.value) || frequency.value < 1) {
                frequency.value = '';
            } else if (frequency.value > 10000) {
                frequency.value = 10000;
            }
        },
        saveSettings() {
            this.saving = true;
            this.$post('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/limits', {
                calendar_id: this.calendar_event.calendar_id,
                settings: this.settings
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
