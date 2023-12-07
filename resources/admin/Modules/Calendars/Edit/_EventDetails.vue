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
                                    popper-class="fcal_select"
                                >
                                    <el-option value="single" :label="$t('One to One')" />
                                    <el-option value="group" :label="$t('Group')"/>
                                </el-select>
                            </el-form-item>

                            <el-form-item :label="$t('Event Name *')" class="fcal_color_select_wrap">
                                <el-input
                                    v-model="calendar_event.title"
                                    :placeholder="$t('Enter Event Title')"
                                >
                                    <template #prepend>
                                        <div class="fcal_color_select">
                                            <span class="fcal_color" :style="'background:'+ calendar_event.color_schema "></span>
                                            <el-select
                                                v-model="calendar_event.color_schema"
                                                :placeholder="$t('Select')"
                                                style="width: 77px"
                                                popper-class="fcal_color_select_popover"
                                            >
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
                                <el-input
                                v-model="calendar_event.description"
                                type="textarea"
                                :rows="2"
                                :placeholder="$t('Enter Description here')"
                                />
                            </el-form-item>
                        </div>
                    </el-form-item>

                    <el-form-item>
                        <div class="fcal_event_card">
                            <el-form-item :label="$t('Meeting Duration *')">
                                <el-select v-model="calendar_event.duration" :placeholder="$t('Select')" popper-class="fcal_select">
                                    <el-option
                                        v-for="item in meetingDuration"
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
                        </div>
                    </el-form-item>

                    <el-form-item>
                        <div class="fcal_event_card">
                            <el-form-item :label="$t('Location *')">
                                <location-selector :slot="calendar_event"/>
                            </el-form-item>
                        </div>
                    </el-form-item>

                    <el-form-item>
                        <div class="fcal_event_card">
                            <div class="card_contents">
                                <span class="sub-label card-title">{{ $t("Redirect on Booking") }}</span>
                                <span>{{ $t("EventDetails/redirect_url_description") }}</span>
                            </div>
                            <div class="card_action">
                                <el-switch v-model="calendar_event.settings.custom_redirect.enabled"/>
                            </div>
                        </div>
                            <div class="fcal_event_child_card" v-if="calendar_event.settings.custom_redirect.enabled">
                                <el-input v-model="calendar_event.settings.custom_redirect.redirect_url" :placeholder="$t('EventDetails/redirect_url_placeholder')"></el-input>
                            </div>
                    </el-form-item>

                    <template v-if="isGroupMeeting">
                        <el-form-item>
                            <div class="fcal_event_card">
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

export default {
    name: 'EventDetails',
    props: ['calendar_event', 'event_type', 'is_board', 'new_event'],
    components: {
        HostSelector,
        LocationSelector,
        EventIcon,
        SaveButton
    },
    data() {
        return {
            saving: false,
            isEnable: this.calendar_event.status === 'active' ? true : false,
            isDisplaySpots: this.calendar_event.is_display_spots == 1 ? true : false,
            isGroupMeeting: this.calendar_event.event_type == 'group',
            colors: this.appVars.event_colors,
            meetingDuration: this.appVars.meeting_durations
        }
    },
    methods: {
        toggleDisplaySpots() {
            this.calendar_event.is_display_spots = this.isDisplaySpots ? 1 : 0;
        },
        toggleSlotStatus() {
            this.calendar_event.status = this.isEnable ? 'active' : 'draft';
        },
        validateDuration(calendar_event) {
            this.calendar_event.custom_duration = Math.max(5, Math.min(720, calendar_event.custom_duration));
        },
        checkDurationType() {
            const fromDurationValue = this.appVars.meeting_durations.some(duration => duration.value === this.calendar_event.duration);
            if (!fromDurationValue) {
                this.calendar_event.custom_duration = this.calendar_event.duration;
                this.calendar_event.duration = 'custom';
            }
        },
        checkValidation() {
            for (const location of this.calendar_event.location_settings) {
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
        getMeetingDuration() {
            return this.calendar_event.duration === 'custom' ? this.calendar_event.custom_duration : this.calendar_event.duration;
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
                custom_redirect: this.calendar_event.settings.custom_redirect
            })
                .then(response => {
                    this.$handleSuccess(response);
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
        this.checkDurationType();
        this.calendar_event.event_type = this.event_type ? this.event_type : this.calendar_event.event_type;
        this.isGroupMeeting = this.calendar_event.event_type == 'group';
    }
}
</script>
