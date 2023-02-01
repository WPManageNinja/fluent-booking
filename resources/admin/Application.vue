<template>
    <div class="fl_app">
        <on-board v-if="is_new" />
        <router-view v-else></router-view>
    </div>
</template>

<script type="text/babel">
import OnBoard from './Components/OnBoard';
export default {
    name: 'DashboardApplication',
    components: {
        OnBoard
    },
    data() {
        return {
            is_new: false
        }
    },
    mounted() {
        this.is_new = this.appVars.is_new;
        jQuery('.notice-warning, .notice-error').remove();
    }
};
</script>
