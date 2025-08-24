<template>
    <el-dialog
        v-model="openModal"
        :title="$t('Add New Booking')"
        :append-to-body="true"
        :close-on-click-modal="false"
        class="fcal_modal fcal_new_booking">
        <el-form v-if="openModal" label-position="top">
            <el-form-item :label="$t('Select Event') + ' *'">
                <el-select
                    v-model="newBooking.event_id"
                    filterable
                    popper-class="fcal_select fcal_select_event"
                    :no-match-text="$t('No Data match')"
                    :no-data-text="$t('No Data')"
                    :placeholder="$t('Select Event')">
                    <el-option-group
                        v-for="calendar in calendarEventLists"
                            :key="calendar.title"
                            :label="calendar.title">
                            <el-option
                                v-for="event in calendar.options"
                                :key="event.id"
                                :label="event.title"
                                :value="event.id">
                            </el-option>
                    </el-option-group>
                </el-select>
            </el-form-item>
            <el-form-item v-if="isRoundRobinEvent" :label="$t('Select Host')">
                <el-select
                    v-model="newBooking.host_user_id"
                    :clearable="true"
                    popper-class="fcal_select">
                    <el-option v-for="member in teamMembers"
                        :key="member.ID"
                        :label="member.name"
                        :value="member.ID">
                    </el-option>
                </el-select>
                <span>{{ $t('NewBooking/host_selection_text') }}</span>
            </el-form-item>
            <el-form-item :label="getTimezoneLabel">
                <TimeZoneSelector v-model="newBooking.timezone"/>
            </el-form-item>
            <el-form-item :label="$t('Meeting Duration *')">
                <el-select
                    v-model="newBooking.duration"
                    popper-class="fcal_select"
                    :disabled="!event?.id">
                    <el-option v-for="duration in availableDurations"
                        :key="duration"
                        :label="getDuration(duration)"
                        :value="duration">
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-checkbox v-model="ignoreAvailability" :disabled="!event?.id">
                    {{ $t('Ignore Availability') }}
                </el-checkbox>
            </el-form-item>
            <div class="fcal_event_date">
                <el-form-item :label="$t('Select Date') + ' *'">
                    <el-input 
                        v-model="newBooking.event_date"
                        @click="selectEventDate=!selectEventDate"
                        :placeholder="$t('Select Date')"
                        :readonly="true"
                        :disabled="!event?.id">
                    </el-input>
                    <el-calendar
                        v-if="selectEventDate && !loading"
                        v-model="eventDate"
                        v-loading="loading"
                        ref="calendar"
                        class="fcal_booking_calendar">
                        <template #header="{ date }">
                            <span>{{ date }}</span>
                            <el-button-group>
                                <el-button size="small" :disabled="isPrevDisabled()" @click="prevMonth">
                                    <el-icon><ArrowLeft /></el-icon>
                                </el-button>
                                <el-button size="small" :disabled="isNextDisabled()" @click="nextMonth">
                                    <el-icon><ArrowRight /></el-icon>
                                </el-button>
                            </el-button-group>
                        </template>
                        <template #date-cell="{ data }">
                            <p @click="toggleSelect(data)" :class="{'is-selected': isSelectedDate(data.day), 'fcal_date_disabled': isDateInvalid(data)}">
                                {{ data.date.getDate() }}
                            </p>
                        </template>
                    </el-calendar>
                </el-form-item>
                <el-form-item :label="$t('Select Time') + ' *'">
                    <el-select
                        v-model="newBooking.event_time"
                        filterable
                        popper-class="fcal_select"
                        :placeholder="$t('Search and Select Time')"
                        :disabled="!newBooking.event_date">
                        <el-option v-for="slot in daySlots"
                            :key="slot.start"
                            :label="getStartTime(slot)"
                            :value="slot.start">
                            <span v-if="slot.remaining" style="float: left">{{ getStartTime(slot) }}</span>
                            <span v-if="slot.remaining" style="float: right">{{ getRemaining(slot) }}</span>
                        </el-option>
                    </el-select>
                </el-form-item>
            </div>
            <el-form-item :label="$t('Select Status')">
                <el-select
                    popper-class="fcal_select"
                    v-model="newBooking.status"
                    :placeholder="$t('Select Status')"
                    :disabled="!event?.id">
                    <el-option key="scheduled" :label="$t('Scheduled')" value="scheduled"/>
                    <el-option key="pending" :label="$t('Pending')" value="pending"/>
                    <el-option key="completed" :label="$t('Completed')" value="completed"/>
                </el-select>
            </el-form-item>
            <el-form-item :label="getNameLabel">
                <el-input v-model="newBooking.name" type="text" />
            </el-form-item>
            <el-form-item :label="getEmailLabel">
                <el-input v-model="newBooking.email"/>
            </el-form-item>
            <el-form-item v-if="isAboutFieldEnabled" :label="$t('What is this meeting about?') + (isAboutRequired ? ' *' : '')">
                <el-input v-model="newBooking.message" type="textarea" :rows="3"/>
            </el-form-item>
            <el-form-item v-if="isMultiGuestEnabled" :label="multiGuestField.label + (multiGuestField.required ? ' *' : '')">
                <div v-if="newBooking.guests.length" class="fcal_new_booking_guests">
                    <div v-for="(guest, index) in newBooking.guests" class="fcal_new_booking_guest">
                        <template v-if="isMultiGuestEvent">
                            <el-input v-model="newBooking.guests[index].name" type="text" :placeholder="$t('Name')"/>
                            <el-input v-model="newBooking.guests[index].email" type="email" :placeholder="$t('Email')"/>
                        </template>
                        <template v-else>
                            <el-input v-model="newBooking.guests[index]" type="email" :placeholder="$t('Email')"/>
                        </template>
                        <el-icon v-if="isRemovable" @click="removeGuest(index)"><CloseBold/></el-icon>
                    </div>
                </div>
                <div v-if="isAddable" @click="addNewGuest" style="cursor: pointer"> + {{ $t('Add guests') }}</div>
            </el-form-item>
            <div v-for="field in formFields" :key="field.name">
                <div v-if="field.enabled && !field.system_defined">
                    <el-form-item v-if="['text', 'email', 'phone', 'number', 'textarea'].includes(field.type)" :label="field.label + (field.required ? ' *' : '')">
                        <el-input v-model="customFields[field.name]" :type="field.type" :placeholder="field.placeholder"/>
                    </el-form-item>
                    <el-form-item v-if="field.type === 'checkbox'">
                        <el-checkbox v-model="customFields[field.name]" true-label="Yes" false-label="No">
                            {{ field.label }}
                        </el-checkbox>
                    </el-form-item>
                    <el-form-item v-if="field.type === 'radio'" :label="field.label + (field.required ? ' *' : '')">
                        <el-radio-group v-model="customFields[field.name]" class="fcal_new_booking_radio">
                            <el-radio v-for="option in field.options" :label="option">{{ option }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item v-if="field.type === 'checkbox-group'" :label="field.label + (field.required ? ' *' : '')">
                        <el-checkbox-group v-model="customFields[field.name]">
                            <el-checkbox v-for="option in field.options" :label="option"/>
                        </el-checkbox-group>
                    </el-form-item>
                    <el-form-item v-if="field.type === 'date'" :label="field.label + (field.required ? ' *' : '')">
                        <el-date-picker
                            v-model="customFields[field.name]"
                            type="date"
                            size="small"
                            :placeholder="field.placeholder"
                            :format="appVars.date_format"
                            :value-format="appVars.date_format"
                        />
                    </el-form-item>
                    <el-form-item v-if="field.type === 'dropdown'" :label="field.label + (field.required ? ' *' : '')">
                        <el-select
                            popper-class="fcal_select"
                            v-model="customFields[field.name]"
                            :placeholder="field.placeholder">
                            <el-option 
                                v-for="option in field.options"
                                :key="option" :label="option" :value="option">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-if="field.type === 'terms-and-conditions'">
                        <el-checkbox v-model="customFields[field.name]" true-label="Accepted" false-label="No">
                            <span v-html="field.terms_and_conditions"></span>
                        </el-checkbox>
                    </el-form-item>
                </div>
            </div>
            <el-form-item v-if="event.location_settings" :label="$t('Location') + ' *'">
                <el-radio-group v-model="locationType" class="fcal_new_booking_radio">
                    <el-radio v-for="location in event.location_settings" :label="location.type">{{ location.title }}</el-radio>
                </el-radio-group>
                <el-input
                    v-if="locationType == 'phone_guest'"
                    v-model="locationDescription"
                    :placeholder="$t('Attendee phone number (with country code)')">
                </el-input>
                <el-input
                    v-if="locationType == 'in_person_guest'"
                    v-model="locationDescription"
                    type="textarea" 
                    :rows="3"
                    :placeholder="$t('Attendee address')">
                </el-input>
            </el-form-item>
        </el-form>
        <template #footer>
            <div class="dialog-footer">
                <el-button class="fcal_plain_btn" @click="openModal = false">
                    {{ $t('Cancel') }}
                </el-button>
                <el-button :disabled="!event?.id" class="fcal_primary_btn" v-loading="saving" @click="validateAndCreate">
                    {{ $t('Create Booking') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script>
import { ArrowRight, ArrowLeft, CloseBold } from '@element-plus/icons-vue';
import TimeZoneSelector from "@/Modules/Calendars/parts/TimeZoneSelector";
export default {
    name: 'AddNewBookingModal',
    props: ['calendarEventLists', 'showModal', 'bookingData'],
    emits: ['closeModal', 'addNewBooking'],
    components: {
        ArrowLeft,
        ArrowRight,
        TimeZoneSelector,
        CloseBold
    },
    data() {
        return {
            saving: false,
            loading: false,
            availableSlots: {},
            availableDurations: {},
            event: {},
            daySlots: [],
            openModal: this.showModal,
            ignoreAvailability: false,
            locationType: '',
            locationDescription: '',
            formFields: {},
            customFields: {},
            newBooking: {
                name: '',
                email: '',
                message: '',
                timezone: '',
                duration: '',
                event_date: null,
                guests: [],
                event_time: '',
                host_user_id: null,
                source_url: window.location.href,
                custom_fields: {},
                status: 'scheduled'
            },
            eventDate: null,
            eventYear: new Date().getFullYear(),
            eventMonth: new Date().getMonth(),
            selectEventDate: false,
            remainingSpot: 1,
            teamMembers: [],
            multiGuestField: [],
            durationLookup: this.appVars.multi_duration_lookup
        }
    },
    watch: {
        'openModal': function () {
            this.$emit('closeModal');
        },
        'ignoreAvailability': function () {
            this.resetSelection();
        },
        'newBooking.event_id': function () {
            this.fetchEvent();
        },
        'newBooking.host_user_id' : function () {
            this.fetchEvent();
        },
        'newBooking.timezone': function () {
            if (this.event?.id) {
                this.fetchEvent();
            }
        },
        'newBooking.duration': function () {
            this.fetchEvent();
        },
        'newBooking.event_date': function () {
            this.updateDaySlots();
        },
        'newBooking.event_time': function () {
            this.remainingSpot = this.daySlots.find(slot => slot.start == this.newBooking.event_time)?.remaining;
        }
    },
    computed: {
        getNameLabel() {
            return this.$t("Attendee's Name") + ' *';
        },
        getEmailLabel() {
            return this.$t("Attendee's Email") + ' *';
        },
        getTimezoneLabel() {
            return this.$t("Attendee's Timezone") + ' *';
        },
        getStartTime() {
            return (slot) => {
                if (this.event?.time_format === '24') {
                    return this.dayjs(slot.start).format('HH:mm');
                }
                return this.dayjs(slot.start).format('hh:mm A');
            }
        },
        getRemaining() {
            return (slot) => {
                return slot.remaining + ' ' + this.$t('spots left');
            }
        },
        isAddable() {
            const length = this.newBooking.guests.length;
            return !length || (length < (this.getLimit() - 1) && (
                this.isMultiGuest()
                    ? (this.newBooking.guests[length - 1]?.name?.trim() && this.newBooking.guests[length - 1]?.email?.trim())
                    : this.newBooking.guests[length - 1]?.trim()
            ));
        },
        isRemovable() {
            return this.newBooking.guests.length > 1;
        },
        isMultiGuestEvent() {
            return ['group', 'group_event'].includes(this.event?.event_type);
        },
        isMultiGuestEnabled() {
            if (this.formFields.length) {
                const multiGuestField = this.formFields.find(field => field.name === 'guests' && field.enabled);
                if (multiGuestField) {
                    this.multiGuestField = multiGuestField;
                    return true;
                }
            }
            return false;
        },
        isAboutFieldEnabled() {
            return this.formFields.length && this.formFields.find(field => field.name === 'message' && field.enabled);
        },
        isAboutRequired() {
            return this.formFields.length && this.formFields.find(field => field.name === 'message' && field.required);
        },
        isRoundRobinEvent() {
            return this.teamMembers.length && this.event?.event_type == 'round_robin';
        },
        getDuration() {
            return (duration) => {
                return this.durationLookup[duration] || duration + ' ' + this.$t('Minutes');
            }
        }
    },
    methods: {
        fetchEvent() {
            this.loading = true;
            this.resetSelection();
            this.$get('bookings/event/' + this.newBooking.event_id, {
                event_id: this.newBooking.event_id,
                timezone: this.newBooking.timezone,
                duration: this.newBooking.duration,
                host_id: this.newBooking.host_user_id,
                guests: this.newBooking.guests,
                start_date: this.dayjs(this.eventYear +'-'+ (this.eventMonth+1) + '-' + '01').format('YYYY-MM-DD HH:mm')
            })
                .then(response => {
                    this.formFields = response.calendar_event.form_fields;
                    this.availableSlots = response.available_slots;
                    this.locationType = response.calendar_event?.slot?.location_settings[0]?.type;
                    this.teamMembers = response.calendar_event?.team_member_profiles ?? [];
                    this.maybeUpdateEvent(response);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        createBooking() {
            this.saving = true;
            this.$post('bookings/create/' + this.event.id, this.newBooking)
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.$emit('addNewBooking');
                    this.openModal = false;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        maybeUpdateEvent(res) {
            const calendarEvent = res.calendar_event?.slot;
            if (this.event?.id != calendarEvent?.id) {
                this.event = calendarEvent;
                this.newBooking.guests = [];
                this.updateDurations();
            }
        },
        resetSelection() {
            this.newBooking.event_date = null;
            this.newBooking.event_time = '';
        },
        validateAndCreate() {
            if (!this.newBooking.name) {
                this.$handleError(this.$t("Attendee's Name") + ' ' + this.$t('is required'));
            } else if (!this.newBooking.email) {
                this.$handleError(this.$t("Attendee's Email") + ' ' + this.$t('is required'));
            } else if (!this.newBooking.event_date || !this.newBooking.event_time) {
                this.$handleError(this.$t("Event Date") + ' ' + this.$t('is required'));
            } else {
                this.newBooking.custom_fields = this.customFields;
                this.newBooking.location_type = this.locationType;
                this.newBooking.ignore_availability = this.ignoreAvailability;
                this.newBooking.location_description = this.locationDescription;
                this.createBooking();
            }
        },
        defaultDaySlots(day) {
            const defaultSlots = [];
            let startTime = this.dayjs(day).set('hour', 0).set('minute', 0);
            const endTime = this.dayjs(day).set('hour', 23).set('minute', 50);
            while (startTime.isSameOrBefore(endTime)) {
                const slot = {
                    start: startTime.format('YYYY-MM-DD HH:mm:ss'),
                    remaining: false,
                };
                defaultSlots.push(slot);
                startTime = startTime.add(5, 'minutes');
            }
            return defaultSlots;
        },
        updateDaySlots() {
            if (!this.ignoreAvailability) {
                this.daySlots = this.availableSlots[this.newBooking.event_date];
            } else {
                this.daySlots = this.defaultDaySlots(this.newBooking.event_date);
            }
        },
        updateDurations() {
            const isMultiDuration = this.event.settings?.multi_duration?.enabled;
            this.availableDurations = isMultiDuration ? this.event.settings?.multi_duration?.available_durations : [this.event.duration];
            this.newBooking.duration = isMultiDuration ? this.event.settings?.multi_duration?.default_duration : this.event.duration;
        },
        toggleSelect(data) {
            if (this.isDateInvalid(data)) {
                return;
            }
            this.selectEventDate = false;
            this.newBooking.event_date = data.day;
            this.eventDate = data.date;
        },
        isDateInvalid(data) {
            if (!this.ignoreAvailability) {
                if (this.event?.max_lookup_date) {
                    const minLookupDate = new Date(this.event?.min_lookup_date);
                    const maxLookupDate = new Date(this.event?.max_lookup_date);
                    if (data.date < minLookupDate || data.date > maxLookupDate) {
                        return true;
                    }
                }
                if (!this.availableSlots[data.day]) {
                    return true;
                }
            }
            return data.date < Date.now() - 86400000;
        },
        isSelectedDate(day) {
            return day == this.eventDate;
        },
        nextMonth() {
            this.eventMonth = (this.eventMonth + 1) % 12;
            this.eventYear += this.eventMonth === 0 ? 1 : 0;

            this.$refs.calendar.selectDate('next-month');

            if (!this.ignoreAvailability) {
                this.fetchEvent();
            }
        },
        prevMonth() {
            const prevMonth = (this.eventMonth === 0) ? 11 : (this.eventMonth - 1);
            const prevYear = (this.eventMonth === 0) ? (this.eventYear - 1) : this.eventYear;

            this.eventMonth = prevMonth;
            this.eventYear = prevYear;

            this.$refs.calendar.selectDate('prev-month');

            if (!this.ignoreAvailability) {
                this.fetchEvent();
            }
        },
        isPrevDisabled() {
            if (!this.ignoreAvailability && this.event?.min_lookup_date) {
                const firstDayOfPrevMonth = new Date(this.eventYear, this.eventMonth, 1);
                const minLookupDate = new Date(this.event?.min_lookup_date);
                return firstDayOfPrevMonth.getTime() < minLookupDate.getTime();
            }
            return false;
        },
        isNextDisabled() {
            if (!this.ignoreAvailability && this.event?.max_lookup_date) {
                const lastDayOfNextMonth = new Date(this.eventYear, this.eventMonth + 1, 0);
                const maxLookupDate = new Date(this.event?.max_lookup_date);
                return lastDayOfNextMonth.getTime() > maxLookupDate.getTime();
            }
            return false;
        },
        isMultiGuest() {
            return ['group', 'group_event'].includes(this.event?.event_type);
        },
        getLimit() {
            if (this.isMultiGuest()) {
                return Math.min(this.multiGuestField.limit, this.remainingSpot);
            }
            return this.multiGuestField.limit;
        },
        addNewGuest() {
            if (this.isMultiGuest()) {
                this.newBooking.guests.push({ name: '', email: '' });
            } else {
                this.newBooking.guests.push('');
            }
        },
        removeGuest(index) {
            this.newBooking.guests.splice(index, 1);
        },
        updateCustomFieldData(booking) {
            const customFields = booking.custom_form_data || {};
            const updatedCustomFields = {};
            Object.entries(customFields).forEach(([key, field]) => {
                updatedCustomFields[key] = field.value;
            });
            return updatedCustomFields;
        },
        updateBookingData() {
            const newBookingData = this.bookingData;
            const eventId = parseInt(newBookingData.event_id);
            const defaultGuestValue = this.isMultiGuest(this.event) ? ['']: [{ name: '', email: '' }];
            this.newBooking = {
                ...this.newBooking,
                event_id: eventId,
                name: newBookingData.first_name + ' ' + newBookingData.last_name,
                email: newBookingData.email,
                message: newBookingData.message,
                timezone: newBookingData.person_time_zone,
                guests: newBookingData.additional_guests.length ? newBookingData.additional_guests : defaultGuestValue,
            };
            this.customFields = this.updateCustomFieldData(newBookingData);
            this.locationType = newBookingData.location_details?.type;
            this.locationDescription = newBookingData.location_details?.description;
        }
    },
    mounted() {
        if (this.bookingData) {
            this.updateBookingData();
        }
    }
}
</script>
