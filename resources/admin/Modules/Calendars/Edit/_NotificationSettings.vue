<template>

    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2> <NoficationIcon/> Email Notification Settings </h2>
        </div>
    </div>

    <div v-if="!loading">
        <div class="fcal_notification_container_wrap">
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
                        <span v-else class="fcal_plain_btn disable"> Disabled </span>
                        <el-switch v-model="notification.enabled" @click="closeEdit(index)"></el-switch>
                    </div>
                </div>
                <div v-if="isEditOpen[index] && notification.enabled" class="fcal_notification_body">
                    <EditNotificationSettings :email="notification.email"/>
                </div>
            </div>
        </div>
        <div class="fcal_create_calendar_form_footer">
            <SaveButton :saving="saving" label="Save Changes" @save="saveSettings"/>
        </div>
    </div>
    <div v-else class="fcal_section_body">
        <el-skeleton :rows="1" animated />
        <el-skeleton :rows="5" animated />
    </div>
</template>

<script type="text/babel">
import EditNotificationSettings from './__EditNotificationSettings.vue';
import { EditPen, Close } from '@element-plus/icons-vue';
import SaveButton from '../../../Components/Buttons/SaveButton.vue';
import NoficationIcon from '../../../Components/Icons/NoficationIcon.vue';

export default {
    name: 'NotificationSettings',
    props: ['slot'],
    components: {
        EditNotificationSettings,
        SaveButton,
        NoficationIcon,
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
        toggleEdit(index) {
            const value = this.isEditOpen[index];
            this.isEditOpen = [];
            this.isEditOpen[index] = !value;
        },
        closeEdit(index) {
            this.isEditOpen[index] = false;
        },
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
        saveSettings() {
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
        }
    },
    mounted() {
        this.fetch();
    }
}
</script>
