<template>
    <div class="fcal_create_calendar_wrap fcal_onboard_wrap">
        <div class="fcal_create_calendar_header">
            <router-link :to="{name: 'calendars'}" class="fcal_back_btn">
                <el-icon :size="20" color="black"><Back/></el-icon>
                <h1>{{ slotTitle }}</h1>
            </router-link>
        </div>

        <div v-if="slot" class="fcal_create_calendar_body">
            <div class="fcal_create_calendar_basic_info">
                <event-details
                    ref="basicInfo"
                    :calendar_event="slot"
                    :event_type="event_type"
                    :new_event="true"
                    @saveAndGotoSetting="saveAndGotoSetting"
                />
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

<script>
import EventDetails from './_EventDetails.vue';
import { Back } from '@element-plus/icons-vue';


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
            redirectRoute: 'event_details',
            teamMembers: []
        }
    },
    computed:  {
        slotTitle() {
            const eventType = this.getEventType(this.event_type);
            return `${this.$t('Add')} ${eventType} ${this.$t('Booking Type')}`;
        }
    },
    methods: {
        getMeetingDuration() {
            return this.slot.duration === 'custom' ? this.slot.custom_duration : this.slot.duration;
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
        },
        getEventSchema() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_id + '/event-schema', {
                calendar_id : this.calendar_id,
            })
                .then(response => {
                    this.slot = response.slot;
                    this.maybeAddTeamMembers();
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        maybeAddTeamMembers() {
            this.slot.settings.team_members = this.teamMembers.length ? this.teamMembers : [];
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
        handleRedirection(res) {
            const calendarId = this.getRedirectCalId(res.slot);
            this.$router.push({
                name: this.redirectRoute,
                params: { calendar_id: calendarId, event_id: res.slot.id },
            });
        },
        checkValidation() {
            for (const location of this.slot.location_settings) {
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
        saveSettings() {
            if (!this.checkValidation()) return;
            this.saving = true;
            this.$post('calendars/' + this.calendar_id + '/events', {
                calendar_id : this.calendar_id,
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
                    this.handleRedirection(response);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        saveAndGotoSetting(routeName) {
            this.redirectRoute = routeName;
            this.saveSettings();
        }
    },
    mounted() {
        this.teamMembers = this.$route.query.team_members ?? [];
        this.$changeTitle(this.$t('Create New Event Type'));
        this.getEventSchema();
    }
}
</script>
