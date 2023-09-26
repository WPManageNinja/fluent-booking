<template>
    <div class="fcal_settings">
        <div class="fcal_settings_header_title">
            <h3>Settings</h3>
        </div>
        <div class="fcal_settings_body">
            <el-aside>
                <ul class="fcal_settings_submenu">
                    <li v-for="(submenu, indx) in setting.submenu" :key="indx">
                        <router-link :to="{ name: setting.menu.key, params: { settings_key: indx }}">
                            {{ submenu.label }}
                        </router-link>
                    </li>
                </ul>
            </el-aside>
            <div v-loading="loading" class="fcal_settings_container">
                <router-view/>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Settings',
    data() {
        return {
            loading: false
        }
    }
}
</script>
