<template>
    <div class="fcal_schedule_event_infos">
        <div class="fcal_schedule_event_infos_body">
            <div class="fcal_schedule_details_header">
                <h1 class="fcal_header_title">
                    Invitees Information
                </h1>
            </div>
            <div class="fcal_schedule_details_event">
                <div class="fcal_schedule_details_event_item">
                    <h3>Invitee Name</h3>
                    <p>{{ booking.first_name }} {{ booking.last_name }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <h3>Invitee Email</h3>
                    <p>{{ booking.email }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <h3>Invitee Timezone</h3>
                    <p>{{ booking.person_time_zone }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <h3>Booked At</h3>
                    <p>{{ toCurrentTimezone(booking.created_at, 'DD MMM YYYY, hh:mma') }}</p>
                </div>
                <div v-if="booking.custom_field" v-for="field in booking.custom_field.value" class="fcal_schedule_details_event_item">
                    <h3>{{ field.label }}</h3>
                    <div class="fcal_spot_details_value">
                        <p>{{ field.value }}</p>
                    </div>
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
