<template>
    <div class="fcal_schedule_event_infos">
        <div v-if="loading">
            <el-skeleton :rows="5" :animated="true" />
        </div>
        <div v-else-if="main_body_contents && main_body_contents.length">
            <div v-for="bodyMeta in main_body_contents" :key="bodyMeta.id" class="fcal_schedule_event_infos_body">
                <div class="fcal_schedule_details_header">
                    <h1 class="fcal_header_title">
                        {{ bodyMeta.title }}
                    </h1>
                </div>
                <div>
                    <div v-html="bodyMeta.content"></div>
                </div>
            </div>
        </div>
    </div>
    <PaymentLogs v-if="payment_order" :payment_order="payment_order" :booking="booking" />
</template>

<script>
import PaymentLogs from "@/Modules/Schedules/parts/PaymentLogs.vue";

export default {
    name: "SourceDetailsSection",
    components: {
        PaymentLogs
    },
    props: ['booking'],
    data() {
        return {
            loading: false,
            payment_order: null,
            main_body_contents: []
        }
    },
    methods: {
        getAdditionalData() {
            this.loading = true;
            this.$get(`schedules/${this.booking.id}/meta-info`)
                .then(response => {
                    this.payment_order = response.payment_order;
                    this.main_body_contents = response.main_body_contents;
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
    mounted() {
        this.getAdditionalData();
    }
}
</script>
