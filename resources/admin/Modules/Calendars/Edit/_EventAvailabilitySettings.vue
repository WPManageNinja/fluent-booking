<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header">
                <h2> <ScheduleIcon/> {{ $t('Availability') }} </h2>
            </div>
            <template v-if="!disabled">
                <div class="fcal_create_calendar_form_body">
                    <el-form label-position="top">
                        <el-form-item class="fcal_availability_switch">
                            <el-switch v-model="settings.reserve_time" :active-text="$t('Reserve Times')"/>
                            <p>{{ $t('Availability/event_availability_reserve_time') }}</p>
                        </el-form-item>
                        <el-form-item :label="$t('Availability Timezone')">
                                <div class="fcal_availability_body">
                                    <div class="fcal_timezone_text">
                                        <el-icon><TimezoneIcon/></el-icon>
                                        <p>{{ calendar_event.calendar.author_timezone }}</p>
                                    </div>
                                    <div>
                                        <AvailableTimes
                                            :title="$t('Available Times')"
                                            :calendar_event="calendar_event"
                                            :settings="settings"
                                            @saveSettings="saveSettings"
                                        />
                                    </div>
                                </div>
                        </el-form-item>
                    </el-form>
                </div>
                <div class="fcal_create_calendar_form_footer">
                    <SaveButton :saving="saving" :label="$t('Save Changes')" @click="saveSettings"/>
                </div>
            </template>
            <ProNotice v-else/>
        </div>
    </div>
</template>

<script>
import AvailableTimes from "./_AvailableTimes";
import ScheduleIcon from "../../../Components/Icons/ScheduleIcon";
import TimezoneIcon from "../../../Components/Icons/TimezoneIcon";
import SaveButton from "@/Components/Buttons/SaveButton";
import ProNotice from "@/Components/Common/ProNotice";
export default {
    name: '_EventAvailabilitySettings',
        components: {
        AvailableTimes,
        SaveButton,
        ScheduleIcon,
        TimezoneIcon,
        ProNotice
    },
    props: ['calendar_event', 'disabled'],
    data() {
        return {
            saving: false,
            scheduleOptions: [],
            availableSchedules: [],
            settings: this.calendar_event.settings
        }
    },
    methods: {
        disabledDate(time) {
            return (time.getTime() + 86400000) <= Date.now();
        },
        checkValidation () {
            if (!Object.keys(this.settings.available_times).length) {
                this.$handleError(this.$t('Please select at least one available time'));
                return false;
            }
            return true;
        },
        saveSettings() {
            if (!this.checkValidation()) return;
            this.saving = true;
            this.$post('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/event-availability', {
                calendar_id: this.calendar_event.calendar_id,
                available_times: this.settings.available_times,
                reserve_time: this.settings.reserve_time
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
    }
}
</script>
