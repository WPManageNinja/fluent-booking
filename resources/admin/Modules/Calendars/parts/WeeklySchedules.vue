<template>
    <div class="fcal_weekly_schedule_wrap">
        <h2 v-if="title" class="fcal_availability_title">{{ title }}</h2>
        <div class="fcal_weekly_schedules">
            <week-day-config 
                v-for="(config, weekDay) in weekly_schedules" 
                :key="weekDay" 
                :week_day="weekDay" 
                :config="config"
            />
        </div>
    </div>
</template>

<script type="text/babel">
import WeekDayConfig from './WeekDayConfig.vue';

export default {
    name: 'WeeklySchedules',
    components: {
        WeekDayConfig
    },
    props: ['weekly_schedules', 'title']
}
</script>
