<template>
    <div class="fcal_settings">
        <div class="fcal_settings_header_title">
            <h3>Settings</h3>
        </div>
        <div class="fcal_settings_body">
            <el-aside>
                <ul class="fcal_settings_sidebar">
                    <SettingMenuItem v-for="(setting, index) in settings" :key="index" :setting="setting" />

<!--                    <li v-for="(setting, index) in settings" :key="index" class="configure">-->
<!--                        <div v-if="setting.submenu">-->
<!--                            <a :href="setting.menu.url">-->
<!--                                <div v-html="setting.menu.svgIcon"></div>-->
<!--                                {{ setting.menu.label }}-->
<!--                            </a>-->
<!--                            <ul class="fcal_settings_submenu">-->
<!--                                <li v-for="(submenu, indx) in setting.submenu" :key="indx">-->
<!--                                    <router-link :to="{ name: setting.menu.key, params: { settings_key: indx }}">-->
<!--                                        {{ submenu.label }}-->
<!--                                    </router-link>-->
<!--                                </li>-->
<!--                            </ul>-->
<!--                        </div>-->
<!--                        <div v-else>-->
<!--                            <router-link :to="{ name: setting.menu.key }">-->
<!--                                <div v-html="setting.menu.svgIcon"></div>-->
<!--                                {{ setting.menu.label }}-->
<!--                            </router-link>-->
<!--                        </div>-->
<!--                    </li>-->
                </ul>
            </el-aside>
            <div v-loading="loading" class="fcal_settings_container">
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
