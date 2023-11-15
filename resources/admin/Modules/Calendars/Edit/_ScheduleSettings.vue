<template>
    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2> <ScheduleIcon/> {{ $t('Availability') }} </h2>
        </div>
        <div class="fcal_create_calendar_form_body">
            <el-form label-position="top">
                <el-form-item :label="$t('ScheduleSettings/availability_type_label')">
                    <el-tabs v-model="slot.availability_type">
                        <el-tab-pane :label="$t('Use an Existing Schedule')" name="existing_schedule">
                            <div class="fcal_availability_body">
                                <h4>{{ $t('Which Schedule Do You Want to Use ?') }}</h4>
                                <el-select
                                    v-model="slot.availability_id"
                                    :placeholder="$t('Select Schedule')"
                                    popper-class="fcal_select"
                                    class="fcal_timezone"
                                    :no-match-text="$t('No Data match')"
                                    :no-data-text="$t('No Data')"
                                >
                                    <el-option-group
                                            v-for="schedulesHosts in slot.settings.schedule_options"
                                            :key="schedulesHosts.hostName"
                                            :label="schedulesHosts.hostName">
                                        <el-option
                                                v-for="schedule in schedulesHosts.schedules"
                                                :key="schedule.value"
                                                :label="schedule.label"
                                                :value="schedule.value">
                                        </el-option>
                                    </el-option-group>
                                </el-select>
                                <ExistingSchedule
                                    :existing_schedules="selectedSchedule"
                                    :timezone="slot.calendar.author_timezone"
                                    :availability_id="slot.availability_id"
                                />

                            </div>
                        </el-tab-pane>
                        <el-tab-pane :label="$t('Set Custom Hours')" name="custom">
                            <div class="fcal_availability_body">
                                <div class="fcal_timezone_text">
                                    <el-icon><TimezoneIcon/></el-icon>
                                    <p>{{ slot.calendar.author_timezone }}</p>
                                </div>
                                <div class="fcal_availability_setting">
                                    <WeeklySchedules
                                        :weekly_schedules="slot.settings.weekly_schedules"
                                        :title="$t('Weekly Hours')"
                                    />
                                    <date-over-rides
                                        :settings="slot.settings"
                                        :title="$t('Add date overrides')"
                                    />

                                </div>
                            </div>
                        </el-tab-pane>
                    </el-tabs>
                </el-form-item>

                <el-form-item :label="$t('Scheduling conditions')" class="fcal_override_scheduling_condition_wrap">
                    <span class="sub-label">{{ $t("Invitees can't schedule within...") }}</span>
                    <SchedulingConditions :settings="slot.settings"/>
                </el-form-item>

                <el-form-item :label="$t('ScheduleSettings/before/after_event_label')">
                    <div class="fcal_buffer_time_wrap">
                        <div class="fcal_buffer_time_before">
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
                        <div class="fcal_buffer_time_after">
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
export default {
    name: '_ScheduleSettings',
    components: {
    SchedulingConditions,
    DateOverRides,
    WeeklySchedules,
    ExistingSchedule,
    ScheduleIcon,
    TimezoneIcon
},
    props: {
        slot: {
            type: Object,
            default: {
                title: '',
                description: '',
                duration: '',
                calendar: {
                    author_timezone: ''
                },
            }
        }
    },
    data() {
        return {
            bufferTimes: this.appVars.buffer_times
        }
    },
    computed: {
        selectedSchedule() {
            const selectedAvailability = this.slot.settings.available_schedules.find(schedule => schedule.id === this.slot.availability_id);
            return selectedAvailability?.settings || [];
        }
    },
    methods: {
        disabledDate(time) {
            return (time.getTime() + 86400000) <= Date.now();
        }
    },
    mounted() {
        this.slot.availability_id ??= this.slot.settings.available_schedules[0].id;
        this.slot.availability_id = parseInt(this.slot.availability_id);
    }
}
</script>
