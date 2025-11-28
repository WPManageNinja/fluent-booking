<template>
    <div class="fcal_create_calendar_wrap fcal_onboard_wrap">
        <div v-if="is_board" class="fcal_welcome_banner">
            <h1>
                <svg class="welcome-svg" xmlns="http://www.w3.org/2000/svg" width="27" height="29" viewBox="0 0 27 29" fill="none">
                    <path class="path1" fill-rule="evenodd" clip-rule="evenodd" d="M8.85634 27.063C6.32888 26.3537 3.68272 25.9959 1.11577 25.6681C0.562888 25.5994 0.0495755 25.9829 0.010084 26.5231C-0.0688991 27.0638 0.326248 27.5582 0.839638 27.6269C3.3276 27.9424 5.89432 28.2801 8.30331 28.9629C8.8167 29.111 9.36981 28.8061 9.52778 28.2817C9.68574 27.7576 9.36973 27.2115 8.85634 27.063Z" fill="white"/>
                    <path class="path1" fill-rule="evenodd" clip-rule="evenodd" d="M16.5174 17.0071C12.4498 12.8561 7.86874 9.23949 3.8801 4.97717C3.52467 4.5791 2.89281 4.55777 2.49789 4.92939C2.10298 5.3014 2.06364 5.92694 2.45856 6.32501C6.4472 10.5996 11.0282 14.2281 15.0958 18.3913C15.4908 18.7799 16.1225 18.7854 16.5174 18.4031C16.8728 18.0212 16.9123 17.3957 16.5174 17.0071Z" fill="white"/>
                    <path class="path1" fill-rule="evenodd" clip-rule="evenodd" d="M24.1398 1.0409C24.2582 3.41671 24.3765 5.79252 24.495 8.16873C24.495 8.71292 24.9691 9.13311 25.522 9.10665C26.0749 9.0798 26.4695 8.61656 26.4695 8.07198C26.3511 5.69222 26.2328 3.31285 26.1143 0.933484C26.0749 0.389291 25.6009 -0.028135 25.048 0.00148363C24.5346 0.0311023 24.1003 0.497104 24.1398 1.0409Z" fill="white"/>
                </svg>
                {{ $t('Congratulations!') }}
                <PartyIcon class="party_icon" />
            </h1>
            <p>{{ $t('Thank You For Choosing FluentBooking. Let\'s -') }} <b>{{ $t('Create Your First Booking Event') }}</b> <br>{{ $t('(Will Take Less Than a Minute!)') }}</p>
        </div>
        <div class="fcal_create_calendar_header">
            <h1 v-if="!is_board" class="fcal_calendar_header_title" @click="$router.push({name: 'calendars'})">
                <el-icon><Back /></el-icon> {{ calendarTitle }}
            </h1>
        </div>
        <div v-if="calendar.slot && !onboarding" class="fcal_create_calendar_body" :class="step==2 ? 'fcal_step_2_active' : ''">
            <div class="fcal_onboard_steps">
                <div v-if="step==1" class="fcal_onboard_step step-1">
                    <div class="fcal_create_calendar_form_header">
                        <h2> {{ $t('Setup Recommended Features') }} </h2>
                    </div>
                    <div v-if="!loading" class="fcal_features_boxes">
                        <div v-if="!hasFeatures.has_fluentcrm" class="fcal_feature">
                            <div class="fcal_feature_heading">
                                <h3>{{ $t('Marketing Automation with FluentCRM') }}</h3>
                                <p>
                                    {{ $t('Segment your guests, send bulk emails, run automations using FluentCRM.') }}
                                    <span style="font-weight: bold;" v-if="featuresSettings.install_fluentcrm != 'yes'">
                                        {{ $t('We highly recommend to enable this feature.') }}
                                    </span>
                                </p>
                            </div>
                            <div class="fcal_feature_actions">
                                <el-checkbox size="large" v-model="featuresSettings.install_fluentcrm" true-label="yes" false-label="no" class="fcal_checkbox"/>
                            </div>
                        </div>
                        <div v-if="!hasFeatures.has_fluentsmtp" class="fcal_feature">
                            <div class="fcal_feature_heading">
                                <h3>{{ $t('Reliable Email delivery with FluentSMTP') }}</h3>
                                <p>
                                    {{ $t('Onboarding/fluent_smtp_description') }}
                                    <span style="font-weight: bold;" v-if="featuresSettings.install_fluentsmtp != 'yes'">
                                        {{ $t('We recommend this for reliable email delivery.') }}
                                    </span>
                                </p>
                            </div>
                            <div class="fcal_feature_actions">
                                <el-checkbox size="large" v-model="featuresSettings.install_fluentsmtp" true-label="yes" false-label="no" class="fcal_checkbox"/>
                            </div>
                        </div>
                        <div v-if="!hasFeatures.has_fluentcart" class="fcal_feature">
                            <div class="fcal_feature_heading">
                                <h3>{{ $t('Monetize your booking with FluentCart') }}</h3>
                                <p>
                                    {{ $t('Seamlessly integrate FluentCart to sell paid bookings and manage products directly from your appointments.') }}
                                    <span style="font-weight: bold;" v-if="featuresSettings.install_fluentcart != 'yes'">
                                        {{ $t('We highly recommend this for monetization.') }}
                                    </span>
                                </p>
                            </div>
                            <div class="fcal_feature_actions">
                                <el-checkbox size="large" v-model="featuresSettings.install_fluentcart" true-label="yes" false-label="no" class="fcal_checkbox"/>
                            </div>
                        </div>
                    </div>
                    <el-skeleton v-else :rows="3" animated />
                </div>

                <div v-if="step==2" class="fcal_onboard_step step-2">
                    <div class="fcal_create_calendar_basic_info">
                        <event-details
                            ref="basicInfo"
                            :is_board="is_board"
                            :calendar_event="calendar.slot"
                            :event_type="calendar.slot.event_type"
                            :new_event="true"
                            @saveAndGotoSetting="saveAndGotoSetting"
                        />
                    </div>
                    <el-form-item :label="$t('Select Your Timezone *')" class="fcal_global_timezone fcal_event_timezone">
                        <time-zone-selector v-model="calendar.author_timezone"/>
                    </el-form-item>
                </div>

                <div v-if="step==3" class="fcal_onboard_step step-3">
                    <div class="fcal_create_calendar_form_header">
                        <h2>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2C17.52 2 22 6.48 22 12Z" stroke="#1B2533" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M15.7099 15.1798L12.6099 13.3298C12.0699 13.0098 11.6299 12.2398 11.6299 11.6098V7.50977" stroke="#1B2533" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $t('Create Your Availability') }}
                        </h2>
                    </div>
                    <WeeklySchedules
                        :weekly_schedules="calendar.slot?.weekly_schedules"
                        :title="$t('Weekly Hours')"
                    />
                </div>
            </div>

            <div class="fcal_create_calendar_form_footer">
                <el-button v-if="step==1 && is_host_calendar" class="fcal_primary_btn" @click="handleStep(2)">{{ $t('Continue') }}</el-button>
                <el-button v-if="step==2 && is_board" class="fcal_plain_btn" @click="handleStep(1)">{{ $t('Back') }}</el-button>
                <el-button v-if="step==3 && is_host_calendar" class="fcal_plain_btn" @click="handleStep(2)">{{ $t('Back') }}</el-button>
                <el-button v-if="step==2 && is_host_calendar" class="fcal_primary_btn" @click="handleStep(3)">{{ $t('Continue') }}</el-button>
                <SaveButton v-if="step==3 || !is_host_calendar" :saving="saving" :label="$t('Continue')" @save="createCalendar"/>
            </div>
        </div>
        <div v-else-if="onboarding" class="fcal_create_calendar_body">
            <div class="fcom_onboarding_steps">
                <div class="fcom_step_header">
                    <h2 class="header_title">{{ onboardingText }}</h2>
                </div>
                <div class="fcom_step">
                    <el-skeleton :animated="true" :rows="3"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import WeeklySchedules from './parts/WeeklySchedules.vue';
import TimeZoneSelector from './parts/TimeZoneSelector.vue';
import HostSelector from '../../Pieces/HostSelector.vue';
import SaveButton from '../../Components/Buttons/SaveButton.vue';
import { Right, Back } from '@element-plus/icons-vue';
import EventDetails from './Edit/_EventDetails';
import PartyIcon from "@/Pieces/PartyIcon";

export default {
    name: 'NewCalender',
    props: ['host_id', 'event_type', 'is_board'],
    components: {
        PartyIcon,
        WeeklySchedules,
        TimeZoneSelector,
        HostSelector,
        SaveButton,
        Right,
        EventDetails,
        Back
    },
    data() {
        return {
            user_id: '',
            is_host_calendar: true,
            require_slug: false,
            calendar: {
                slug: '',
                title: '',
                type: 'simple',
                description: '',
                author_timezone: '',
                user_id: '',
                slot: {
                    duration: '30',
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
                    location_settings: [
                        {
                            type: '',
                            title: '',
                            description: '',
                            host_phone_number: ''
                        }
                    ],
                    settings: {
                        reserve_time: false,
                        available_times: {},
                        location_fields: this.appVars.location_fields
                    }
                }
            },
            saving: false,
            loading: false,
            onboarding: false,
            step: this.is_board ? 1 : 2,
            redirectRoute: 'event_details',
            onboardingText: this.$t('Please wait while we save your settings'),
            hasFeatures: {
                has_fluentcrm: !!this.appVars.features?.has_fluentcrm,
                has_fluentsmtp: !!this.appVars.features?.has_fluentsmtp,
                has_fluentcart: !!this.appVars.features?.has_fluentcart,
            },
            featuresSettings: {
                install_fluentcrm: 'yes',
                install_fluentsmtp: 'yes',
                install_fluentcart: 'yes'
            }
        }
    },
    computed: {
        calendarTitle() {
            const bookingType = this.getEventType(this.event_type);
            return `${this.$t('Add')} ${bookingType} ${this.$t('Booking Type')}`;
        },
    },
    methods: {
        createCalendar() {
            if (!this.checkValidation()) return;
            this.saving = true;
            this.updateMeetingDuration();
            const data = {
                calendar: this.calendar
            };

            if (this.is_board) {
                this.onboarding = true;
                data.features = this.featuresSettings;
            }

            this.$post('calendars', data)
                .then(response => {
                    this.redirectToSetting(response);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                    this.onboarding = false;
                });
        },
        getRedirectCalId(slot) {
            if (this.redirectRoute == 'event_details') {
                return slot.calendar_id;
            }
            if (['single_event', 'group_event'].includes(slot.event_type)) {
                return this.appVars.all_hosts.find(host => host.id == slot.user_id)?.calendar_id;
            }
            return slot.calendar_id;
        },
        redirectToSetting(res) {
            const calendarId = this.getRedirectCalId(res.slot);
            this.$router.push({
                name: this.redirectRoute,
                params: { calendar_id: calendarId, event_id: res.slot.id }
            });
            if (this.is_board) {
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            }
        },
        checkValidation() {
            if(!this.calendar.slot.location_settings[0]) {
                this.$handleError(this.$t('Please provide a location first'));
                return false;
            }
            if (!this.calendar.slot.title) {
                this.$handleError(this.$t('Event Title is required'));
                return false;
            }
            if (this.calendar.slot.settings?.multi_duration?.enabled && !this.calendar.slot.settings?.multi_duration?.available_durations?.length) {
                this.$handleError(this.$t('Multiple Duration requires at least 1 option'));
                return false;
            }
            for (const location of this.calendar.slot.location_settings) {
                if (!location.type) {
                    this.$handleError(this.$t('Location Type is required'));
                    return false;
                } else if ((location.type == 'custom') && !location.title) {
                    this.$handleError(this.$t('Location Title is required'));
                    return false;
                } else if ((location.type == 'online_meeting') && !location.meeting_link) {
                    this.$handleError(this.$t('Meeting link is required'));
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
        saveAndGotoSetting(routeName) {
            this.redirectRoute = routeName;
            this.createCalendar();
        },
        updateMeetingDuration() {
            const duration = this.calendar.slot.duration;
            this.calendar.slot.duration = duration === 'custom' ? this.calendar.slot.custom_duration : duration;
        },
        handleStep(index) {
            if (this.step == 1 || this.step > index) {
                this.step = index;
                return;
            }
            if (!this.calendar.slot.title) {
                this.$handleError(this.$t('Title Field is required'));
                return;
            }
            if (!this.checkValidation()) {
                return;
            }
            this.step = index;
        },
        getEventType(eventType) {
            const typeMap = {
                'single': this.$t('One-to-One'),
                'group': this.$t('Group'),
                'round_robin': this.$t('Round Robin'),
                'collective': this.$t('Collective'),
                'single_event': this.$t('Single Event'),
                'group_event': this.$t('Group Event')
            };
            return typeMap[eventType];
        }
    },
    mounted() {
        if (this.host_id && this.event_type) {
            this.calendar.slot.event_type = this.event_type;
            this.calendar.user_id = this.host_id;
        }

        if (this.$route.query.team_name) {
            this.calendar.type = 'team';
            this.is_host_calendar = false;
            this.calendar.title = this.$route.query.team_name;
            this.calendar.slot.settings.team_members = this.$route.query.team_members;
        }

        if (this.$route.query.event_name) {
            this.calendar.type = 'event';
            this.is_host_calendar = false;
            this.calendar.title = this.$route.query.event_name;
            this.calendar.slot.settings.team_members = this.$route.query.event_members;
        }

        if (this.is_board && this.step == 1) {
            const isAllAvailable = Object.values(this.hasFeatures).some(feature => !feature);
            if (!isAllAvailable) {
                this.step = 2;
            }
        }

        if(!this.hasSupport('is_hosted')) {
            this.require_slug = false;
            return;
        }

        if(this.appVars.intended_username) {
            this.calendar.slug = this.appVars.intended_username;
            this.require_slug = true;
            return;
        }

        this.require_slug = true;
    }
}
</script>
