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
                            <div class="icon" v-html="menu.icon"></div> {{menu.title}}
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
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-[16px] w-[16px] stroke-[2px] ltr:mr-2 rtl:ml-2 md:mt-0" data-testid="icon-component"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>',
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
