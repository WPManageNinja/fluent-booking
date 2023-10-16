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
                <basic-info ref="basicInfo" :slot="slot" :event_type="event_type" />
            </div>
            <div class="fcal_create_calendar_form_footer">
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
import {Back} from '@element-plus/icons-vue';


export default {
    name: 'NewSlotEvent',
    props: ['calendar_id', 'event_type'],
    components: {
        BasicInfo,
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
            const eventType = this.event_type == 'group' ? 'Group' : 'One-to-One';
            return `Add ${eventType} Booking Type`;
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
        getMeetingDuration() {
            return this.slot.duration === 'custom' ? this.slot.custom_duration : this.slot.duration;
        },
        checkValidattion() {
            const location = this.slot.location_settings[0];
            if (!location.type) {
                this.$handleError('Location is required');
                return false;
            } else if ((location.type == 'custom') && !location.custom_title)  {
                this.$handleError('Location Title is required');
                return false;
            } else if ((location.type == 'in_person_organizer' || location.type == 'custom') && !location.description)  {
                this.$handleError('Location Description is required');
                return false;
            } else if (location.type == 'phone_organizer' && !location.host_phone_number) {
                this.$handleError('Phone Number is required');
                return false;
            }
            return true;
        },
        saveSettings() {
            if (!this.checkValidattion()) {
                return;
            }
            this.saving = true;
            this.$post('calendars/' + this.calendar_id + '/slots', {
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
                        name: 'slot_settings', 
                        params: {calendar_id: response.slot.calendar_id, event_id: response.slot.id},
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
