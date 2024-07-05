<template>
    <div class="fl_app">
        <NoticeBlock />
        <router-view></router-view>
    </div>
</template>

<script>
import OnBoard from './Components/OnBoard';
import NoticeBlock from './Components/Notice/NoticeBlock';

export default {
    name: 'DashboardApplication',
    components: {
        OnBoard,
        NoticeBlock
    },
    mounted() {
        jQuery('.notice-warning, .notice-error').remove();
    }
};
</script>
