<template>
    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2> <ScheduleIcon/> Schedule Settings </h2>
        </div>
        <div class="fcal_create_calendar_form_body">
            <el-form v-model="formData" label-position="top">
                <el-form-item label="Date range">
                    <span class="sub-label">Invitees can schedule...</span>

                    <el-radio-group v-model="formData.dateRangeType" class="fcal_date_range_radio">
                        <div class="fcal_date_range_radio_item">
                            <el-radio label="within_days" size="large">Within future days</el-radio>

                            <div v-if="formData.dateRangeType == 'within_days'" class="fcal_date_range_radio_condition">
                                <el-input v-model="formData.within_days" type="number">
                                    <template #append>Days into the future</template>
                                </el-input>
                            </div>
                        </div>
                        <div class="fcal_date_range_radio_item">
                            <el-radio label="within_date" size="large">Within a date range</el-radio>

                            <div v-if="formData.dateRangeType == 'within_date'" class="fcal_date_range_radio_condition">
                                <el-date-picker
                                    v-model="formData.within_date"
                                    type="daterange"
                                    range-separator=""
                                    start-placeholder="Select Date"
                                    end-placeholder=" "
                                    popper-class="fcal_daterange_popover"
                                />
                            </div>
                        </div>
                        <div class="fcal_date_range_radio_item">
                            <el-radio label="indefinitely" size="large">Indefinitely into the future </el-radio>
                        </div>
                    </el-radio-group>
                </el-form-item>
                <el-divider/>
                <el-form-item label="How do you want to offer your availability for this event type?">
                    <el-tabs v-model="formData.availabilityTab">
                        <el-tab-pane label="Use an Existing Schedule" name="existingSchedule">
                            <div class="fcal_availability_body">
                                <el-select
                                    v-model="formData.timezone"
                                    placeholder="Select timezone"
                                    popper-class="fcal_select"
                                    class="fcal_timezone"
                                >
                                    <el-option
                                        label="Asia/Dhaka"
                                        value="asia/dhaka"
                                    />
                                    <el-option
                                        label="United State"
                                        value="us"
                                    />
                                </el-select>

                                <ExistingSchedule :existing_schedules="slot.settings.weekly_schedules" />

                            </div>
                        </el-tab-pane>
                        <el-tab-pane label="Set Custom Hours" name="setCustomHour">
                            <div class="fcal_availability_body">
                                <el-select
                                    v-model="formData.timezone"
                                    placeholder="Select timezone"
                                    popper-class="fcal_select"
                                    class="fcal_timezone"
                                >
                                    <el-option
                                        label="Asia/Dhaka"
                                        value="asia/dhaka"
                                    />
                                    <el-option
                                        label="United State"
                                        value="us"
                                    />
                                </el-select>
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
import ScheduleIcon from "../../../Components/Icons/ScheduleIcon.vue";
export default {
    name: '_ScheduleSettings',
    components: {
        SchedulingConditions,
        DateOverRides,
        WeeklySchedules,
        ExistingSchedule,
        ScheduleIcon
    },
    props: ['slot'],
    data() {
        return {
            formData: {
                dateRangeType: 'within_days',
                within_days: '',
                within_date: '',
                availabilityTab: 'setCustomHour',
                timezone: 'asia/dhaka'
            }
        }
    }
}
</script>

<style scoped>

</style>