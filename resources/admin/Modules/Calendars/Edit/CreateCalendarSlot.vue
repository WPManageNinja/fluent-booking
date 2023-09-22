<template>
    <div class="fcal_create_calendar_wrap">
        <div class="fcal_create_calendar_header">
            <h1>Add One-on-One Booking Type</h1>
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
                        <h3>Notification & Question</h3>
                    </template>
                </el-step>
            </el-steps>
        </div>


        <div v-if="slot" class="fcal_create_calendar_body">
            <div v-if="stepIndex == 1" class="fcal_create_calendar_basic_info">
                <basic-info :slot="slot" :event_type="event_type" />
            </div>

            <div v-if="stepIndex == 2" class="fcal_create_calendar_schedule_setting">
                <ScheduleSettings :slot="slot" />
            </div>

            <div class="fcal_create_calendar_form_footer">
                <el-button v-if="stepIndex != 1" class="fcal_plain_btn" @click="backStep">
                    Go Back
                </el-button>
                <el-button class="fcal_primary_btn" @click="handleSaveContinue">
                    {{ stepIndex == 1 ? 'Save and ' : null }}Continue
                </el-button>
            </div>
        </div>
        <div class="fcal_create_calendar_body" v-else-if="loading">
            <el-skeleton :rows="1" animated />
            <el-skeleton :rows="5" animated />
            <el-skeleton :rows="5" animated />
        </div>
    </div>
</template>

<script type="text/babel">
import ScheduleSettings from './_ScheduleSettings';
import BasicInfo from './_BasicInfo.vue';
import { Right } from '@element-plus/icons-vue';

export default {
    name: 'NewSlotEvent',
    props: ['calendar_id', 'event_type'],
    components: {
        ScheduleSettings,
        BasicInfo,
        Right
    },
    data() {
        return {
            slot: null,
            loading: true,
            saving: false,
            stepIndex: 1
        }
    },
    methods: {
        handleSteps(step) {
            this.stepIndex = step;
        },
        getSlotSchema() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_id + '/slot-schema')
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
            this.$post('calendars/' + this.calendar_id + '/slots', {
                title: this.slot.title,
                description: this.slot.description,
                duration: this.slot.duration,
                settings: this.slot.settings,
                location_type: this.slot.location_type,
                location_heading: this.slot.location_heading,
                location_settings: this.slot.location_settings,
                event_type: this.slot.event_type
            })
                .then(response => {
                    this.$handleSuccess(response);
                    this.$router.push({ name: 'slot_settings', params: { calendar_id: response.slot.calendar_id, slot_id: response.slot.id } })
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        backStep() {
            this.stepIndex -= 1;
            if (this.stepIndex <= 1) {
                this.stepIndex = 1;
            }
        },
        handleSaveContinue() {
            this.stepIndex += 1;
            if (this.stepIndex > 3) {
                this.stepIndex = 3;
            }
            console.log(this.stepIndex);
        }
    },
    mounted() {
        this.$changeTitle('Create new Event Type');
        this.getSlotSchema();
    }
}
</script>
