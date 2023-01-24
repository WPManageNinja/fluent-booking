<template>
    <div class="fcal_create_calendar fcal_section fcal_section_narrow">
        <div class="fcal_section_header">
            <h3>Create a new schedule calendar</h3>
        </div>
        <div class="fcal_section_body">
            <el-form :model="calendar" label-position="top">
                <el-form-item label="Title">
                    <el-input type="text" placeholder="Title of the form" v-model="calendar.title"/>
                </el-form-item>
                <el-row :gutter="20">
                    <el-col :md="12" :xs="24">
                        <el-form-item label="Slot Duration">
                            <el-input type="number" :min="10" placeholder="duration in minutes"
                                      v-model="calendar.slot.duration">
                                <template #append>minutes</template>
                            </el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :md="12" :xs="24">
                        <el-form-item label="Select Your Timezone">
                            <time-zone-selector v-model="calendar.author_timezone"/>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-form-item label="Schedule Type">
                    <el-radio-group v-model="calendar.slot.schedule_type">
                        <el-radio-button label="weekly_schedules">
                            Weekly Hours By Day
                        </el-radio-button>
                        <el-radio-button :disabled="true" label="custom_dates">
                            Specific Dates & Hours (coming soon)
                        </el-radio-button>
                    </el-radio-group>
                </el-form-item>
                <template v-if="calendar.slot.schedule_type == 'weekly_schedules'">
                    <el-form-item label="Weekly Hours Schedules">
                        <weekly-schedules :weekly_schedules="calendar.slot.weekly_schedules"/>
                    </el-form-item>
                </template>
                <el-form-item>
                    <el-button :disabled="saving" v-loading="saving" type="primary" @click="createCalendar">Create
                    </el-button>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script type="text/babel">
import WeeklySchedules from './parts/WeeklySchedules.vue';
import TimeZoneSelector from './parts/TimeZoneSelector.vue';

export default {
    name: 'NewCalender',
    components: {
        WeeklySchedules,
        TimeZoneSelector
    },
    data() {
        return {
            calendar: {
                title: '',
                description: '',
                author_timezone: '',
                slot: {
                    duration: 15,
                    schedule_type: 'weekly_schedules',
                    weekly_schedules: {
                        sun: {
                            enabled: false,
                            slots: []
                        },
                        mon: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        tue: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        wed: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        thu: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        fri: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        sat: {
                            enabled: false,
                            slots: []
                        }
                    }
                }
            },
            saving: false
        }
    },
    methods: {
        createCalendar() {
            this.saving = true;
            this.$post('calendars', {
                calendar: this.calendar
            })
                .then(response => {
                    this.saving = false;
                    console.log(response);
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
