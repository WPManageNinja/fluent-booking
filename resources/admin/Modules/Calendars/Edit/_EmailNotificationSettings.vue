<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header"> 
                <h2>
                    <el-icon><Message/></el-icon>
                    {{ $t('Email Notification Settings') }}
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
            <div class="fcal_notification_container_wrap">
                <div :class="['fcal_notification_container', {disabled: !notification.enabled}]"
                    v-for="(notification, index) in notifications" :key="index">
                    <div class="fcal_notification_header">
                        <span :class="['header_left']">
                            {{ notification.title }}
                        </span>
                        <div class="header_right">
                            <span v-if="!notification.enabled" class="fcal_plain_btn disable"> {{ $t('Disabled') }} </span>
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
            <div class="fcal_create_calendar_form_footer">
                <SaveButton :saving="saving" :label="$t('Save Changes')" @save="saveSettings"/>
            </div>

            <el-dialog
                v-model="showEdit"
                v-if="showEdit"
                :title="(editingNotification) ? $t('Edit:') + ' ' + editingNotification.title : $t('Edit Notification')"
                class="fcal_modal fcal_notification_modal"
                :close-on-click-modal="false"
            >
                <EditEmailNotificationSettings :smart_codes="smart_codes" v-if="editingNotification.email" :notification="editingNotification"/>
                <template #footer>
                    <div class="dialog-footer">
                        <el-button class="fcal_primary_btn" :disabled="saving" v-loading="saving" @click="saveSettings">
                            {{ $t('Save Email') }}
                        </el-button>
                    </div>
                </template>
            </el-dialog>

        </div>
        <div v-else class="fcal_section_body">
            <el-skeleton :rows="1" animated/>
            <el-skeleton :rows="5" animated/>
        </div>
        <CloneDrawer
            v-if="isCloneOpen"
            :isOpen="isCloneOpen"
            :eventLists="event_lists"
            :saving="saving"
            :title="$t('Clone Notification Settings')"
            :label="$t('Select Calendar Event')"
            :placeholder="$t('Select Event')"
            :helpText="$t('CalendarEvent/select_notification_settings')"
            :buttonLabel="$t('Clone Settings')"
            @clone="cloneSettings"
            @update:isOpen="isCloneOpen = $event"
        />
    </div>
</template>

<script>
import EditEmailNotificationSettings from './__EditEmailNotificationSettings.vue';
import { EditPen, Close, Message, MoreFilled } from '@element-plus/icons-vue';
import SaveButton from '../../../Components/Buttons/SaveButton.vue';
import CloneDrawer from '../../../Components/Common/CloneDrawer.vue';

export default {
    name: 'EmailNotification',
    props: ['calendar_event', 'event_lists'],
    components: {
        EditEmailNotificationSettings,
        SaveButton,
        EditPen,
        Close,
        Message,
        MoreFilled,
        CloneDrawer
    },
    data() {
        return {
            editingNotification: {},
            showEdit: false,
            notifications: {},
            loading: false,
            saving: false,
            stepIndex: 3,
            isCloneOpen: false,
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
        fetch() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/email-notifications', {
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
            this.$post('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/email-notifications', {
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
            this.$post('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/email-notifications/clone', {
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
                    this.isCloneOpen = false;
                });
        }
    },
    mounted() {
        this.fetch();
    }
}
</script>
