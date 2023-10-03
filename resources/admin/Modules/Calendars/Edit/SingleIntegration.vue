<template>
    <div class="fcal_single_integration_wrap">
        <div class="fcal_header">
            <router-link :to="{name: 'calendars'}" class="fcal_back_btn">
                <el-icon><Back /></el-icon> Go Back
            </router-link>
        </div>

        <el-skeleton v-if="loading" />
        <div v-else class="fcal_single_integration_body_wrap">
            <el-aside>
                <ul class="fcal_settings_sidebar">
                    <li v-for="(menu, index) in menuItems" :key="index">
                        <router-link :to="{ name: menu.key, params: { settings_key: menu.key }}">{{ menu.label }}</router-link>
                    </li>
                </ul>
            </el-aside>

            <div class="fcal_single_integration_body">
                <router-view />
            </div>
        </div>

    </div>
</template>

<script>
import {Back, Minus, Plus} from '@element-plus/icons-vue';
import {markRaw} from "vue";

export default {
    name: 'SingleIntegration',
    components: {
        Back
    },
    data() {
        return {
            user_id: this.$route.params.id,
            loading: false,
            settings: {},
            fieldSettings: {},
            settingsKey: 'google_calendar',
            menuItems: ''
        }
    },
    methods: {
        getSettings() {
            this.loading = true;
            this.$get('integrations/settings/menu', {
                settings_key: this.settingsKey,
            })
            .then(response => {
                this.menuItems = response.menu_items
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

<style scoped>

</style>