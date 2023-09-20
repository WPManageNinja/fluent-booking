<template>
    <div class="fluentcrm_databox fluent_booking_in_fluent_crm">
        <h1>Booking is a joss Booking Plugin of FluentWP</h1>
    </div>
</template>

<script>
export default {
    name: 'ContactSection',
    props: ['subscriber'],
    data() {
        return {
            loading: true
        }
    },
    methods: {
        consoleIt() {
            console.log('ContactSection');
        }
    },
    mounted() {
        this.consoleIt();
    }
}
</script>

<style scoped>
</style>