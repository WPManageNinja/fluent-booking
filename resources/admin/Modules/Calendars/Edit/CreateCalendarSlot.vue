<template>
    <div class="fcal_create_calendar_wrap">
        <div class="fcal_create_calendar_header">
            <router-link :to="{name: 'calendars'}" class="fcal_back_btn">
                <el-icon :size="20" color="black"><Back/></el-icon>
                <h1>{{ slotTitle }}</h1>
            </router-link>
        </div>

        <div v-if="slot" class="fcal_create_calendar_body">
            <div class="fcal_create_calendar_basic_info">
                <event-details ref="basicInfo" :calendar_event="slot" :event_type="event_type" :new_event="true" />
            </div>
            <div class="fcal_create_calendar_form_footer">
                <el-button class="fcal_primary_btn" @click="saveSettings">
                    {{ $t('Continue') }}
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
import EventDetails from './_EventDetails.vue';
import {Back} from '@element-plus/icons-vue';


export default {
    name: 'NewSlotEvent',
    props: ['calendar_id', 'event_type'],
    components: {
        EventDetails,
        Back
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
            const eventType = this.event_type == 'group' ? this.$t('Group') : this.$t('One-to-One');
            return `${this.$t('Add')} ${eventType} ${this.$t('Booking Type')}`;
        }
    },
    methods: {
        getEventSchema() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_id + '/event-schema')
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
        getMeetingDuration() {
            return this.slot.duration === 'custom' ? this.slot.custom_duration : this.slot.duration;
        },
        checkValidation() {
            for (const location of this.slot.location_settings) {
                if (!location.type) {
                    this.$handleError(this.$t('Location Type is required'));
                    return false;
                } else if ((location.type == 'custom') && !location.title) {
                    this.$handleError(this.$t('Location Title is required'));
                    return false;
                } else if ((location.type == 'in_person_organizer' || location.type == 'custom') && !location.description) {
                    this.$handleError(this.$t('Location Description is required'));
                    return false;
                } else if (location.type == 'phone_organizer' && !location.host_phone_number) {
                    this.$handleError(this.$t('Phone Number is required'));
                    return false;
                }
            }
            return true;
        },
        saveSettings() {
            if (!this.checkValidation()) return;
            
            this.saving = true;
            this.$post('calendars/' + this.calendar_id + '/events', {
                title: this.slot.title,
                status: this.slot.status,
                color_schema: this.slot.color_schema,
                description: this.slot.description,
                duration: this.getMeetingDuration(),
                max_book_per_slot: this.slot.max_book_per_slot,
                is_display_spots: this.slot.is_display_spots,
                settings: this.slot.settings,
                location_type: this.slot.location_type,
                location_heading: this.slot.location_heading,
                location_settings: this.slot.location_settings,
                event_type: this.slot.event_type
            })
                .then(response => {
                    this.$handleSuccess(response);
                    this.$router.push({ 
                        name: 'event_details', 
                        params: {calendar_id: response.slot.calendar_id, event_id: response.slot.id},
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
        this.$changeTitle(this.$t('Create new Event Type'));
        this.getEventSchema();
    }
}
</script>
