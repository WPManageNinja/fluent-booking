<template>
    <div class="fl_app">
        <router-view></router-view>
    </div>
</template>

<script type="text/babel">
import OnBoard from './Components/OnBoard';

export default {
    name: 'DashboardApplication',
    components: {
        OnBoard
    },
    mounted() {
        jQuery('.notice-warning, .notice-error').remove();
    }
};
</script>
