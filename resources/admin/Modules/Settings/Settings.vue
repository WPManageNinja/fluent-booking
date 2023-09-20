<template>
    <div class="fcal_settings">
        <div class="fcal_settings_body">
            <el-aside>
                <div class="fcal_title">
                    <h3>Settings</h3>
                </div>
                <ul class="fcal_settings_sidebar">
                    <li>
                        <router-link to="/settings">
                            General
                        </router-link>
                    </li>
                    <li>
                        <router-link :to="{name: 'profile-settings'}">
                            Profile
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
