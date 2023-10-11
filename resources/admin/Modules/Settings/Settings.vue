<template>
    <div class="fcal_settings">
        <div class="fcal_settings_header_title">
            <h3>Global Settings</h3>
        </div>
        <div class="fcal_settings_body">
            <el-aside v-loading="loading">
                <ul class="fcal_settings_sidebar">
                    <li class="fcal_settings_submenu_item">
                        <router-link class="fcal_img_menu_link" :to="{ name: 'general_settings' }">
                            <el-icon><Operation /></el-icon>
                            <span>General Settings</span>
                        </router-link>
                    </li>
                    <li v-for="(menu, itemName) in menuItems" :key="itemName" class="fcal_settings_submenu_item">
                        <router-link class="fcal_img_menu_link" :to="menu.route">
                            <img class="fcal_img_icon" :src="menu.icon_url"/>
                            <span>{{ menu.title }}</span>
                        </router-link>
                    </li>
                </ul>
            </el-aside>
            <div class="fcal_settings_container">
                <router-view v-if="!loading"/>
                <el-skeleton v-else animated>
                    <template #template>
                        <el-skeleton-item/>
                        <el-skeleton-item style="width: 70%"/>
                        <el-skeleton-item style="width: 50%"/>
                        <el-skeleton-item style="width: 50%"/>
                        <el-skeleton-item style="width: 50%"/>
                    </template>
                </el-skeleton>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import {Operation } from '@element-plus/icons-vue';
export default {
    name: 'Settings',
    components: {
        Operation
    },
    data() {
        return {
            loading: false,
            menuItems: {}
        }
    },
    methods: {
        fetchMenuItems() {
            this.loading = true;
            this.$get('settings/menu')
                .then(response => {
                    this.menuItems = response.menu_items;
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        gotoMenu(menu) {
            console.log(menu);
        }
    },
    mounted() {
        this.fetchMenuItems();
    }
}
</script>
