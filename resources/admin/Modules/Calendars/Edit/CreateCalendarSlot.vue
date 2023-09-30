<template>
    <div class="fcal_create_calendar_wrap">
        <div class="fcal_create_calendar_header">
            <h1>{{ slotTitle }}</h1>
        </div>

        <div v-if="slot" class="fcal_create_calendar_body">
            <div class="fcal_create_calendar_basic_info">
                <basic-info ref="basicInfo" :slot="slot" :event_type="event_type" />
            </div>
            <div class="fcal_create_calendar_form_footer">
                <el-button class="fcal_plain_btn" @click="goToCalendars">
                    Go Back
                </el-button>
                <el-button class="fcal_primary_btn" @click="saveSettings">
                    Continue
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
import BasicInfo from './_BasicInfo.vue';

export default {
    name: 'NewSlotEvent',
    props: ['calendar_id', 'event_type'],
    components: {
        BasicInfo,
    },
    data() {
        return {
            slot: null,
            loading: true,
            saving: false,
        }
    },
    computed:  {
        slotTitle() {
            const eventType = `${this.event_type.charAt(0).toUpperCase()}${this.event_type.slice(1)}`;
            return `Add ${eventType} Booking Type`;
        }
    },
    methods: {
        goToCalendars() {
            this.$router.push({ name: 'calendars' });
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
            const formData  =  this.$refs.basicInfo.formData;
            this.saving = true;
            this.$post('calendars/' + this.calendar_id + '/slots', {
                title: this.slot.title,
                description: this.slot.description,
                duration: this.slot.duration,
                settings: this.slot.settings,
                location_type: this.slot.location_type,
                location_heading: this.slot.location_heading,
                location_settings: this.slot.location_settings,
                event_type: formData.event_type
            })
                .then(response => {
                    this.$handleSuccess(response);
                    this.$router.push({ 
                        name: 'slot_settings', 
                        params: {calendar_id: response.slot.calendar_id, slot_id: response.slot.id},
                        query: {step: 'basic-info' }
                    })
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
    },
    mounted() {
        this.$changeTitle('Create new Event Type');
        this.getSlotSchema();
    }
}
</script>
