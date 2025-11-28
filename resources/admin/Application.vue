<template>
    <div class="fl_app">
        <UpgradationBlock />
        <NoticeBlock />
        <router-view></router-view>
    </div>
</template>

<script>
import UpgradationBlock from './Components/Notice/UpgradationBlock.vue';
import NoticeBlock from './Components/Notice/NoticeBlock.vue';

export default {
    name: 'DashboardApplication',
    components: {
        NoticeBlock,
        UpgradationBlock
    },
    mounted() {
        jQuery('.update-nag,.notice, #wpbody-content > .updated, #wpbody-content > .error').not('.booking_notice').remove();
    }
};
</script>
