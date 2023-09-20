<template>
    <div class="fcal_settings">
        <div class="fcal_settings_body">
            <el-aside>
                <div class="fcal_title">
                    <h3>Settings</h3>
                </div>
                <ul class="fcal_settings_sidebar">
                    <li class="general">
                        <router-link to="/settings">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M2.5 7.59166V12.4C2.5 14.1667 2.5 14.1667 4.16667 15.2917L8.75 17.9417C9.44167 18.3417 10.5667 18.3417 11.25 17.9417L15.8333 15.2917C17.5 14.1667 17.5 14.1667 17.5 12.4083V7.59166C17.5 5.83333 17.5 5.83333 15.8333 4.70833L11.25 2.05833C10.5667 1.65833 9.44167 1.65833 8.75 2.05833L4.16667 4.70833C2.5 5.83333 2.5 5.83333 2.5 7.59166Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg> General
                        </router-link>
                    </li>
                    <li>
                        <router-link :to="{name: 'profile-settings'}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10.0002 10C12.3013 10 14.1668 8.13452 14.1668 5.83334C14.1668 3.53215 12.3013 1.66667 10.0002 1.66667C7.69898 1.66667 5.8335 3.53215 5.8335 5.83334C5.8335 8.13452 7.69898 10 10.0002 10Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M17.1585 18.3333C17.1585 15.1083 13.9501 12.5 10.0001 12.5C6.05013 12.5 2.8418 15.1083 2.8418 18.3333" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg> Profile
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
