<template>
    <div class="fcal_weekly_schedules">
        <week-day-config v-for="(config, weekDay) in weekly_schedules" :key="weekDay" :week_day="weekDay"
                         :config="config"/>
    </div>
</template>

<script type="text/babel">
import WeekDayConfig from './WeekDayConfig.vue';

export default {
    name: 'WeeklySchedules',
    components: {
        WeekDayConfig
    },
    props: ['weekly_schedules']
}
</script>
