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
                    <li class="configure">
                        <router-link :to="{name: 'configure-integrations'}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4.7915 13.425V6.57499C5.8665 6.29999 6.6665 5.33332 6.6665 4.16666C6.6665 2.78332 5.54984 1.66666 4.1665 1.66666C2.78317 1.66666 1.6665 2.78332 1.6665 4.16666C1.6665 5.33332 2.4665 6.29999 3.5415 6.57499V13.4167C2.4665 13.7 1.6665 14.6667 1.6665 15.8333C1.6665 17.2167 2.78317 18.3333 4.1665 18.3333C5.54984 18.3333 6.6665 17.2167 6.6665 15.8333C6.6665 14.6667 5.8665 13.7 4.7915 13.425Z" fill="#445164" stroke="#445164" stroke-width="1" />
                                <path d="M16.4583 13.425V5.41666C16.4583 4.14999 15.4333 3.12499 14.1667 3.12499H11.725L12.9 2.14999C13.1667 1.92499 13.2 1.53333 12.9833 1.26666C12.7583 0.999994 12.3667 0.96666 12.1 1.18333L9.6 3.26666C9.45833 3.38333 9.375 3.55833 9.375 3.74999C9.375 3.94166 9.45833 4.10833 9.6 4.23333L12.1 6.31666C12.2167 6.41666 12.3583 6.45833 12.5 6.45833C12.675 6.45833 12.8583 6.38333 12.9833 6.23333C13.2083 5.96666 13.1667 5.57499 12.9 5.34999L11.725 4.37499H14.1667C14.7417 4.37499 15.2083 4.84166 15.2083 5.41666V13.425C14.1333 13.7 13.3333 14.6667 13.3333 15.8333C13.3333 17.2167 14.45 18.3333 15.8333 18.3333C17.2167 18.3333 18.3333 17.2167 18.3333 15.8333C18.3333 14.6667 17.5333 13.7 16.4583 13.425Z" fill="#445164" stroke="#445164" stroke-width="1" />
                            </svg> Configure Integrations
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
