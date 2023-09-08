<template>
    <div class="fcal_settings">
        <div class="fcal_section_narrow fcal_section">
            <div style="padding:15px;" class="fcal_section_header">
                <div class="fcal_title">
                    <h3>Settings</h3>
                </div>
            </div>
            <div v-loading="loading" class="fcal_settings_container">
                <div class="fcal_section_body">
                    <div class="fcal_settings_title">
                        <p>Configure Integration</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Settings',
    data() {
        return {
            loading: false
        }
    }
}
</script>
