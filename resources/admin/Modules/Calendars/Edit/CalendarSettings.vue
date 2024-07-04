<template>
    <div class="fcal_single_integration_wrap">
        <div class="fcal_header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item :to="{ name: 'calendars' }">{{ $t('Booking Types') }}</el-breadcrumb-item>
                <el-breadcrumb-item>{{ calendar.title ?? '...' }}</el-breadcrumb-item>
                <el-breadcrumb-item>{{ $t('Settings') }}</el-breadcrumb-item>
            </el-breadcrumb>
        </div>

        <el-skeleton v-if="loading" />
        <div v-else class="fcal_single_integration_body_wrap">
            <el-aside>
                <ul class="fcal_settings_sidebar">
                    <template v-for="(menu, index) in menuItems">
                        <li v-if="isRouteVisible(menu)" :key="index">
                            <router-link :to="menu.route" @click.native="setCurrentMenu(menu)" class="calendar_route">
                                <el-icon><div class="icon" v-html="menu.svgIcon"></div></el-icon>
                                {{ menu.label }}
                            </router-link>
                        </li>
                    </template>
                </ul>
            </el-aside>
            <div v-if="calendar.id" class="fcal_single_integration_body">
                <router-view :calendar="calendar" :disabled="currentMenu.disable"/>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">

export default {
    name: 'CalendarSettings',
    data() {
        return {
            calendar_id: this.$route.params.calendar_id,
            loading: false,
            menuItems: {},
            calendar: {},
            currentMenu: {}
        }
    },
    computed: {
        isRouteVisible() {
            return (menu) => {
                return menu.type == 'route' && menu.visible;
            }
        }
    },
    methods: {
        setCurrentMenu(menu) {
            this.currentMenu = menu;
        },
        updateCurrentMenu() {
            const currentRouteName = this.$route.name;
            const currentMenu = Object.values(this.menuItems).find(menu => menu.route.name === currentRouteName);
            if (currentMenu) {
                this.currentMenu = currentMenu;
            }
        },
        getSettings() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_id, {
                calendar_id: this.calendar_id,
                with: ['settings_menu']
            })
            .then(response => {
                this.menuItems = response.settings_menu;
                this.calendar = response.calendar;
                this.updateCurrentMenu();
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
