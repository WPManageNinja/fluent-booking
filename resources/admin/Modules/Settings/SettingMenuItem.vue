<template>
    <li class="fcal_settings_submenu_item">
        <div v-if="setting.submenu">
            <a :href="setting.menu.url" @click="handleClick(setting.menu.key)" class="fcal_submenu_item">
                <div class="icon" v-html="setting.menu.svgIcon"></div>
                {{ setting.menu.label }} <el-icon><ArrowRight /></el-icon>
            </a>
            <ul class="fcal_settings_submenu" :class="isShowSubMenu ? 'active' : null">
                <li v-for="(submenu, indx) in setting.submenu" :key="indx">
                    <router-link :to="{ name: setting.menu.key, params: { settings_key: indx }}">
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
        }
    },
    methods: {
        handleClick(name) {
            this.isShowSubMenu = !this.isShowSubMenu;
        }
    },
    mounted() {

    }
}
</script>

<style scoped>

</style>