<template>
    <div class="fcal_settings">
        <div class="fcal_settings_header_title">
            <h3>{{ $t('Global Settings') }}</h3>
        </div>
        <div class="fcal_settings_body">
            <el-aside v-loading="loading">
                <ul class="fcal_settings_sidebar">
                    <SettingsMenu @updateMenuDisabled="updateMenuDisabled" />
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

    <el-drawer
        v-model="showDrawer"
        :size="320"
        :title="$t('Settings')"
        :direction="appVars.is_rtl ? 'ltr' : 'rtl'"
        :append-to-body="true"
        modal-class="fcal_settings_menu_drawer">
        <SettingsMenu @toggle-menu="showDrawer = false"/>
    </el-drawer>
</template>

<script>
import SettingsMenu from './_SettingsMenu.vue';

export default {
    name: 'Settings',
    components: {
        SettingsMenu,
    },
    data() {
        return {
            showDrawer: false,
            menuDisabled: false
        }
    },
    methods: {
        updateMenuDisabled(newVal) {
            this.menuDisabled = newVal || false;
        }
    },
    mounted() {
        window.toggleMobileSettingsMenu = () => {
            this.showDrawer = !this.showDrawer;
        };
    }
}
</script>
