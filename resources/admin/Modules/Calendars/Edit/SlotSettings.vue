<template>
    <div class="fcal_create_calendar fcal_section fcal_section_narrow">
        <div v-if="slot" class="fcal_section_header">
            <div class="fcal_title">
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item :to="{ name: 'calendars' }">Event Schedulers</el-breadcrumb-item>
                    <el-breadcrumb-item>{{ slot.calendar.title }}</el-breadcrumb-item>
                    <el-breadcrumb-item>Edit {{ slot.title }}</el-breadcrumb-item>
                </el-breadcrumb>
            </div>
            <div class="fcal_actions">
                <el-button @click="saveSettings()" :disabled="saving" v-loading="saving" type="success">Save Event Settings</el-button>
            </div>
        </div>
        <div v-if="slot" class="fcal_section_body">
            <slot-settings-from :slot="slot" />
            <el-button @click="saveSettings()" :disabled="saving" v-loading="saving" type="success">Save Event Settings</el-button>
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

export default {
    name: 'SlotSettings',
    props: ['slot_id', 'calendar_id'],
    components: {
        SlotSettingsFrom
    },
    data() {
        return {
            slot: null,
            loading: true,
            saving: false
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
                settings: this.slot.settings
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
