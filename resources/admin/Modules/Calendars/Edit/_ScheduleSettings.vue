<template>
    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2> <ScheduleIcon/> Schedule Settings </h2>
        </div>
        <div class="fcal_create_calendar_form_body">
            <el-form label-position="top">
                <el-form-item label="Date range">
                    <span class="sub-label">Invitees can schedule...</span>

                    <el-radio-group v-model="slot.settings.range_type" class="fcal_date_range_radio">
                        <div class="fcal_date_range_radio_item">
                            <el-radio label="range_days" size="large">Within future days</el-radio>

                            <div v-if="slot.settings.range_type == 'range_days'" class="fcal_date_range_radio_condition">
                                <el-input v-model="slot.settings.range_days" type="number">
                                    <template #append>Days into the future</template>
                                </el-input>
                            </div>
                        </div>
                        <div class="fcal_date_range_radio_item">
                            <el-radio label="range_date_between" size="large">Within a date range</el-radio>

                            <div v-if="slot.settings.range_type == 'range_date_between'" class="fcal_date_range_radio_condition">
                                <el-date-picker
                                    v-model="slot.settings.range_date_between"
                                    type="daterange"
                                    :disabled-date="disabledDate"
                                    value-format="YYYY-MM-DD"
                                    range-separator="To"
                                    start-placeholder="Start Date"
                                    end-placeholder="End Date"
                                    popper-class="fcal_daterange_popover"
                                />
                            </div>
                        </div>
                        <div class="fcal_date_range_radio_item">
                            <el-radio label="range_indefinite" size="large">Indefinitely into the future </el-radio>
                        </div>
                    </el-radio-group>
                </el-form-item>
                <el-divider/>
                <el-form-item label="How do you want to offer your availability for this event type?">
                    <el-tabs v-model="slot.availability_type">
                        <el-tab-pane label="Use an Existing Schedule" name="existing_schedule">
                            <div class="fcal_availability_body">
                                <h4>Which Schedule Do You Want to Use?</h4>
                                <el-select
                                    v-model="slot.availability_id"
                                    placeholder="Select Schedule"
                                    popper-class="fcal_select"
                                    class="fcal_timezone"
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
                        <el-tab-pane label="Set Custom Hours" name="custom">
                            <div class="fcal_availability_body">
                                <div class="fcal_timezone_text">
                                    <el-icon><TimezoneIcon/></el-icon>
                                    <p>{{ slot.calendar.author_timezone }}</p>
                                </div>
                                <div class="fcal_availability_setting">
                                    <WeeklySchedules
                                        :weekly_schedules="slot.settings.weekly_schedules"
                                        title="Weekly Hours"
                                    />
                                    <date-over-rides
                                        :settings="slot.settings"
                                        title="Add date overrides"
                                    />

                                </div>
                            </div>
                        </el-tab-pane>
                    </el-tabs>
                </el-form-item>

                <el-form-item label="Scheduling conditions" class="fcal_override_scheduling_condition_wrap">
                    <span class="sub-label">Invitees can't schedule within...</span>
                    <SchedulingConditions :settings="slot.settings"/>
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
    computed: {
        selectedSchedule() {
            const selectedAvailability = this.slot.settings.available_schedules.find(schedule => schedule.id === this.slot.availability_id);
            return selectedAvailability?.value || [];
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
