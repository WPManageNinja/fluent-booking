<template>
    <div class="fcal_settings">
        <div class="fcal_settings_header_title">
            <h3>Settings</h3>
        </div>
        <div class="fcal_settings_body">
            <el-aside>
                <ul class="fcal_settings_sidebar">
                    <SettingMenuItem v-for="(setting, index) in settings" :key="index" :setting="setting" />
                </ul>
            </el-aside>
            <div class="fcal_settings_container">
                <router-view/>
            </div>
        </div>
    </div>
</template>

<script>
import SettingMenuItem from "./SettingMenuItem";
export default {
    name: 'Settings',
    components: {
        SettingMenuItem
    },
    data() {
        return {
            loading: false,
            settings: {}
        }
    },
    methods: {
        fetchSettings() {
            this.loading = true;
            this.$get('settings')
                .then(response => {
                    this.settings = response.items;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    mounted() {
        this.fetchSettings();
    }
}
</script>
