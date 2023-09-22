<template>
    <div  class="fcal_form_section">
        <div style="padding: 15px 0;" class="fcal_section_body">
            <el-form :model="slot" label-position="top">
                <el-form-item v-if="false" label="Schedule Type">
                    <el-radio-group v-model="slot.settings.schedule_type">
                        <el-radio-button label="weekly_schedules">
                            Weekly Hours By Day
                        </el-radio-button>
                        <el-radio-button :disabled="true" label="custom_dates">
                            Specific Dates & Hours (coming soon)
                        </el-radio-button>
                    </el-radio-group>
                </el-form-item>
                <template v-if="slot.settings.schedule_type == 'weekly_schedules'">
                    <h3>Weekly Hours Schedules</h3>
                    <div class="fcal_timezone_text">Timezone: {{ slot.calendar.author_timezone }}</div>
                    <el-row :gutter="30">
                        <el-col :md="16" :sm="24">
                            <weekly-schedules :weekly_schedules="slot.settings.weekly_schedules"/>
                        </el-col>
                        <el-col :md="8" :sm="24">
                            <h3 style="font-size: 20px;">Date overrides</h3>
                            <date-over-rides :settings="slot.settings"/>
                        </el-col>
                    </el-row>
                </template>

                <el-row :gutter="30">
                    <el-col :md="16" :sm="24">
                        <h3 style="margin-top: 20px;">Date Range</h3>
                        <el-form-item label="Invitees can schedule...">
                            <el-radio-group v-model="slot.settings.range_type">
                                <el-radio-button label="range_days">
                                    Within future days
                                </el-radio-button>
                                <el-radio-button label="range_date_between">
                                    Within a Date Range
                                </el-radio-button>
                                <el-radio-button label="range_indefinite">
                                    Indefinitely into the future
                                </el-radio-button>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item v-if="slot.settings.range_type == 'range_days'">
                            <div style="max-width: 500px;">
                                <el-input placeholder="ex: 60" type="number" v-model="slot.settings.range_days">
                                    <template #append>days into the future</template>
                                </el-input>
                            </div>
                        </el-form-item>
                        <el-form-item label="Select the available date range" v-else-if="slot.settings.range_type == 'range_date_between'">
                            <div style="max-width: 500px;">
                                <el-date-picker
                                    v-model="slot.settings.range_date_between"
                                    type="daterange"
                                    value-format="YYYY-MM-DD"
                                    range-separator="To"
                                    :disabled-date="disabledDate"
                                    start-placeholder="Start date"
                                    end-placeholder="End date"
                                />
                            </div>
                        </el-form-item>
                    </el-col>
                    <el-col :md="8" :sm="24">
                        <h3 style="margin-top: 20px;">Scheduling conditions</h3>
                        <el-form-item label="Invitees can't schedule within...">
                            <scheduling-conditions :settings="slot.settings"/>
                        </el-form-item>
                    </el-col>
                </el-row>

            </el-form>
        </div>
    </div>
</template>

<script type="text/babel">
import WeeklySchedules from "../parts/WeeklySchedules.vue";
import DateOverRides from './_DateOverRides.vue';
import SchedulingConditions from './__SchedulingConditions.vue';

export default {
    name: 'SlotSettingsForm',
    components: {
        WeeklySchedules,
        DateOverRides,
        SchedulingConditions
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
                }
            }
        }
    },
    methods: {
        disabledDate(time) {
            return (time.getTime() + 86400000) <= Date.now();
        }
    }
}
</script>
