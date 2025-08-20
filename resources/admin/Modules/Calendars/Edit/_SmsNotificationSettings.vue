<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header">
                <h2>
                    <el-icon><Notification/></el-icon>
                    {{ $t('SMS Notification Settings') }}
                </h2>
                <div class="fcal_header_action">
                    <el-dropdown trigger="click" popper-class="fcal_select">
                        <span class="el-dropdown-link">
                            <el-icon><MoreFilled/></el-icon>
                        </span>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item
                                    @click="isCloneOpen = true">
                                    {{ $t('Clone from') }}
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
            </div>
        </div>

        <div v-if="!loading">
            <template v-if="!disabled">
                <div v-if="notifications">
                    <div class="fcal_notification_container_wrap">
                        <div class="fcal_notification_title">
                            <h3> {{ $t('Notification Settings') }}</h3>
                            <p>{{ $t('Customize the sms notifications sent to attendees and organizers') }}</p>
                        </div>
                        <div class="fcal_notifications">
                            <div :class="['fcal_notification_container', {disabled: !notification.enabled}]"
                                v-for="(notification, index) in notificationSettings" :key="index">
                                <div class="fcal_notification_header">
                                    <span :class="['header_left']">
                                        {{ notification.title }}
                                    </span>
                                    <div class="header_right">
                                        <span>
                                            <el-button @click="toggleEdit(index)" class="fcal_plain_btn">
                                                <el-icon><EditPen/></el-icon> {{ $t('Edit') }}
                                            </el-button>
                                        </span>
                                        <el-switch v-model="notification.enabled" @change="saveSettings()"></el-switch>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="fcal_notification_container_wrap">
                        <div class="fcal_notification_title">
                            <h3> {{ $t('Other Notifications') }}</h3>
                            <p>{{ $t('Optimize your sms notifications for confirmations and declines') }}</p>
                        </div>
                        <div class="fcal_notifications">
                            <div :class="['fcal_notification_container', {disabled: !notification.enabled}]"
                                v-for="(notification, index) in otherNotifications" :key="index">
                                <div class="fcal_notification_header">
                                    <span :class="['header_left']">
                                        {{ notification.title }}
                                    </span>
                                    <div class="header_right">
                                        <span>
                                            <el-button @click="toggleEdit(index)" class="fcal_plain_btn">
                                                <el-icon><EditPen/></el-icon> {{ $t('Edit') }}
                                            </el-button>
                                        </span>
                                        <el-switch v-model="notification.enabled" @change="saveSettings()"></el-switch>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <el-dialog
                        v-model="showEdit"
                        v-if="showEdit"
                        :title="(editingNotification) ? $t('Edit:')+' ' + editingNotification.title : $t('Edit Notification')"
                        class="fcal_modal fcal_notification_modal"
                        :close-on-click-modal="false"
                    >
                        <EditSmsNotificationSettings 
                            v-if="editingNotification.sms"
                            :smart_codes="smart_codes"
                            :host_phone="calendar_event.calendar.author_profile.phone"
                            :calendar_id="calendar_event.calendar_id"
                            :notification="editingNotification"
                        />
                        <template #footer>
                            <div class="dialog-footer">
                                <el-button class="fcal_primary_btn" :disabled="saving" v-loading="saving" @click="saveSettings">
                                    {{ $t('Save SMS') }}
                                </el-button>
                            </div>
                        </template>
                    </el-dialog>
                </div>
                <div v-else>
                    <p>{{ $t('SmsNotificationSettings/configure_twilio_desc') }} 
                        <span><el-link @click="goToTwilioSettings">{{ $t('here') }}</el-link></span>
                    </p>
                </div>
            </template>
            <ProNotice v-else/>
        </div>
        <div v-else class="fcal_section_body">
            <el-skeleton :rows="1" animated/>
            <el-skeleton :rows="5" animated/>
        </div>
        <CloneDrawer
            v-if="isCloneOpen"
            :isOpen="isCloneOpen"
            :eventId="calendar_event.id"
            :eventLists="event_lists"
            :saving="saving"
            :title="$t('Clone SMS Settings')"
            :label="$t('Select Calendar Event')"
            :placeholder="$t('Select Event')"
            :helpText="$t('CalendarEvent/select_sms_settings')"
            :buttonLabel="$t('Clone Settings')"
            @clone="cloneSettings"
            @update:isOpen="isCloneOpen = $event"
        />
    </div>
</template>

<script>
import EditSmsNotificationSettings from './__EditSmsNotificationSettings.vue';
import { Close, Notification, MoreFilled } from '@element-plus/icons-vue';
import SaveButton from '../../../Components/Buttons/SaveButton.vue';
import NoficationIcon from '../../../Components/Icons/NoficationIcon.vue';
import ProNotice from '@/Components/Common/ProNotice.vue';
import CloneDrawer from '@/Components/Common/CloneDrawer.vue';

export default {
    name: 'SmsNotificationSettings',
    props: ['calendar_event', 'disabled', 'event_lists'],
    components: {
        EditSmsNotificationSettings,
        SaveButton,
        NoficationIcon,
        Notification,
        Close,
        ProNotice,
        MoreFilled,
        CloneDrawer
    },
    data() {
        return {
            editingNotification: {},
            showEdit: false,
            isCloneOpen: false,
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
    computed: {
        notificationSettings() {
            const otherSettings = ['booking_request_attendee', 'booking_request_host', 'declined_by_host'];
            let notificationsArray = Object.entries(this.notifications);
            notificationsArray = notificationsArray.filter(([key, field]) => !otherSettings.includes(key));
            return Object.fromEntries(notificationsArray);
        },
        otherNotifications() {
            const otherSettings = ['booking_request_attendee', 'booking_request_host', 'declined_by_host'];
            let notificationsArray = Object.entries(this.notifications);
            notificationsArray = notificationsArray.filter(([key, field]) => otherSettings.includes(key));
            return Object.fromEntries(notificationsArray);
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
            this.$get('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/sms-notifications', {
                calendar_id: this.calendar_event.calendar_id,
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
            this.$post('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/sms-notifications', {
                calendar_id: this.calendar_event.calendar_id,
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
        },
        cloneSettings(eventId) {
            this.saving = true;
            this.$post('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/sms-notifications/clone', {
                calendar_id: this.calendar_event.calendar_id,
                from_event_id: eventId
            })
                .then(response => {
                    this.$handleSuccess(response);
                    this.notifications = response.notifications;
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
        if (!this.disabled) {
            this.fetch();
        }
    }
}
</script>
