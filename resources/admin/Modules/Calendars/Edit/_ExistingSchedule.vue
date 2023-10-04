<template>
    <div class="fcal_availability_setting">
        <div class="fcal_weekly_schedule_wrap">
            <h2 class="fcal_availability_title">Weekly Hours</h2>
            <ul class="fcal_weekly_existing_schedule">
                <li v-for="(schedule, i) in existing_schedules.weekly_schedules" :key="i">
                    <span class="day">{{ i }}</span>
                    <span class="date">
                        <span v-if="schedule.slots.length" v-for="(time, index) in schedule.slots" :key="index">
                            {{ time.start }} - {{ time.end }}
                        </span>
                        <span v-else class="unavailable">Unavailable</span>
                    </span>
                </li>
            </ul>
        </div>
        <div class="fcal_override_table">
            <h2 class="fcal_availability_title">Date Overrides</h2>
            <div class="fcal_override_date">
                <table v-if="dateOverridesNotEmpty" class="fcal_table_compact fcal_table_stripe">
                    <tbody>
                        <tr v-for="(date, index) in existing_schedules.date_overrides" style="cursor: pointer;">
                            <td>
                                <span class="date">{{index}}</span>
                            </td>
                            <td style="text-align: right;">
                                <ul class="fcal_slots_list">
                                    <li v-for="time in date">
                                        {{toDateFormat('2022-12-12 ' + time.start, 'HH:mma')}} - {{toDateFormat('2022-12-12 ' + time.end, 'HH:mma')}}
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-else>No specific date overrides found for this schedule</div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "_ExistingSchedule.vue",
    props: ['existing_schedules'],
    computed: {
        dateOverridesNotEmpty() {
            if (this.existing_schedules.date_overrides) {
                return Object.keys(this.existing_schedules?.date_overrides).length;
            }
            return false;
        }
    }
}
</script>
