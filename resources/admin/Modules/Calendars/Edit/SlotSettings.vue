<template>
    <div class="fcal_create_calendar_wrap">

        <div class="fcal_header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item :to="{ name: 'calendars' }">Booking Types</el-breadcrumb-item>
                <el-breadcrumb-item>{{ calendar.author_profile?.name }}</el-breadcrumb-item>
                <el-breadcrumb-item>{{ slot?.title }}</el-breadcrumb-item>
            </el-breadcrumb>

            <div class="fcal_actions">
                <el-button class="fcal_plain_btn fcal_copy_btn" @click="copyTo(slot?.id)">
                    <el-icon><CopyDocument /></el-icon> [fluent_booking id="{{ slot?.id }}"]
                </el-button>
<!--                <el-button class="fcal_plain_btn">-->
<!--                    <el-icon><View /></el-icon> View LandingPage-->
<!--                </el-button>-->
            </div>
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
                <div v-if="activeTab == 'basic-info'" class="fcal_create_calendar_body">
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
                <div v-if="activeTab == 'schedule-settings'" class="fcal_create_calendar_body">
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
                        <Message/>
                    </el-icon>
                    Email Notifications
                </template>
                <div v-if="activeTab == 'notification-settings'" class="fcal_create_calendar_body">
                    <el-skeleton v-if="loading"/>
                    <NotificationSettings v-else ref="notificationData" :slot="slot"/>
                </div>
            </el-tab-pane>

            <el-tab-pane name="question-settings">
                <template #label>
                    <el-icon><QuestionIcon/></el-icon> Booking Questions
                </template>
                <div v-if="activeTab == 'question-settings'" class="fcal_create_calendar_body">
                    <el-skeleton v-if="loading"/>
                    <QuestionSettings v-else :activeTab="activeTab" :slot="slot"/>
                </div>
            </el-tab-pane>

            <el-tab-pane name="webhooks-settings">
                <template #label>
                    <el-icon>
                        <Link/>
                    </el-icon>
                    Webhooks Feeds
                </template>
                <div v-if="activeTab == 'webhooks-settings'" class="fcal_create_calendar_body">
                    <el-skeleton v-if="loading"/>
                    <WebhookSettings 
                        v-else 
                        :activeTab="activeTab"
                        :event_id="event_id"
                        :calendar_id="calendar_id"
                    />
                </div>
            </el-tab-pane>
            
            <el-tab-pane name="payment-settings">
              <template #label>
                <el-icon>
                  <Money/>
                </el-icon>
                Payment Settings
              </template>
              <div class="fcal_create_calendar_body" v-if="activeTab === 'payment-settings'">
                <el-skeleton v-if="loading"/>
                <payment-settings 
                    v-else 
                    :activeTab="activeTab"
                    :event_id="event_id"
                    :calendar_id="calendar_id"
                />
              </div>
            </el-tab-pane>

            <el-tab-pane name="integrations">
              <template #label>
                <el-icon>
                    <Connection/>
                </el-icon>
                Integrations
              </template>
              <div class="fcal_create_calendar_body" v-if="activeTab === 'integrations'">
                <el-skeleton v-if="loading"/>
                <integration
                    v-else 
                    :activeTab="activeTab"
                    :event_id="event_id"
                    :calendar_id="calendar_id"
                    :has_pro="true"
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
import QuestionSettings from "./_QuestionSettings.vue";
import EventIcon from '../../../Components/Icons/EventIcon';
import QuestionIcon from '../../../Components/Icons/QuestionIcon';
import ScheduleIcon from '../../../Components/Icons/ScheduleIcon';
import SaveButton from '../../../Components/Buttons/SaveButton';
import NoficationIcon from '../../../Components/Icons/NoficationIcon';
import {Back, Link, Message, View, CopyDocument, Money, Connection} from '@element-plus/icons-vue';
import WebhookSettings from "./WebHook/WebhookSettings";
import { copyToClipBoard } from '@/Bits/data_config.js';
import PaymentSettings from "./Payments/PaymentSettings.vue";
import Integration from './GeneralIntegration/Integration.vue';

export default {
    name: 'SlotSettings',
    props: ['event_id', 'calendar_id'],
    components: {
        WebhookSettings,
        ScheduleSettings,
        PaymentSettings,
        BasicInfo,
        SaveButton,
        NotificationSettings,
        QuestionSettings,
        EventIcon,
        ScheduleIcon,
        NoficationIcon,
        QuestionIcon,
        Back,
        Link,
        View,
        CopyDocument,
        Money,
        Message,
        Connection,
        Integration
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
        getLocationSettings() {
            return [{
                type: this.slot.location_settings[0].type,
                title: this.slot.location_settings[0].title,
                description: this.slot.location_settings[0].description,
                host_phone_number: this.slot.location_settings[0].host_phone_number
            }]
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
                location_settings: this.getLocationSettings()
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
        copyTo(text) {
            const CopyText = '[fluent_booking id="'+text+'"]';
            copyToClipBoard(CopyText);

            this.$handleSuccess('Shortcode has been copied to your clipboard');
        },
    },
    mounted() {
        this.$changeTitle('Slot Settings');
        this.getSlot();
    }
}
</script>
