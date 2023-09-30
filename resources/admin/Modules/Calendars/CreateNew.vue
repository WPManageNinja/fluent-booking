<template>
    <div class="fcal_create_calendar_wrap">
        <div class="fcal_create_calendar_header">
            <h1>Create a new booking calendar</h1>
        </div>

        <div v-if="calendar.slot" class="fcal_create_calendar_body">
            <div class="fcal_create_calendar_basic_info">
                <basic-info ref="basicInfo" :slot="calendar.slot" :event_type="calendar.slot.event_type" />
            </div>

            <div class="fcal_create_calendar_form_footer">
                <el-button class="fcal_primary_btn" @click="createCalendar">
                    Continue
                </el-button>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import WeeklySchedules from './parts/WeeklySchedules.vue';
import TimeZoneSelector from './parts/TimeZoneSelector.vue';
import LocationSelector from './Edit/_LocationSelector.vue';
import HostSelector from '../../Pieces/HostSelector.vue';
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
        Right,
        BasicInfo
    },
    data() {
        return {
            user_id: '',
            require_slug: false,
            form_step: 'general',
            checking_slug: false,
            eventTypes: this.appVars.event_types,
            calendar: {
                slug: '',
                title: '',
                description: '',
                author_timezone: 'Asia/Dhaka',
                user_id: '',
                slot: {
                    duration: 15,
                    title: '',
                    description: '',
                    schedule_type: 'weekly_schedules',
                    weekly_schedules: {
                        sun: {
                            enabled: false,
                            slots: []
                        },
                        mon: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        tue: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        wed: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        thu: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        fri: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        sat: {
                            enabled: false,
                            slots: []
                        }
                    },
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
                    this.saving = false;
                    if(response.redirect_url) {
                        window.location.href = response.redirect_url;
                    }
                    setTimeout(() => {
                        window.location.reload(true);
                    }, 500);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
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
