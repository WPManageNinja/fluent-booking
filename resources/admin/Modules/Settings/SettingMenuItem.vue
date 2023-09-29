<template>
    <li class="fcal_settings_submenu_item">
        <div v-if="setting.submenu">
            <a :href="setting.menu.url" @click="handleClick(setting)" :class="['fcal_submenu_item', {'router-link-exact-active': isShowSubMenu, 'open-menu': toggleSubMenu}]">
                <div class="icon" v-html="setting.menu.svgIcon"></div>
                {{ setting.menu.label }} <el-icon><ArrowRight /></el-icon>
            </a>
            <ul :class="['fcal_settings_submenu', {'active': toggleSubMenu}]">
                <li v-for="(submenu, indx) in setting.submenu" :key="indx">
                    <router-link :to="{ name: setting.menu.key, params: { settings_key: submenu.key }}">
                        {{ submenu.label }}
                    </router-link>
                </li>
            </ul>
        </div>
        <div v-else>
            <router-link :to="{ name: setting.menu.key }">
                <div class="icon" v-html="setting.menu.svgIcon"></div>
                {{ setting.menu.label }}
            </router-link>
        </div>
    </li>
</template>

<script>
import { ArrowRight } from '@element-plus/icons-vue';

export default {
    name: "SettingMenuItem",
    props: ['setting'],
    components: {
        ArrowRight
    },
    data() {
        return {
            isShowSubMenu: false,
            toggleSubMenu: false
        }
    },
    watch: {
        '$route.path': function() {
            this.checkSubMenu();
        }
    },
    methods: {
        handleClick(setting) {
            this.toggleSubMenu = !this.toggleSubMenu;
            this.$router.push({ 
                name: setting.menu.key, 
                params: {settings_key: setting.submenu[0].key}
            });
        },
        checkSubMenu() {
            const pathParts = this.$route.path.split('/');
            if (pathParts[2] === this.setting.menu.key) {
                this.isShowSubMenu = true;
                this.toggleSubMenu = true;
            } else {
                this.isShowSubMenu = false;
                this.toggleSubMenu = false;
            }
        }
    },
    mounted() {
        this.checkSubMenu();
    }
}
</script>