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
                    <h3>{{ $t('Invitee Email') }}</h3>
                    <p>{{ booking.email }}</p>
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
                    <p>{{$t(toCurrentTimezone(booking.created_at, 'DD'))}} {{$t(toCurrentTimezone(booking.created_at, 'MMM').toLowerCase())}} {{ toCurrentTimezone(booking.created_at, 'YYYY, hh:mma') }}</p>
                </div>
                <div v-if="booking.custom_form_data" v-for="field in booking.custom_form_data" class="fcal_schedule_details_event_item">
                    <template v-if="field.value && field.value != 'undefined' && field.label != 'Location'">
                        <h3 >{{ field.label }}</h3>
                        <div class="fcal_spot_details_value">
                            <p>{{ field.value }}</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "SingleInviteeInfo",
    props: ['booking']
}
</script>
