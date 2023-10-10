<template>
    <div class="fcal_single_integration_wrap">
        <div class="fcal_header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item :to="{ name: 'calendars' }">Booking Types</el-breadcrumb-item>
                <el-breadcrumb-item>{{ calendar.author_profile?.name }}</el-breadcrumb-item>
                <el-breadcrumb-item>Settings</el-breadcrumb-item>
            </el-breadcrumb>
        </div>

        <el-skeleton v-if="loading" />
        <div v-else class="fcal_single_integration_body_wrap">
            <el-aside>
                <ul class="fcal_settings_sidebar">
                    <li v-for="(menu, index) in menuItems" :key="index">
                        <router-link v-if="menu.type == 'route'" :to="menu.route" class="calendar_route">
                            <el-icon><div class="icon" v-html="menu.svgIcon"></div></el-icon>
                            {{ menu.label }}
                        </router-link>
                    </li>
                </ul>
            </el-aside>
            <div v-if="calendar.id" class="fcal_single_integration_body">
                <router-view :calendar="calendar" />
            </div>
        </div>
    </div>
</template>

<script type="text/babel">

export default {
    name: 'CalendarSettings',
    data() {
        return {
            calendar_id: this.$route.params.id,
            loading: false,
            menuItems: {},
            calendar: {}
        }
    },
    methods: {
        getSettings() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_id, {
                with: ['settings_menu']
            })
            .then(response => {
                this.menuItems = response.settings_menu
                this.calendar = response.calendar
            })
            .catch(errors => {
                this.$handleError(errors);
            })
            .finally(() => {
                this.loading = false;
            })
        },
    },
    mounted() {
        this.getSettings();
    }

}
</script>
