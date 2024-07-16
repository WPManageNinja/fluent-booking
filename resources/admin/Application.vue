<template>
    <div class="fl_app">
        <UpgradationBlock />
        <router-view></router-view>
    </div>
</template>

<script>
import OnBoard from './Components/OnBoard';
import UpgradationBlock from './Components/Notice/UpgradationBlock.vue';

export default {
    name: 'DashboardApplication',
    components: {
        OnBoard,
        UpgradationBlock
    },
    mounted() {
        jQuery('.update-nag,.notice, #wpbody-content > .updated, #wpbody-content > .error').not('.booking_notice').remove();
    }
};
</script>
