<template>
    <div class="fcal_schedule_event_infos">
        <div v-if="loading">
            <el-skeleton :rows="5" :animated="true" />
        </div>
        <div v-else class="fcal_schedule_event_infos_body">
            <div class="fcal_schedule_details_header">
                <h1 class="fcal_header_title">
                    {{ showing_booking.sourceDetails?.title }}
                </h1>
            </div>
            <div>
                <div v-html="showing_booking.sourceDetails?.content"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "SourceDetailsSection",
    props: ['booking'],
    data() {
        return {
            loading: false,
            showing_booking: this.booking
        }
    },
    methods: {
        fetchDetails() {
            this.loading = true;
            this.$get(`schedules/${this.booking.id}`)
                .then(response => {
                    this.showing_booking = response.schedule;
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    mounted() {
        if (!this.booking.sourceDetails?.title) {
            this.fetchDetails();
        }
    }
}
</script>
