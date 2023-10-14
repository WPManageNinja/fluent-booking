<template>

    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2>
                <NoficationIcon/>
                Email Notification Settings
            </h2>
        </div>
    </div>

    <div v-if="!loading">
        <div class="fcal_notification_container_wrap">
            <div :class="['fcal_notification_container', {disabled: !notification.enabled}]"
                 v-for="(notification, index) in notifications" :key="index">
                <div class="fcal_notification_header">
                    <span :class="['header_left']">
                        {{ notification.title }}
                    </span>
                    <div class="header_right">
                        <span v-if="!notification.enabled" class="fcal_plain_btn disable"> Disabled </span>
                        <span>
                            <el-button @click="toggleEdit(index)" class="fcal_plain_btn">
                                <el-icon><EditPen/></el-icon> Edit
                            </el-button>
                        </span>
                        <el-switch v-model="notification.enabled" @change="saveSettings()"></el-switch>
                    </div>
                </div>
            </div>
        </div>
        <div class="fcal_create_calendar_form_footer">
            <SaveButton :saving="saving" label="Save Changes" @save="saveSettings"/>
        </div>

        <el-dialog
            v-model="showEdit"
            v-if="showEdit"
            :title="(editingNotification) ? 'Edit: ' + editingNotification.title : 'Edit Notification'"
            class="fcal_modal fcal_notification_modal"
            :close-on-click-modal="false"
        >
            <EditNotificationSettings v-if="editingNotification.email" :notification="editingNotification"/>
            <template #footer>
                <div class="dialog-footer">
                    <el-button class="fcal_primary_btn" :disabled="saving" v-loading="saving" @click="saveSettings">
                        Save Email
                    </el-button>
                </div>
            </template>
        </el-dialog>

    </div>
    <div v-else class="fcal_section_body">
        <el-skeleton :rows="1" animated/>
        <el-skeleton :rows="5" animated/>
    </div>
</template>

<script type="text/babel">
import EditNotificationSettings from './__EditNotificationSettings.vue';
import {EditPen, Close} from '@element-plus/icons-vue';
import SaveButton from '../../../Components/Buttons/SaveButton.vue';
import NoficationIcon from '../../../Components/Icons/NoficationIcon.vue';

export default {
    name: 'NotificationSettings',
    props: ['calendar_event'],
    components: {
        EditNotificationSettings,
        SaveButton,
        NoficationIcon,
        EditPen,
        Close
    },
    data() {
        return {
            editingNotification: {},
            showEdit: false,
            notifications: {},
            loading: false,
            saving: false,
            stepIndex: 3
        }
    },
    methods: {
        toggleEdit(notificationKey) {
            if(notificationKey) {
                this.editingNotification = this.notifications[notificationKey];
                this.showEdit = true;
                return;
            }

            this.showEdit = false;
            this.editingKey = '';
        },
        closeEdit() {
            this.showEdit = false;
            this.editingNotification = {};
        },
        fetch() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_event.calendar.id + '/slots/' + this.calendar_event.id + '/notifications', {
                with: ['smart_codes']
            })
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
            this.$post('calendars/' + this.calendar_event.calendar.id + '/slots/' + this.calendar_event.id + '/notifications', {
                notifications: this.notifications
            })
                .then(response => {
                    this.$handleSuccess(response);
                    this.closeEdit();
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
