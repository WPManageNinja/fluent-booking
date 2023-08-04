<template>
    <div v-if="!loading">
        <div class="fcal_notification_container" v-for="(notification, index) in notifications" :key="index">
            <div class="fcal_notification_header">
                <span :class="['header_left', {active: isEditOpen[index]}]">
                    {{ notification.title }}
                </span>
                <div class="header_right">
                    <span v-if="notification.enabled">
                        <el-link @click="toggleEdit(index)" type="primary">
                            {{ editButtonText(index) }}
                        </el-link>
                    </span>
                    <span style="color: red;" v-else>Disabled</span>
                    <el-switch v-model="notification.enabled" @click="closeEdit(index)"></el-switch>
                </div>
            </div>
            <div v-if="isEditOpen[index] && notification.enabled">
                <EditNotificationSettings :email="notification.email"/>
            </div>
        </div>
        <div class="fcal_notification_btn">
            <el-button
                type="success"
                @click="save()"
                :disabled="saving"
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

export default {
    name: 'NotificationSettings',
    props: ['slot'],
    components: {
        EditNotificationSettings
    },
    data() {
        return {
            notifications: {},
            loading: false,
            saving: false,
            isEditOpen: []
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
            this.$get('calendars/' + this.slot.calendar_id + '/slots/' + this.slot.id + '/notifications')
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
            this.$post('calendars/' + this.slot.calendar_id + '/slots/' + this.slot.id + '/notifications', {
                notifications: this.notifications
            })
                .then(response => {
                    this.$notify.success(response.message);
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