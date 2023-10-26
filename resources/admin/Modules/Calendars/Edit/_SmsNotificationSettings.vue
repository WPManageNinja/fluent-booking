<template>

    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2>
                <el-icon>
                    <Notification/>
                </el-icon>
                SMS Notification Settings
            </h2>
        </div>
    </div>

    <div v-if="!loading">
        <div v-if="notifications">
            <div class="fcal_notification_container_wrap">
                <div :class="['fcal_notification_container', {disabled: !notification.enabled}]"
                    v-for="(notification, index) in notifications" :key="index">
                    <div class="fcal_notification_header">
                        <span :class="['header_left']">
                            {{ notification.title }}
                        </span>
                        <div class="header_right">
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
                <EditSmsNotificationSettings 
                    v-if="editingNotification.sms"
                    :smart_codes="smart_codes"
                    :host_phone="host_phone"
                    :calendar_id="calendar_event.calendar_id"
                    :notification="editingNotification"
                />
                <template #footer>
                    <div class="dialog-footer">
                        <el-button class="fcal_primary_btn" :disabled="saving" v-loading="saving" @click="saveSettings">
                            Save SMS
                        </el-button>
                    </div>
                </template>
            </el-dialog>
        </div>
        <div v-else>
            <p>You didn't configure twilio yet. Please configure from <span><el-link @click="goToTwilioSettings">here</el-link></span> </p>
        </div>
    </div>
    <div v-else class="fcal_section_body">
        <el-skeleton :rows="1" animated/>
        <el-skeleton :rows="5" animated/>
    </div>
</template>

<script type="text/babel">
import EditSmsNotificationSettings from './__EditSmsNotificationSettings.vue';
import {EditPen, Close, Notification} from '@element-plus/icons-vue';
import SaveButton from '../../../Components/Buttons/SaveButton.vue';
import NoficationIcon from '../../../Components/Icons/NoficationIcon.vue';

export default {
    name: 'SmsNotificationSettings',
    props: ['calendar_event', 'host_phone'],
    components: {
        EditSmsNotificationSettings,
        SaveButton,
        NoficationIcon,
        Notification,
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
            stepIndex: 3,
            smart_codes: {
                texts: {},
                html: {}
            }
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
        goToTwilioSettings() {
            this.$router.push({
                name: 'configure-integrations',
                params: { settings_key: 'twilio' }
            });
        },
        fetch() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_event.calendar.id + '/slots/' + this.calendar_event.id + '/sms-notifications', {
                with: ['smart_codes']
            })
                .then(response => {
                    this.notifications = response.notifications;
                    this.smart_codes = response.smart_codes;
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
            this.$post('calendars/' + this.calendar_event.calendar.id + '/slots/' + this.calendar_event.id + '/sms-notifications', {
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
