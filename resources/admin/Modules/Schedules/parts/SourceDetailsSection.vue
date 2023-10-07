<template>
    <div class="fcal_schedule_event_infos">
        <div class="fcal_schedule_event_infos_body">
            <div class="fcal_schedule_details_header">
                <h1 class="fcal_header_title">
                    {{ spots[0].sourceDetails.title }}
                </h1>
            </div>
            <div v-for="(spot, index) in spots" :key="index">
                <h3>Booking {{ index+1 }}</h3>
                <div v-html="spot.sourceDetails.content"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "SourceDetailsSection",
    props: ['spots']
}
</script>