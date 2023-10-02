<template>
    <div class="fcal_availability_setting">
        <div class="fcal_weekly_schedule_wrap">
            <h2 class="fcal_availability_title">Weekly Hours</h2>

            <ul class="fcal_weekly_existing_schedule">
                <li v-for="(schedule, i) in existing_schedules" :key="i">
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
    </div>
</template>

<script>
export default {
    name: "_ExistingSchedule.vue",
    props: ['existing_schedules']
}
</script>
