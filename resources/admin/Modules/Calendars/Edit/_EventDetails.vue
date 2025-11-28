<template>
    <div :class="(!is_board && !new_event) ? 'fcal_create_calendar_body' : ''">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header">
                <h2> <EventIcon/> {{ $t('Event Details') }} </h2>
                <el-switch class="fcal_switch" v-model="isEnable" @change="toggleSlotStatus"/>
            </div>
            <div class="fcal_create_calendar_form_body">
                <el-form label-position="top">
                    <el-form-item>
                        <div class="fcal_event_details_wrap">
                            <el-form-item
                                v-if="is_board"
                                :label="$t('Event Type')">
                                <el-select
                                    v-model="calendar_event.event_type"
                                    popper-class="fcal_select">
                                    <el-option value="single" :label="$t('One to One')" />
                                    <el-option value="group" :disabled="!appVars.has_pro" :label="getGroupLabel()"/>
                                </el-select>
                            </el-form-item>

                            <el-form-item :label="$t('Event Name *')" class="fcal_color_select_wrap">
                                <el-input
                                    v-model="calendar_event.title"
                                    :placeholder="$t('Enter Event Title')">
                                    <template #prepend>
                                        <div class="fcal_color_select">
                                            <span class="fcal_color" :style="'background:'+ calendar_event.color_schema "></span>
                                            <el-select
                                                v-model="calendar_event.color_schema"
                                                :placeholder="$t('Select')"
                                                style="width: 77px"
                                                popper-class="fcal_color_select_popover">
                                                <el-option
                                                    v-for="(color, i) in colors" :key="i"
                                                    :value="color.value">
                                                    <span :style="'background:'+color.value"></span>
                                                </el-option>
                                            </el-select>
                                        </div>
                                    </template>
                                </el-input>
                            </el-form-item>
                            
                            <el-form-item :label="$t('Description')">
                                <wp-editor
                                    v-model="calendar_event.description"
                                    :height="120"
                                    :media_buttons="false"
                                    :quick_tags="false"
                                    :toolbar="editorToolbar"
                                />
                            </el-form-item>
                        </div>
                    </el-form-item>

                    <el-form-item>
                        <div class="fcal_event_card">
                            <div class="fcal_meeting_duration">
                                <el-form-item v-if="!calendar_event.settings?.multi_duration?.enabled" :label="$t('Meeting Duration *')">
                                    <el-select
                                        v-model="calendar_event.duration"
                                        :placeholder="$t('Select')"
                                        popper-class="fcal_select">
                                        <el-option
                                            v-for="item in meetingDurations"
                                            :key="item.value"
                                            :label="item.label"
                                            :value="item.value"
                                        />
                                    </el-select>
                                    <div v-if="calendar_event.duration === 'custom'" class="custom-duration">
                                        <el-input
                                            v-model="calendar_event.custom_duration"
                                            @change="validateDuration(calendar_event)"
                                            type="number"
                                            :min="5">
                                            <template #append>{{ $t('Minutes') }}</template>
                                        </el-input>
                                    </div>
                                </el-form-item>
                                <template v-else-if="showMultiDuration">
                                    <el-form-item :label="$t('Available Durations') + ' *'">
                                        <el-select
                                            v-model="calendar_event.settings.multi_duration.available_durations"
                                            @change="updateDefaultDurations"
                                            multiple
                                            :placeholder="$t('Select')"
                                            popper-class="fcal_select">
                                            <el-option
                                                v-for="item in multiDurations"
                                                :key="item.value"
                                                :label="item.label"
                                                :value="item.value"
                                            />
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item :label="$t('Default Duration')">
                                        <el-select
                                            v-model="calendar_event.settings.multi_duration.default_duration"
                                            :placeholder="$t('Select')"
                                            popper-class="fcal_select">
                                            <el-option
                                                v-for="item in defaultDurations"
                                                :key="item.value"
                                                :label="item.label"
                                                :value="item.value"
                                            />
                                        </el-select>
                                    </el-form-item>
                                </template>
                                <el-form-item v-if="showMultiDuration">
                                    <el-switch v-model="calendar_event.settings.multi_duration.enabled" :active-text="$t('Allow attendee to select duration')"/>
                                </el-form-item>
                            </div>
                        </div>
                    </el-form-item>

                    <el-form-item>
                        <div class="fcal_event_card">
                            <el-form-item :label="$t('Location *')">
                                <location-selector :slot="calendar_event" @saveAndGotoSetting="saveAndGotoSetting"/>
                            </el-form-item>
                        </div>
                    </el-form-item>

                    <template v-if="showMaxInvitees">
                        <el-form-item>
                            <div class="fcal_event_card">
                                <div>
                                    <el-form-item :label="$t('Max invitees in a spot')">
                                        <el-input type="number" :min="1" v-model="calendar_event.max_book_per_slot"></el-input>
                                    </el-form-item>
                                    <el-checkbox
                                        v-model="isDisplaySpots"
                                        @change="toggleDisplaySpots"
                                        class="fcal_checkbox"
                                        type="checkbox"
                                        :label="$t('Display remaining spots on booking page')">
                                    </el-checkbox>
                                </div>
                            </div>
                        </el-form-item>
                    </template>
                </el-form>
            </div>
            <div v-if="!is_board && !new_event" class="fcal_create_calendar_form_footer">
                <SaveButton :saving="saving" :label="$t('Save Changes')" @click="saveSettings"/>
            </div>
        </div>
    </div>
</template>

<script>
import LocationSelector from "./_LocationSelector";
import EventIcon from "../../../Components/Icons/EventIcon";
import HostSelector from "@/Pieces/HostSelector";
import SaveButton from "@/Components/Buttons/SaveButton";
import WpEditor from '../../../Components/FormBuilder/WpEditorField.vue';

export default {
    name: 'EventDetails',
    props: ['calendar_event', 'event_type', 'is_board', 'new_event'],
    emits: ['saveAndGotoSetting'],
    components: {
        HostSelector,
        LocationSelector,
        EventIcon,
        SaveButton,
        WpEditor
    },
    data() {
        return {
            saving: false,
            loading: false,
            isEnable: this.calendar_event.status === 'active' ? true : false,
            isDisplaySpots: this.calendar_event.is_display_spots == 1 ? true : false,
            isGroupMeeting: false,
            isGroupEvent: false,
            isEventCalendar: false,
            colors: this.appVars.event_colors,
            meetingDurations: this.appVars.meeting_durations,
            multiDurations: this.appVars.multi_durations,
            durationLookup: this.appVars.multi_duration_lookup,
            editorToolbar: 'bold,italic,underline,bullist,numlist,link,undo,redo',
            defaultDurations: [],
            redirectRoute: ''
        }
    },
    computed: {
        showMultiDuration() {
            return !this.is_board && !this.new_event && !this.isGroupMeeting && !this.isEventCalendar;
        },
        showMaxInvitees () {
            return this.appVars.has_pro && (this.isGroupMeeting || this.isGroupEvent);
        }
    },
    methods: {
        getGroupLabel() {
            return this.appVars.has_pro ? this.$t('Group') : this.$t('Group (Pro Required)');
        },
        toggleDisplaySpots() {
            this.calendar_event.is_display_spots = this.isDisplaySpots ? 1 : 0;
        },
        toggleSlotStatus() {
            this.calendar_event.status = this.isEnable ? 'active' : 'draft';
        },
        validateDuration(calendar_event) {
            const { event_type, custom_duration } = calendar_event;
            const maxValue = (['single_event', 'group_event'].includes(event_type)) ? 1440 : 720;
            this.calendar_event.custom_duration = Math.max(5, Math.min(maxValue, custom_duration));
        },
        getDuration(duration) {
            return this.durationLookup[duration];
        },
        updateDefaultDurations(updatedValue) {
            if (!updatedValue || !updatedValue.length) {
                return;
            }
            this.calendar_event.settings.multi_duration.available_durations = updatedValue.sort((a, b) => a - b);
            
            const durations = updatedValue.map(duration => ({
                value: duration,
                label: this.getDuration(duration)
            }));
            this.defaultDurations = durations;

            const defaultDuration = this.calendar_event.settings.multi_duration.default_duration;
            if (!updatedValue.includes(defaultDuration)) {
                this.calendar_event.settings.multi_duration.default_duration = updatedValue[0];
            }
        },
        checkDurationType() {
            const fromDurationValue = this.appVars.meeting_durations.some(duration => duration.value === this.calendar_event.duration);
            if (!fromDurationValue) {
                this.calendar_event.custom_duration = this.calendar_event.duration;
                this.calendar_event.duration = 'custom';
            }
        },
        getMeetingDuration() {
            return this.calendar_event.duration === 'custom' ? this.calendar_event.custom_duration : this.calendar_event.duration;
        },
        getRedirectCalId(calendarId) {
            if (['single_event', 'group_event'].includes(this.calendar_event.event_type)) {
                const userId = this.calendar_event.user_id;
                return this.appVars.all_hosts.find(host => host.id == userId)?.calendar_id;
            }
            return calendarId;
        },
        handleRedirection(res) {
            if (this.redirectRoute) {
                this.$router.push({
                    name: this.redirectRoute,
                    params: { calendar_id: this.getRedirectCalId(res.calendar_id) }
                });
            }
        },
        checkValidation() {
            if (!this.calendar_event.title) {
                this.$handleError(this.$t('Event Title is required'));
                return false;
            }
            if (this.calendar_event.settings.multi_duration?.enabled) {
                if (!this.calendar_event.settings.multi_duration?.available_durations?.length) {
                    this.$handleError(this.$t('Multiple Duration requires at least 1 option'));
                    return false;
                } else if (!this.calendar_event.settings.multi_duration?.default_duration) {
                    this.$handleError(this.$t('Default Duration is required'));
                    return false;
                }
            }
            for (const location of this.calendar_event.location_settings) {
                if (!location.type) {
                    this.$handleError(this.$t('Location Type is required'));
                    return false;
                }
                if ((location.type == 'custom') && !location.title) {
                    this.$handleError(this.$t('Location Title is required'));
                    return false;
                }
                if ((location.type == 'online_meeting') && !location.meeting_link) {
                    this.$handleError(this.$t('Meeting link is required'));
                    return false;
                }
                if ((location.type == 'in_person_organizer' || location.type == 'custom') && !location.description) {
                    this.$handleError(this.$t('Location Description is required'));
                    return false;
                }
                if (location.type == 'phone_organizer' && !location.host_phone_number) {
                    this.$handleError(this.$t('Phone Number is required'));
                    return false;
                }
            }
            return true;
        },
        saveSettings() {
            if (!this.checkValidation()) return;
            this.saving = true;
            this.$post('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/details', {
                calendar_id: this.calendar_event.calendar_id,
                title: this.calendar_event.title,
                status: this.calendar_event.status,
                color_schema: this.calendar_event.color_schema,
                description: this.calendar_event.description,
                duration: this.getMeetingDuration(),
                max_book_per_slot: this.calendar_event.max_book_per_slot,
                is_display_spots: this.calendar_event.is_display_spots,
                location_settings: this.calendar_event.location_settings,
                multi_duration: this.calendar_event.settings.multi_duration,
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
            if (this.is_board || this.new_event) {
                this.$emit('saveAndGotoSetting', routeName)
            } else {
                this.redirectRoute = routeName;
                this.saveSettings();
            }
        }
    },
    mounted() {
        this.checkDurationType();
        this.updateDefaultDurations(this.calendar_event.settings?.multi_duration?.available_durations);
        this.calendar_event.event_type = this.event_type ? this.event_type : this.calendar_event.event_type;
        this.isGroupMeeting = this.calendar_event.event_type == 'group';
        this.isGroupEvent = this.calendar_event.event_type == 'group_event';
        this.isEventCalendar = this.isGroupEvent || this.calendar_event.event_type == 'single_event';
    }
}
</script>
