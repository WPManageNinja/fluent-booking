<template>
    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M8 2V5" stroke="#1B2533" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 2V5" stroke="#1B2533" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M7 13H15" stroke="#1B2533" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M7 17H12" stroke="#1B2533" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 3.5C19.33 3.68 21 4.95 21 9.65V15.83C21 19.95 20 22.01 15 22.01H9C4 22.01 3 19.95 3 15.83V9.65C3 4.95 4.67 3.69 8 3.5H16Z" stroke="#1B2533" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg> Schedule Settings
            </h2>
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

                <el-form-item label="How do you want to offer your availability for this event type?">
                    <el-tabs v-model="formData.availabilityTab">
                        <el-tab-pane label="Use an Existing Schedule" name="existingSchedule">User</el-tab-pane>
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
export default {
    name: '_ScheduleSettings',
    components: {SchedulingConditions, DateOverRides, WeeklySchedules},
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