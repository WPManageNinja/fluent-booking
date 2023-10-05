<template>
    <div class="fcal_create_calendar_wrap">
        <div class="fcal_create_calendar_header">
            <h1>Create a new booking calendar</h1>
        </div>

        <div v-if="calendar.slot" class="fcal_create_calendar_body">
            <div class="fcal_create_calendar_basic_info">
                <basic-info ref="basicInfo" :slot="calendar.slot" :event_type="calendar.slot.event_type" />
            </div>
            <el-form-item label="Select Your Timezone *" class="fcal_global_timezone">
                <time-zone-selector v-model="calendar.author_timezone"/>
            </el-form-item>
            <div class="fcal_create_calendar_form_footer">
                <SaveButton :saving="saving" label="Continue" @save="createCalendar"/>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import WeeklySchedules from './parts/WeeklySchedules.vue';
import TimeZoneSelector from './parts/TimeZoneSelector.vue';
import LocationSelector from './Edit/_LocationSelector.vue';
import HostSelector from '../../Pieces/HostSelector.vue';
import SaveButton from '../../Components/Buttons/SaveButton.vue';
import { Right } from '@element-plus/icons-vue';
import BasicInfo from './Edit/_BasicInfo';

export default {
    name: 'NewCalender',
    props: ['host_id', 'event_type'],
    components: {
        WeeklySchedules,
        TimeZoneSelector,
        LocationSelector,
        HostSelector,
        SaveButton,
        Right,
        BasicInfo
    },
    data() {
        return {
            user_id: '',
            require_slug: false,
            form_step: 'general',
            checking_slug: false,
            calendar: {
                slug: '',
                title: '',
                description: '',
                author_timezone: '',
                user_id: '',
                slot: {
                    duration: '15',
                    title: '',
                    description: '',
                    status: 'active',
                    color_schema: '#0099ff',
                    availability_type: 'existing_schedule',
                    schedule_type: 'weekly_schedules',
                    weekly_schedules: this.appVars.schedule_schema,
                    event_type: 'single',
                    location_type: '',
                    location_heading: '',
                    location_settings: {
                        description: ''
                    }
                }
            },
            saving: false
        }
    },
    methods: {
        createCalendar() {
            this.saving = true;
            this.$post('calendars', {
                calendar: this.calendar
            })
                .then(response => {
                    this.redirectToSetting(response.calendar.id, response.slot.id); 
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        redirectToSetting(calendarId, slotId) {
            this.$router.push({
                name: 'slot_settings',
                params: { calendar_id: calendarId, slot_id: slotId }
            });
            if (this.appVars.is_new) {
                setTimeout(() => {
                    window.location.reload();
                }, 150);
            }
        },
        checkSlug() {
            if (!this.calendar.slug) {
                this.$handleError('Please provide a slug first');
                return;
            }

            if(!isNaN(this.calendar.slug)) {
                this.$handleError('Only number in slug is not allowed');
                return;
            }

            if  (this.calendar.slug.length < 4) {
                this.$handleError('The Slug need to be atleast 4 characters');
                return;
            }

            // check if the slug has special characters or any space. we will only allow alpha-numeric characters
            const isInvalid = this.calendar.slug.match(/[^a-zA-Z0-9_-]/g);
            if(isInvalid) {
                this.$handleError('Your booking slug only accepts alpha-numeric values. Please do not provide any space or special characters');
                return;
            }

            this.checking_slug = true;
            this.$post('calendars/check-slug', {
                slug: this.calendar.slug
            })
                .then(response => {
                    this.form_step = 'general';
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.checking_slug = false;
                });

        }
    },
    mounted() {
        if (this.host_id && this.event_type) {
            this.calendar.slot.event_type = this.event_type;
            this.calendar.user_id = this.host_id;
        }

        if(!this.hasSupport('is_hosted')) {
            this.form_step = 'general';
            this.require_slug = false;
            return;
        }

        if(this.appVars.intended_username) {
            this.calendar.slug = this.appVars.intended_username;
            this.form_step = 'general';
            this.require_slug = true;
            return;
        }

        this.require_slug = true;
        this.form_step = 'slug';
    }
}
</script>
