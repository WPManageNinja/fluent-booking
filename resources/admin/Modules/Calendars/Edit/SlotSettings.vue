<template>
    <div class="fcal_create_calendar_wrap">

        <div class="fcal_header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item :to="{ name: 'calendars' }">Booking Types</el-breadcrumb-item>
                <el-breadcrumb-item>{{ calendar.author_profile?.name }}</el-breadcrumb-item>
                <el-breadcrumb-item>{{ slot?.title }}</el-breadcrumb-item>
            </el-breadcrumb>
        </div>

        <el-tabs
            v-model="activeTab"
            tab-position="left"
            @tab-change="handleTabChange"
            class="fcal_tabs">
            <el-tab-pane name="basic-info">
                <template #label>
                    <el-icon>
                        <EventIcon/>
                    </el-icon>
                    Event Details
                </template>
                <div class="fcal_create_calendar_body">
                    <el-skeleton v-if="loading"/>
                    <basic-info v-else :slot="slot"/>
                    <div class="fcal_create_calendar_form_footer">
                        <SaveButton :saving="saving" label="Save Changes" @save="saveSettings"/>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane name="schedule-settings">
                <template #label>
                    <el-icon>
                        <ScheduleIcon/>
                    </el-icon>
                    Schedule Settings
                </template>
                <div class="fcal_create_calendar_body">
                    <el-skeleton v-if="loading"/>
                    <ScheduleSettings v-else :slot="slot"/>
                    <div class="fcal_create_calendar_form_footer">
                        <SaveButton :saving="saving" label="Save Changes" @save="saveSettings"/>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane name="notification-settings">
                <template #label>
                    <el-icon>
                        <NoficationIcon/>
                    </el-icon>
                    Notification
                </template>
                <div class="fcal_create_calendar_body">
                    <el-skeleton v-if="loading"/>
                    <NotificationSettings v-else ref="notificationData" :slot="slot"/>
                </div>
            </el-tab-pane>
            <el-tab-pane name="question-settings">
                <template #label>
                    <el-icon><QuestionIcon/></el-icon> Booking Questions
                </template>
                <div class="fcal_create_calendar_body">
                    <el-skeleton v-if="loading"/>
                    <QuestionSettings v-else :activeTab="activeTab" :slot="slot"/>
                </div>
            </el-tab-pane>
            <el-tab-pane name="webhooks-settings">
                <template #label>
                    <el-icon>
                        <Link/>
                    </el-icon>
                    Webhooks Settings
                </template>
                <div class="fcal_create_calendar_body">
                    <el-skeleton v-if="loading"/>
                    <WebhookSettings
                        :event_id="event_id"
                        :calendar_id="calendar_id"
                    />
                </div>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script type="text/babel">
import BasicInfo from './_BasicInfo'
import NotificationSettings from './_NotificationSettings'
import ScheduleSettings from "./_ScheduleSettings";
import QuestionSettings from "./_QuestionSettings";
import EventIcon from '../../../Components/Icons/EventIcon';
import QuestionIcon from '../../../Components/Icons/QuestionIcon';
import ScheduleIcon from '../../../Components/Icons/ScheduleIcon';
import SaveButton from '../../../Components/Buttons/SaveButton';
import NoficationIcon from '../../../Components/Icons/NoficationIcon';
import {Back, Link} from '@element-plus/icons-vue';
import WebhookSettings from "./WebHook/WebhookSettings"

export default {
    name: 'SlotSettings',
    props: ['event_id', 'calendar_id'],
    components: {
        WebhookSettings,
        ScheduleSettings,
        BasicInfo,
        SaveButton,
        NotificationSettings,
        QuestionSettings,
        EventIcon,
        ScheduleIcon,
        NoficationIcon,
        QuestionIcon,
        Back,
        Link
    },
    data() {
        return {
            calendar: {},
            slot: null,
            loading: true,
            saving: false,
            activeTab: 'basic-info'
        }
    },
    methods: {
        getSlot() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_id + '/slots/' + this.event_id, {
                with: ['calendar']
            })
                .then(response => {
                    this.calendar = response.calendar;
                    this.slot = response.slot;
                    this.updateTabValue();
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        updateTabValue() {
            if (this.$route.query.step) {
                this.activeTab = this.$route.query.step;
            }
        },
        handleTabChange() {
            this.$router.push({
                name: 'slot_settings',
                params: {calendar_id: this.slot?.calendar_id, event_id: this.slot?.id},
                query: {step: this.activeTab}
            })
        },
        getMeetingDuration() {
            return this.slot.duration === 'custom' ? this.slot.custom_duration : this.slot.duration;
        },
        saveSettings() {
            this.saving = true;
            this.$post('calendars/' + this.calendar_id + '/slots/' + this.event_id, {
                title: this.slot.title,
                status: this.slot.status,
                color_schema: this.slot.color_schema,
                description: this.slot.description,
                duration: this.getMeetingDuration(),
                settings: this.slot.settings,
                max_book_per_slot: this.slot.max_book_per_slot,
                is_display_spots: this.slot.is_display_spots,
                availability_type: this.slot.availability_type,
                availability_id: this.slot.availability_id,
                location_type: this.slot.location_type,
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
    }
}
</script>
