<template>
    <div class="fcal_schedule_event_infos">
        <div class="fcal_schedule_event_infos_body">
            <div class="fcal_schedule_details_header">
                <h1 class="fcal_header_title">
                    {{ $t('Invitees Information') }}
                </h1>
            </div>
            <div class="fcal_schedule_details_event">
                <div class="fcal_schedule_details_event_item">
                    <h3>{{ $t('Invitee Name') }}</h3>
                    <p>{{ booking.first_name }} {{ booking.last_name }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <editable-booking-data
                        input_type="textarea"
                        data_key="email"
                        :input_label="$t('Invitee Email')"
                        :booking="booking">
                    </editable-booking-data>
                </div>
                <div v-if="booking.additional_guests.length" class="fcal_schedule_details_event_item">
                    <h3>{{ $t('Additional Guests') }}</h3>
                    <div v-for="guest in booking.additional_guests" class="fcal_spot_details_value">
                        <p>{{ guest }}</p>
                    </div>
                </div>
                <div v-if="booking.message" class="fcal_schedule_details_event_item">
                    <h3>{{ $t('Message') }}</h3>
                    <p>{{ booking.message }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <h3>{{ $t('Invitee Timezone') }}</h3>
                    <p>{{ booking.person_time_zone }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <h3>{{ $t('Booked At') }}</h3>
                    <p>{{ bookedAtHandler(booking.created_at) }}</p>
                </div>
                <div v-if="booking.custom_form_data" v-for="field in booking.custom_form_data" class="fcal_schedule_details_event_item">
                    <template v-if="field.value && field.value != 'undefined' && field.label != 'Location'">
                        <h3>{{ field.label }}</h3>
                        <div class="fcal_spot_details_value">
                            <p v-html="field.value"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import EditableBookingData from "./EditableBookingData";

export default {
    name: "SingleInviteeInfo",
    props: ['booking'],
    components: {
        EditableBookingData
    },

    methods: {
        bookedAtHandler(date) {
            return this.toCurrentTimezone(date, this.appVars.date_time_formatter);
        }
    }
}
</script>
