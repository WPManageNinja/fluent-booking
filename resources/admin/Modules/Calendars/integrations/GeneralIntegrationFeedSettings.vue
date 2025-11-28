<template>
    <div class="fcal_calendar_settings">
        <el-skeleton v-if="loading" :animated="true" :rows="5" />
        <template v-else-if="driver">
            <div class="fcal_settings_header">
                <div class="fcal_settings_head">
                    <h2>{{ driver.title }}</h2>
                    <p v-html="driver.description"></p>
                </div>
            </div>
            <div v-if="driver.configure_type == 'oauth'">
                <h3>{{ driver.oauth_content.instruction }}</h3>

                <div class="fcal_remote_calendar_block fcal_promt_box">
                    <div class="fcal_remote_header">
                        <div class="fcal_driver_brand">
                            <img :src="driver.icon"/>
                            <div class="fcal_driver_heading">
                                <h3>{{ driver.oauth_content.title }}</h3>
                                <p v-html="driver.oauth_content.subtitle"></p>
                            </div>
                        </div>
                        <div class="fcal_driver_action">
                            <a v-if="driver.oauth_content.btn_url" :href="driver.oauth_content.btn_url"
                               class="el-button el-button--primary el-button--small">
                                {{ driver.oauth_content.btn_text }}
                            </a>
                            <el-button v-else-if="driver.oauth_content.disconnect_text" v-loading="disconnecting"
                                       :disabled="disconnecting" @click="disconnect()" type="danger" plain>
                                {{ driver.oauth_content.disconnect_text }}
                            </el-button>
                        </div>
                    </div>
                </div>
            </div>
            <p v-if="driver.secure_message">
                <hr />
                <el-icon><Lock /></el-icon>
                <span v-html="driver.secure_message"></span>
            </p>
        </template>
        <div v-else>
            <h3>{{ $t('GeneralIntegrationFeedSettings/failed_to_load_desc') }}</h3>
        </div>
    </div>
</template>

<script type="text/babel">
import { Lock } from "@element-plus/icons-vue";

export default {
    name: 'GeneralIntegrationFeedSettings',
    components: { Lock },
    props: ['calendar', 'settings_key'],
    data() {
        return {
            disconnecting: false,
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
                calendar_id : this.calendar.id,
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
        },
        disconnect() {
            this.$confirm(this.$t('Are you sure you want to disconnect this integration?'), this.$t('Disconnect Integration'), {
                confirmButtonText: this.$t('Confirm, Disconnect'),
                cancelButtonText: this.$t('Cancel'),
                type: 'warning'
            }).then(() => {
                this.disconnecting = true;
                this.$post('calendars/' + this.calendar.id + '/integrations/general_integration_feed/disconnect', {
                    calendar_id : this.calendar.id,
                    settings_key: this.settings_key
                })
                    .then(response => {
                        this.$notify.success(response.message);
                        this.fetchSettings();
                    })
                    .catch(errors => {
                        this.$handleError(errors);
                    })
                    .finally(() => {
                        this.disconnecting = false;
                    });
            });
        }
    },
    mounted() {
        this.fetchSettings();
    }
}
</script>
