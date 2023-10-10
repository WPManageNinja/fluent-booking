<template>
    <div class="fcal_calendar_settings">
        <template v-if="driver">
            <div class="fcal_settings_header">
                <div class="fcal_settings_head">
                    <h2>{{ driver.title }}</h2>
                    <p v-html="driver.description"></p>
                </div>
            </div>

            <div v-if="driver.configure_type == 'require_oauth'">
                <h3>{{ driver.oauth_content.instruction }}</h3>

                <div class="fcal_remote_calendar_block fcal_promt_box">
                    <div class="fcal_remote_header">
                        <div class="fcal_driver_brand">
                            <img :src="driver.icon"/>
                            <div class="fcal_driver_heading">
                                <h3>{{ driver.oauth_content.title }}</h3>
                                <p>{{ driver.oauth_content.subtitle }}</p>
                            </div>
                        </div>
                        <div class="fcal_driver_action">
                            <a :href="driver.oauth_content.btn_url"
                               class="el-button el-button--primary el-button--small">
                                {{driver.oauth_content.btn_text}}
                                </a>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'GeneralIntegrationFeedSettings',
    props: ['calendar', 'settings_key'],
    data() {
        return {
            settings: {},
            driver: null,
            loading: false,
            saving: false
        }
    },
    methods: {
        fetchSettings() {
            this.loading = true;
            this.$get('calendars/' + this.calendar.id + '/integrations/general_integration_feed', {
                settings_key: this.settings_key
            })
                .then(response => {
                    this.driver = response.driver;
                    this.settings = response.settings;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    mounted() {
        this.fetchSettings();
    }
}
</script>
