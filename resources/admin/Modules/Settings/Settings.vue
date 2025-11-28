<template>
    <div class="fcal_settings">
        <div class="fcal_settings_header_title">
            <h3>{{ $t('Global Settings') }}</h3>
        </div>
        <div class="fcal_settings_body">
            <el-aside v-loading="loading">
                <ul class="fcal_settings_sidebar">
                    <li v-for="(menu, itemName) in menuItems" :key="itemName" class="fcal_settings_submenu_item" :class="menu.class">
                        <router-link class="fcal_img_menu_link" :to="menu.route" :class="isChildrenActive(menu.route.name)" @click.native="toggleSubMenu(menu)">
                            <img v-if="menu.icon_url" class="fcal_img_icon" :src="menu.icon_url"/>
                            <el-icon v-else-if="menu.el_icon" class="fcal_img_icon">
                                <component :is="menu.el_icon"/>
                            </el-icon>
                            <span>{{ menu.title }}</span>
                            <template v-if="menu.children">
                                <el-icon v-if="isExpanded(menu.route.name)" class="fcal_img_menu_arrow">
                                    <ArrowDown />
                                </el-icon>
                                <el-icon v-else class="fcal_img_menu_arrow">
                                    <ArrowRight />
                                </el-icon>
                            </template>
                        </router-link>
                        <ul v-if="menu.children && isExpanded(menu.route.name)" class="fcal_settings_sub_menu">
                            <li v-for="(child, childName) in menu.children" class="submenu_item" :key="childName">
                                <router-link class="item_menu_link" :to="child.route" :class="isActive(child.route.name)" @click.native="setMenuStatus(child)">
                                    <span>{{ child.title }}</span>
                                </router-link>
                            </li>
                        </ul>
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
import { Lock, Operation, Money, ArrowDown, ArrowRight } from '@element-plus/icons-vue';

export default {
    name: 'Settings',
    components: {
        Lock,
        Operation,
        TeamIcon,
        Money,
        ArrowDown,
        ArrowRight
    },
    data() {
        return {
            loading: false,
            menuDisabled: false,
            expandedMenu: null,
            hasPro: this.appVars.has_pro,
            settingsMenuItems: this.appVars.settings_menu_items
        }
    },
    computed: {
        menuItems() {
            return Object.values(this.settingsMenuItems).filter(menu => {
                return !!menu.route;
            });
        }
    },
    methods: {
        maybeExpandedMenu() {
            const currentRoute = this.$route.meta;
            if (currentRoute?.parent) {
                this.expandedMenu = currentRoute?.parent;
            }
        },
        updateMenuStatus() {
            const currentRouteName = this.$route.name;
            const currentMenu = Object.values(this.menuItems).find(menu => {
                if (menu.route?.name === currentRouteName) {
                    return menu;
                }
                if (menu.children) {
                    return Object.values(menu.children).find(child => child.route.name === currentRouteName);
                }
                return false;
            });
            this.menuDisabled = currentMenu?.disable && !this.hasPro;
        },
        isChildrenActive(current) {
            const routeMeta = this.$route?.meta;
            const isCurrentRoute = this.$route?.name === current;
            const isParentRoute = routeMeta?.parent === current;
            return (isCurrentRoute && routeMeta?.children) || isParentRoute ? 'router-link-active router-link-exact-active' : '';
        },
        isActive(current) {
            const activeRoute = this.$route?.name;
            const isParentRoute = this.$route?.meta?.children_of === current;
            return (activeRoute === current) || isParentRoute ? 'router-link-active router-link-exact-active' : '';
        },
        isExpanded(current) {
            return this.expandedMenu === current && this.isChildrenActive(current);
        },
        setMenuStatus(menu) {
            this.menuDisabled = menu?.disable && !this.hasPro;
        },
        toggleSubMenu(menu) {
            this.setMenuStatus(menu);
            if (!menu.children) {
                this.expandedMenu = null;
                return;
            }
            const current = menu.route?.name;
            this.expandedMenu = this.expandedMenu === current ? null : current;
        }
    },
    mounted() {
        this.maybeExpandedMenu();
        this.updateMenuStatus();
    }
}
</script>
