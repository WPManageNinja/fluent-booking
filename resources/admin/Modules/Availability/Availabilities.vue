<template>
    <div class="fcal_availabilities">
        <div class="fcal_create_calendar fcal_section_narrow fcal_section">
            <div style="padding:15px;" class="fcal_section_header">
                <div class="fcal_title">
                    <h3>Set your availability</h3>
                </div>
            </div>
            <div style="padding: 0;" v-loading="loading" class="fcal_section_body">
                <p>Choose a schedule below to edit or create a new one that you can apply to your event types</p>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'Availabilities',
    data() {
        return {
            loading: false
        }
    }
}
</script>
