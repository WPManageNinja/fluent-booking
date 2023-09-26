<template>

    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
                    <path d="M2.49612 8.5H11.9961" stroke="#1B2533" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.49612 16.5H8.49612" stroke="#1B2533" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10.9961 16.5H14.9961" stroke="#1B2533" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M22.4961 12.03V16.11C22.4961 19.62 21.6061 20.5 18.0561 20.5H6.93612C3.38612 20.5 2.49612 19.62 2.49612 16.11V7.89C2.49612 4.38 3.38612 3.5 6.93612 3.5H14.9961" stroke="#1B2533" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19.5761 4.12982L15.8661 7.83982C15.7261 7.97982 15.5861 8.25982 15.5561 8.45982L15.3561 9.87982C15.2861 10.3898 15.6461 10.7498 16.1561 10.6798L17.5761 10.4798C17.7761 10.4498 18.0561 10.3098 18.1961 10.1698L21.9061 6.45982C22.5461 5.81982 22.8461 5.07982 21.9061 4.13982C20.9561 3.18982 20.2161 3.48982 19.5761 4.12982Z" stroke="#1B2533" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19.0461 4.66016C19.3661 5.79016 20.2461 6.67016 21.3661 6.98016" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg> Additional Info
            </h2>
        </div>
    </div>

    <div v-if="!loading" class="fcal_notification_container_wrap">
        <div :class="['fcal_notification_container', {disabled: !notification.enabled}]" v-for="(notification, index) in notifications" :key="index">
            <div class="fcal_notification_header">
                <span :class="['header_left', {active: isEditOpen[index]}]">
                    {{ notification.title }}
                </span>
                <div class="header_right">
                    <span v-if="notification.enabled">
                        <el-button v-if="isEditOpen[index]" @click="toggleEdit(index)" class="fcal_plain_btn">
                            <el-icon><Close /></el-icon> Close
                        </el-button>
                        <el-button v-else @click="toggleEdit(index)" class="fcal_plain_btn">
                            <el-icon><EditPen /></el-icon> Edit
                        </el-button>
                    </span>
                    <span style="color: red;" v-else>Disabled</span>
                    <el-switch v-model="notification.enabled" @click="closeEdit(index)"></el-switch>
                </div>
            </div>
            <div v-if="isEditOpen[index] && notification.enabled" class="fcal_notification_body">
                <EditNotificationSettings :email="notification.email"/>
            </div>
        </div>

        <div class="fcal_create_calendar_form_footer">
            <el-button
                @click="save()"
                :disabled="saving"
                class="fcal_primary_btn_update"
                v-loading="saving">
                Update Notification Settings
            </el-button>
        </div>
    </div>
    <div class="fcal_section_body" v-else>
        <el-skeleton :rows="1" animated />
        <el-skeleton :rows="5" animated />
    </div>
</template>

<script type="text/babel">
import EditNotificationSettings from './__EditNotificationSettings.vue';
import { EditPen, Close } from '@element-plus/icons-vue';

export default {
    name: 'NotificationSettings',
    props: ['slot'],
    components: {
        EditNotificationSettings,
        EditPen,
        Close
    },
    data() {
        return {
            notifications: {},
            loading: false,
            saving: false,
            isEditOpen: [],
            stepIndex: 3
        }
    },
    computed: {
        editButtonText() {
            return (index) => this.isEditOpen[index] ? 'Close' : 'Edit';
        },
    },
    methods: {
        fetch() {
            this.loading = true;
            this.$get('calendars/' + this.slot.calendar.id + '/slots/' + this.slot.id + '/notifications')
                .then(response => {
                    this.notifications = response.notifications;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        save() {
            this.saving = true;
            this.$post('calendars/' + this.slot.calendar.id + '/slots/' + this.slot.id + '/notifications', {
                notifications: this.notifications
            })
                .then(response => {
                    this.$handleSuccess(response);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        toggleEdit(index) {
            const value = this.isEditOpen[index];
            this.isEditOpen = [];
            this.isEditOpen[index] = !value;
        },
        closeEdit(index) {
            this.isEditOpen[index] = false;
        }
    },
    mounted() {
        this.fetch();
    }
}
</script>