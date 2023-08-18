<template>
    <div class="fcal_create_calendar fcal_section fcal_section_narrow">
        <div v-if="slot" class="fcal_section_header">
            <div class="fcal_title">
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item :to="{ name: 'calendars' }">Booking Types</el-breadcrumb-item>
                    <el-breadcrumb-item v-if="appVars.supported_features.multi_users">{{ slot.calendar?.user.full_name }}</el-breadcrumb-item>
                    <el-breadcrumb-item>Edit {{ slot.title }}</el-breadcrumb-item>
                </el-breadcrumb>
            </div>
            <div class="fcal_actions">
            </div>
        </div>
        <div v-if="slot" class="fcal_section_body">

            <el-tabs v-model="activeTab">
                <el-tab-pane name="info" label="Event Information">
                    <basic-info :slot="slot" />
                    <el-button @click="saveSettings()" :disabled="saving" v-loading="saving" type="success">Update Event Settings</el-button>
                </el-tab-pane>
                <el-tab-pane name="schedule" label="Scheduling Settings">
                    <slot-settings-from :slot="slot" />
                    <el-button @click="saveSettings()" :disabled="saving" v-loading="saving" type="success">Save Event Settings</el-button>
                </el-tab-pane>
                <el-tab-pane name="notification" label="Notification Settings">
                    <notification-settings v-if="activeTab == 'notification'" :slot="slot" />
                </el-tab-pane>
            </el-tabs>
        </div>
        <div class="fcal_section_body" v-else-if="loading">
            <el-skeleton :rows="1" animated />
            <el-skeleton :rows="5" animated />
            <el-skeleton :rows="5" animated />
        </div>
    </div>
</template>

<script type="text/babel">
import SlotSettingsFrom from './_SlotSettingsForm.vue';
import BasicInfo from './_BasicInfo.vue'
import NotificationSettings from './_NotificationSettings.vue'

export default {
    name: 'SlotSettings',
    props: ['slot_id', 'calendar_id'],
    components: {
        SlotSettingsFrom,
        BasicInfo,
        NotificationSettings
    },
    data() {
        return {
            slot: null,
            loading: true,
            saving: false,
            activeTab: 'info'
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
        saveSettings() {
            this.saving = true;
            this.$post('calendars/' + this.calendar_id + '/slots/' + this.slot_id, {
                title: this.slot.title,
                description: this.slot.description,
                duration: this.slot.duration,
                settings: this.slot.settings,
                max_book_per_slot: this.slot.max_book_per_slot,
                is_display_spots: this.slot.is_display_spots,
                location_type: this.slot.location_type,
                location_heading: this.slot.location_heading,
                location_settings: this.slot.location_settings
            })
                .then(response => {
                    this.$notify.success(response.message);
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
