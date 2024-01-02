<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header">
                <h2> <ScheduleIcon/> {{ $t('Availability') }} </h2>
            </div>
            <div class="fcal_create_calendar_form_body">
                <el-form label-position="top">
                    <el-form-item v-if="isTeam">
                        <el-switch v-model="settings.common_schedule" :active-text="$t('Choose a common schedule')"/>
                        <span>{{ $t('Availability/team_availability_description') }}</span>
                    </el-form-item>
                    <template v-if="!isTeam || settings.common_schedule">
                        <el-form-item :label="$t('Availability Range')">
                            <span class="sub-label">{{ $t('Invitees can schedule...') }}</span>
                            <el-radio-group v-model="settings.range_type" class="fcal_date_range_radio">
                                <div class="fcal_date_range_radio_item">
                                    <el-radio label="range_days" size="large">{{ $t('Within future days') }}</el-radio>
                                    <div v-if="settings.range_type == 'range_days'" class="fcal_date_range_radio_condition">
                                        <el-input v-model="settings.range_days" type="number">
                                            <template #append>{{ $t('Days into the future') }}</template>
                                        </el-input>
                                    </div>
                                </div>
                                <div class="fcal_date_range_radio_item">
                                    <el-radio label="range_date_between" size="large">{{ $t('Within a date range') }}</el-radio>
                                    <div v-if="settings.range_type == 'range_date_between'" class="fcal_date_range_radio_condition">
                                        <el-date-picker
                                            v-model="settings.range_date_between"
                                            type="daterange"
                                            :disabled-date="disabledDate"
                                            value-format="YYYY-MM-DD"
                                            :range-separator="$t('To')"
                                            :start-placeholder="$t('Start date')"
                                            :end-placeholder="$t('End date')"
                                            popper-class="fcal_daterange_popover"
                                        />
                                    </div>
                                </div>
                                <div class="fcal_date_range_radio_item">
                                    <el-radio label="range_indefinite" size="large">{{ $t('Indefinitely into the future') }} </el-radio>
                                </div>
                            </el-radio-group>
                        </el-form-item>
                        <el-divider/>
                        <el-form-item :label="$t('ScheduleSettings/availability_type_label')">
                            <el-tabs v-model="calendar_event.availability_type">
                                <el-tab-pane :label="$t('Use an Existing Schedule')" name="existing_schedule">
                                    <div v-if="!loading" class="fcal_availability_body">
                                        <h4>{{ $t('Which Schedule Do You Want to Use ?') }}</h4>
                                        <el-select
                                            v-model="calendar_event.availability_id"
                                            :placeholder="$t('Select Schedule')"
                                            popper-class="fcal_select"
                                            class="fcal_timezone"
                                            :no-match-text="$t('No Data match')"
                                            :no-data-text="$t('No Data')"
                                        >
                                            <el-option-group
                                                v-for="schedulesHosts in scheduleOptions"
                                                    :key="schedulesHosts.hostName"
                                                    :label="schedulesHosts.hostName">
                                                    <el-option
                                                        v-for="schedule in schedulesHosts.schedules"
                                                        :key="schedule.value"
                                                        :label="schedule.label"
                                                        :value="schedule.value">
                                                    </el-option>
                                            </el-option-group>
                                        </el-select>
                                        <ExistingSchedule
                                            :existing_schedules="selectedSchedule"
                                            :timezone="calendar_event.calendar.author_timezone"
                                            :availability_id="calendar_event.availability_id"
                                        />
    
                                    </div>
                                    <el-skeleton v-else :rows="5" animated />
                                </el-tab-pane>
                                <el-tab-pane :label="$t('Set Custom Hours')" name="custom">
                                    <div class="fcal_availability_body">
                                        <div class="fcal_timezone_text">
                                            <el-icon><TimezoneIcon/></el-icon>
                                            <p>{{ calendar_event.calendar.author_timezone }}</p>
                                        </div>
                                        <div class="fcal_availability_setting">
                                            <WeeklySchedules
                                                :weekly_schedules="settings.weekly_schedules"
                                                :title="$t('Weekly Hours')"
                                            />
                                            <date-over-rides
                                                :settings="settings"
                                                :title="$t('Add date overrides')"
                                            />
    
                                        </div>
                                    </div>
                                </el-tab-pane>
                            </el-tabs>
                        </el-form-item>
                    </template>
                </el-form>
            </div>
            <div class="fcal_create_calendar_form_footer">
                <SaveButton :saving="saving" :label="$t('Save Changes')" @click="saveSettings"/>
            </div>
        </div>
    </div>
</template>

<script>
import WeeklySchedules from "../parts/WeeklySchedules";
import DateOverRides from "./_DateOverRides";
import ExistingSchedule from './_ExistingSchedule';
import ScheduleIcon from "../../../Components/Icons/ScheduleIcon";
import TimezoneIcon from "../../../Components/Icons/TimezoneIcon";
import SaveButton from "@/Components/Buttons/SaveButton";
export default {
    name: '_AvailabilitySettings',
    components: {
        DateOverRides,
        WeeklySchedules,
        ExistingSchedule,
        SaveButton,
        ScheduleIcon,
        TimezoneIcon
    },
    props: {
        calendar_event: {
            type: Object,
            default: {
                title: '',
                description: '',
                duration: '',
                calendar: {
                    author_timezone: ''
                },
            }
        }
    },
    data() {
        return {
            loading: false,
            saving: false,
            scheduleOptions: [],
            availableSchedules: [],
            settings: this.calendar_event.settings
        }
    },
    computed: {
        isTeam() {
            return this.calendar_event.calendar.type === 'team';
        },
        selectedSchedule() {
            const selectedAvailability = this.availableSchedules.find(schedule => schedule.id === this.calendar_event.availability_id);
            return selectedAvailability?.settings || [];
        }
    },
    methods: {
        disabledDate(time) {
            return (time.getTime() + 86400000) <= Date.now();
        },
        getAvailabilitySettings() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/availability', {
                calendar_id: this.calendar_event.calendar_id
            })
                .then(response => {
                    this.scheduleOptions = response.schedule_options;
                    this.availableSchedules = response.available_schedules;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        saveSettings() {
            this.saving = true;
            this.$post('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/availability', {
                calendar_id: this.calendar_event.calendar_id,
                schedule_type: this.settings.schedule_type,
                weekly_schedules: this.settings.weekly_schedules,
                date_overrides: this.settings.date_overrides,
                range_type: this.settings.range_type,
                range_days: this.settings.range_days,
                range_date_between: this.settings.range_date_between,
                common_schedule: this.settings.common_schedule,
                availability_type: this.calendar_event.availability_type,
                availability_id: this.calendar_event.availability_id,
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
        this.getAvailabilitySettings();
        this.calendar_event.availability_id ??= this.settings.available_schedules[0].id;
        this.calendar_event.availability_id = parseInt(this.calendar_event.availability_id);    
    }
}
</script>
