<template>
    <el-form :model="slot" label-position="top">
        <div class="fcal_form_section">
            <div class="fcal_section_heading">
                <h3>Event Information</h3>
            </div>
            <div class="fcal_section_body">
                <el-form-item label="Event name">
                    <el-input type="text" placeholder="Title of the event slot" v-model="slot.title"/>
                </el-form-item>
                <el-form-item label="Event Description">
                    <el-input :rows="4" type="textarea" placeholder="Event Description" v-model="slot.description"/>
                </el-form-item>
                <el-row :gutter="30">
                    <el-col :sm="24" :md="12">
                        <el-form-item label="Slot Duration">
                            <el-input type="number" :min="10" placeholder="duration in minutes"
                                      v-model="slot.duration">
                                <template #append>minutes</template>
                            </el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :sm="24" :md="12">
                        <el-form-item label="Location">
                            <location-selector :slot="slot" />
                        </el-form-item>
                    </el-col>
                </el-row>
            </div>
        </div>
        <div class="fcal_form_section">
            <div class="fcal_section_heading">
                <h3>Scheduling Settings</h3>
            </div>
            <div class="fcal_section_body">
                <el-form-item label="Schedule Type">
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
                    <el-form-item label="Weekly Hours Schedules">
                        <div class="fcal_timezone_text">Timezone: {{ slot.calendar.author_timezone }}</div>
                        <weekly-schedules :weekly_schedules="slot.settings.weekly_schedules"/>
                    </el-form-item>
                </template>
            </div>
        </div>
    </el-form>
</template>

<script type="text/babel">
import WeeklySchedules from "../parts/WeeklySchedules.vue";
import LocationSelector from "./_LocationSelector.vue";

export default {
    name: 'SlotSettingsForm',
    components: {
        WeeklySchedules,
        LocationSelector
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
}
</script>
