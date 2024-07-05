<template>
    <div class="dashboard_notices" v-if="dashboardNotices.length">
        <div class="fcal_dashboard_notice" v-for="(notice, notice_key) in dashboardNotices"
            :key="notice_key"
            v-html="notice">
        </div>
    </div>
</template>
  
<script>
export default {
    name: 'NoticeBlock',
    data() {
        return {
            dashboardNotices: this.appVars.dashboard_notices
        }
    },
};
</script>