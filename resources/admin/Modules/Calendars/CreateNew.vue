<template>
    <div class="fcal_create_calendar fcal_section fcal_section_narrow">
        <div class="fcal_section_header">
            <h3>Let's create your first booking form</h3>
        </div>
        <div class="fcal_section_body">
            <div style="max-width: 600px; margin: 0 auto;" v-if="form_step == 'slug'">
                <h3>Please choose your booking slug</h3>
                <el-input placeholder="Your Profile Slug" type="text" v-model="calendar.slug">
                    <template #prepend>{{appVars.site_url}}</template>
                </el-input>
                <el-button @click="checkSlug()" style="margin-top: 30px;" :disabled="checking_slug" v-loading="checking_slug" type="primary" size="large">Continue</el-button>
            </div>
            <div v-else>
                <el-form :model="calendar" label-position="top">
                    <el-form-item label="Title of the booking">
                        <el-input type="text" placeholder="eg: 15 minutes meeting" v-model="calendar.slot.title"/>
                    </el-form-item>
                    <el-row :gutter="20">
                        <el-col :md="12" :xs="24">
                            <el-form-item label="Slot Duration">
                                <el-input type="number" :min="10" placeholder="duration in minutes"
                                          v-model="calendar.slot.duration">
                                    <template #append>minutes</template>
                                </el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :md="12" :xs="24">
                            <el-form-item label="Location *">
                                <location-selector :slot="calendar.slot" />
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-form-item label="Select Your Timezone">
                        <time-zone-selector v-model="calendar.author_timezone"/>
                    </el-form-item>
                    <el-form-item label="Schedule Type">
                        <el-radio-group v-model="calendar.slot.schedule_type">
                            <el-radio-button label="weekly_schedules">
                                Weekly Hours By Day
                            </el-radio-button>
                            <el-radio-button :disabled="true" label="custom_dates">
                                Specific Dates & Hours (coming soon)
                            </el-radio-button>
                        </el-radio-group>
                    </el-form-item>
                    <template v-if="calendar.slot.schedule_type == 'weekly_schedules'">
                        <el-form-item label="Weekly Hours Schedules">
                            <weekly-schedules :weekly_schedules="calendar.slot.weekly_schedules"/>
                        </el-form-item>
                    </template>
                    <el-form-item>
                        <el-button :disabled="saving" v-loading="saving" type="primary" @click="createCalendar">Create
                        </el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import WeeklySchedules from './parts/WeeklySchedules.vue';
import TimeZoneSelector from './parts/TimeZoneSelector.vue';
import LocationSelector from './Edit/_LocationSelector.vue';

export default {
    name: 'NewCalender',
    components: {
        WeeklySchedules,
        TimeZoneSelector,
        LocationSelector
    },
    data() {
        return {
            require_slug: false,
            form_step: 'general',
            checking_slug: false,
            calendar: {
                slug: '',
                title: '',
                description: '',
                author_timezone: '',
                slot: {
                    duration: 15,
                    title: '15 minutes meeting',
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
                    // reload the page
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
                this.$notify.error('Please provide a slug first');
                return;
            }

            if(!isNaN(this.calendar.slug)) {
                this.$notify.error('Only number in slug is not allowed');
                return;
            }

            if  (this.calendar.slug.length < 4) {
                this.$notify.error('The Slug need to be atleast 4 characters');
                return;
            }

            // check if the slug has special characters or any space. we will only allow alpha-numeric characters
            const isInvalid = this.calendar.slug.match(/[^a-zA-Z0-9_-]/g);
            if(isInvalid) {
                this.$notify.error('Your booking slug only accepts alpha-numeric values. Please do not provide any space or special characters');
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
