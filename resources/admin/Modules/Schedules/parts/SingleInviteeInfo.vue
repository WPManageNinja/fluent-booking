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
                    <p>{{ spot.first_name }} {{ spot.last_name }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <h3>Invitee Email</h3>
                    <p>{{ spot.email }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <h3>Invitee Timezone</h3>
                    <p>{{ spot.person_time_zone }}</p>
                </div>
                <div class="fcal_schedule_details_event_item">
                    <h3>Booked At</h3>
                    <p>{{ toCurrentTimezone(spot.created_at, 'DD MMM YYYY, hh:mma') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "SingleInviteeInfo",
    props: ['spot']
}
</script>