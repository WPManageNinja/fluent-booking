<template>
    <div class="fcal_create_calendar_wrap">
        <div class="fcal_header">
            <router-link :to="{name: 'calendars'}" class="fcal_back_btn">
                <el-icon><Back /></el-icon> Go Back
            </router-link>
            <h1>
                [Event Title]
            </h1>
        </div>
        <el-tabs
            v-model="activeTab"
            tab-position="left"
            @tab-change="handleTabChange"
            class="fcal_tabs">
            <el-tab-pane name="basic-info">
                <template #label>
                    <el-icon><Calendar /></el-icon> Event Details
                </template>
                <div class="fcal_create_calendar_body">
                    <el-skeleton v-if="loading" />
                    <basic-info v-else :slot="slot" />

                    <div class="fcal_create_calendar_form_footer">
                        <el-button
                            @click="saveSettings()"
                            :disabled="saving"
                            v-loading="saving"
                            class="fcal_primary_btn_update"
                        >
                            Update Settings
                        </el-button>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane name="schedule-settings">
                <template #label>
                    <el-icon><Calendar /></el-icon> Schedule Settings
                </template>
                <div class="fcal_create_calendar_body">
                    <el-skeleton v-if="loading" />
                    <ScheduleSettings v-else :slot="slot" />

                    <div class="fcal_create_calendar_form_footer">
                        <el-button
                            @click="saveSettings()"
                            :disabled="saving"
                            v-loading="saving"
                            class="fcal_primary_btn_update"
                        >
                            Update Settings
                        </el-button>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane name="notification-settings">
                <template #label>
                    <el-icon><Bell /></el-icon> Notification
                </template>
                <div class="fcal_create_calendar_body">
                    <el-skeleton v-if="loading" />
                    <NotificationSettings v-else ref="notificationData" :slot="slot" />

                    <div class="fcal_create_calendar_form_footer">
                        <el-button
                            @click="saveSettings()"
                            :disabled="saving"
                            v-loading="saving"
                            class="fcal_primary_btn_update"
                        >
                            Update Settings
                        </el-button>
                    </div>
                </div>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script type="text/babel">
import SlotSettingsFrom from './_SlotSettingsForm.vue';
import BasicInfo from './_BasicInfo.vue'
import NotificationSettings from './_NotificationSettings.vue'
import ScheduleSettings from "./_ScheduleSettings";
import { Right, Back, Calendar, Bell } from '@element-plus/icons-vue';

export default {
    name: 'SlotSettings',
    props: ['slot_id', 'calendar_id'],
    components: {
        ScheduleSettings,
        SlotSettingsFrom,
        BasicInfo,
        NotificationSettings,
        Right,
        Back,
        Calendar,
        Bell
    },
    data() {
        return {
            slot: null,
            loading: true,
            saving: false,
            activeTab: 'basic-info'
        }
    },
    methods: {
        getSlot() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_id + '/slots/' + this.slot_id)
                .then(response => {
                    this.slot = response.slot;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        handleTabChange() {
            this.$router.push({ name: 'slot_settings', params: { calendar_id: this.slot.calendar_id, slot_id: this.slot.id }, query: { step: this.activeTab } })
        },
        saveSettings() {
            this.saving = true;
            this.$post('calendars/' + this.calendar_id + '/slots/' + this.slot_id, {
                title: this.slot.title,
                description: this.slot.description,
                duration: this.slot.duration,
                settings: this.slot.settings,
                max_book_per_slot: this.slot.max_book_per_slot,
                is_display_spots: this.slot.is_display_spots,
                location_type: 'phone',//this.slot.location_type,
                location_heading: this.slot.location_heading,
                location_settings: this.slot.location_settings
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
        this.$changeTitle('Slot Settings');
        this.getSlot();
        if (this.$route.query.step) {
            this.activeTab = this.$route.query.step
        }
    }
}
</script>
