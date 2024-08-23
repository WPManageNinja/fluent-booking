<template>
    <div class="fcal_settings">
        <div class="fcal_settings_header_title">
            <h3>{{ $t('Global Settings') }}</h3>
        </div>
        <div class="fcal_settings_body">
            <el-aside v-loading="loading">
                <ul class="fcal_settings_sidebar">
                    <li v-for="(menu, itemName) in menuItems" :key="itemName" class="fcal_settings_submenu_item" :class="menu.class">
                        <router-link class="fcal_img_menu_link" :to="menu.route" @click.native="setMenuStatus(menu.disable)">
                            <img v-if="menu.icon_url" class="fcal_img_icon" :src="menu.icon_url"/>
                            <el-icon v-else-if="menu.el_icon" class="fcal_img_icon">
                                <component :is="menu.el_icon"/>
                            </el-icon>
                            <span>{{ menu.title }}</span>
                        </router-link>
                    </li>
                </ul>
            </el-aside>
            <div class="fcal_settings_container">
                <router-view v-if="!loading" :disabled="menuDisabled"/>
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

<script>
import TeamIcon from "@/Components/Icons/TeamIcon.vue";
import { Lock, Operation } from '@element-plus/icons-vue';

export default {
    name: 'Settings',
    components: {
        Lock,
        Operation,
        TeamIcon,
    },
    data() {
        return {
            loading: true,
            menuDisabled: false,
            menuItems: {}
        }
    },
    methods: {
        setMenuStatus(status) {
            this.menuDisabled = status;
        },
        updateMenuStatus() {
            const currentRouteName = this.$route.name;
            const currentMenu = Object.values(this.menuItems).find(menu => menu.route.name === currentRouteName);
            this.menuDisabled = currentMenu.disable || false;
        },
        fetchMenuItems() {
            this.loading = true;
            this.$get('settings/menu')
                .then(response => {
                    this.menuItems = response.menu_items;
                    this.updateMenuStatus();
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    mounted() {
        this.fetchMenuItems();
    }
}
</script>
