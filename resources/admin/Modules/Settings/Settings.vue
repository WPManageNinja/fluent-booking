<template>
    <div class="fcal_settings">
        <div class="fcal_settings_header_title">
            <h3>General Settings</h3>
        </div>
        <div class="fcal_settings_body">
            <el-aside>
                <ul class="fcal_settings_sidebar">
                    <li v-for="(menu, itemName) in menuItems" :key="itemName" class="fcal_settings_submenu_item">
                        <router-link :to="menu.route">
                            {{menu.title}}
                        </router-link>
                    </li>
                </ul>
            </el-aside>
            <div class="fcal_settings_container">
                <router-view v-if="!loading" />
                <el-skeleton v-else animated>
                    <template #template>
                        <el-skeleton-item />
                        <el-skeleton-item style="width: 70%" />
                        <el-skeleton-item style="width: 50%" />
                        <el-skeleton-item style="width: 50%" />
                        <el-skeleton-item style="width: 50%" />
                    </template>
                </el-skeleton>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'Settings',
    data() {
        return {
            loading: false,
            menuItems: {
                google_calendar: {
                    title: 'Google Calendar',
                    route: {
                        name: 'configure-integrations',
                        params: {
                            settings_key: 'google_calendar'
                        }
                    }
                },
            }
        }
    },
    methods: {
        gotoMenu(menu) {
            console.log(menu);
        }
    }
}
</script>
