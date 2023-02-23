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
            </el-form>
        </div>
    </div>
</template>

<script type="text/babel">
import WeeklySchedules from "../parts/WeeklySchedules.vue";
import DateOverRides from './_DateOverRides.vue';

export default {
    name: 'SlotSettingsForm',
    components: {
        WeeklySchedules,
        DateOverRides
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
    }
}
</script>
