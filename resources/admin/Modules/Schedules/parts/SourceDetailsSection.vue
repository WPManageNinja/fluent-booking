<template>
    <div class="fcal_schedule_event_infos">
        <div class="fcal_schedule_event_infos_body">
            <div class="fcal_schedule_details_header">
                <h1 class="fcal_header_title">
                    {{ booking.sourceDetails.title }}
                </h1>
            </div>
            <div>
                <div v-html="booking.sourceDetails.content"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "SourceDetailsSection",
    props: ['booking']
}
</script>
