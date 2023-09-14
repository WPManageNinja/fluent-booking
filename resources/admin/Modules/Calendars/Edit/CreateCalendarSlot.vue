<template>
    <div style="max-width: 960px; margin: 0 auto;" class="fcal_create_calendar fcal_section">
        <div v-if="slot" class="fcal_section_header">
            <div class="fcal_title">
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item :to="{ name: 'calendars' }">Event Schedulers</el-breadcrumb-item>
                    <el-breadcrumb-item>{{ slot.calendar.title }}</el-breadcrumb-item>
                    <el-breadcrumb-item>Create new event type</el-breadcrumb-item>
                </el-breadcrumb>
            </div>
        </div>
        <div v-if="slot" class="fcal_section_body">
            <h3>Event Information</h3>
            <basic-info :slot="slot" />

            <h3>Scheduling Settings</h3>
            <slot-settings-from :slot="slot" />

            <el-button @click="saveSettings()" :disabled="saving" v-loading="saving" type="success">Create a new scheduling form</el-button>
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

export default {
    name: 'NewSlotEvent',
    props: ['calendar_id'],
    components: {
        SlotSettingsFrom,
        BasicInfo
    },
    data() {
        return {
            slot: null,
            loading: true,
            saving: false
        }
    },
    methods: {
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
        }
    },
    mounted() {
        this.$changeTitle('Create new Event Type');
        this.getSlotSchema();
    }
}
</script>
