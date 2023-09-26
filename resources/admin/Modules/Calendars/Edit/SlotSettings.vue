<template>
    <div class="fcal_create_calendar_wrap">
        <div class="fcal_create_calendar_header">
            <h1>Edit One-on-One Booking Type</h1>
            <el-steps class="fcal_steps" :space="200" :active="stepIndex">
                <el-step>
                    <template #title>
                        <h3 @click="handleSteps(1)">Event Info <el-icon><Right /></el-icon></h3>
                    </template>
                </el-step>
                <el-step>
                    <template #title>
                        <h3 @click="handleSteps(2)">Schedule Settings <el-icon><Right /></el-icon></h3>
                    </template>
                </el-step>
                <el-step>
                    <template #title>
                        <h3 @click="handleSteps(3)">Notification & Question</h3>
                    </template>
                </el-step>
            </el-steps>
        </div>

        <div v-if="slot" class="fcal_create_calendar_body">
            <div v-if="stepIndex == 1" class="fcal_create_calendar_basic_info">
                <basic-info ref="basicInfo" :slot="slot" />
            </div>

            <div v-if="stepIndex == 2" class="fcal_create_calendar_schedule_setting">
                <ScheduleSettings :slot="slot" />
            </div>

            <div v-if="stepIndex == 3" class="fcal_create_calendar_notification_setting">
                <NotificationSettings ref="notificationData" :slot="slot" />
            </div>


            <div class="fcal_create_calendar_form_footer">
                <el-button v-if="stepIndex != 1" class="fcal_plain_btn" @click="backStep">
                    Go Back
                </el-button>

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
    </div>
</template>

<script type="text/babel">
import SlotSettingsFrom from './_SlotSettingsForm.vue';
import BasicInfo from './_BasicInfo.vue'
import NotificationSettings from './_NotificationSettings.vue'
import ScheduleSettings from "./_ScheduleSettings";
import { Right } from '@element-plus/icons-vue';

export default {
    name: 'SlotSettings',
    props: ['slot_id', 'calendar_id'],
    components: {
        ScheduleSettings,
        SlotSettingsFrom,
        BasicInfo,
        NotificationSettings,
        Right
    },
    data() {
        return {
            slot: null,
            loading: true,
            saving: false,
            activeTab: 'info',
            stepIndex: 1
        }
    },
    methods: {
        handleSteps(step) {
            this.stepIndex = step;
            this.$router.push({ name: 'slot_settings', params: { calendar_id: this.slot.calendar_id, slot_id: this.slot.id }, query: { step: step } })

        },
        backStep() {
            this.stepIndex -= 1;
            if (this.stepIndex <= 1) {
                this.stepIndex = 1;
            }
            this.$router.push({ name: 'slot_settings', params: { calendar_id: this.slot.calendar_id, slot_id: this.slot.id }, query: { step: this.stepIndex } })
        },
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
            this.stepIndex = this.$route.query.step;
        }
    }
}
</script>
