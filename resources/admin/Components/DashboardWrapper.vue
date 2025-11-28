<template>
    <on-boarding v-if="is_new"/>
    <dashboard v-else/>
</template>

<script>
import Dashboard from './Dashboard.vue';
import OnBoarding from './OnBoard.vue';

export default {
    name: 'DashboardWrapper',
    components: {
        Dashboard,
        OnBoarding
    },
    data() {
        return {
            is_new: false
        }
    },
    mounted() {
        this.is_new = this.appVars.is_new;
    }
}
</script>
