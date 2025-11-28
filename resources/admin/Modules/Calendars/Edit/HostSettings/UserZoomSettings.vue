<template>
    <div class="fcal_calendar_settings">
        <div class="fcal_settings_header">
            <div class="fcal_settings_head">
                <h2>{{ $t('Zoom Integrations Settings') }}</h2>
                <p>{{ $t('Connect your Zoom account to create meeting when a event is booked.') }}</p>
            </div>
        </div>
        <template v-if="!disabled">
            <el-skeleton :rows="4" animated v-if="loading"/>
            <div v-else class="fcal_calendar_body">
                <div v-if="connection" class="fcal_remote_calendar_block">
                    <each-zoom-account @disconnected="fetchConnection()" :calendar_id="calendar.id" :connectedAccount="connection"/>
                    <p style="padding: 10px 20px;">{{ $t('UserZoomSettings/zoom_meeting_location_desc') }}</p>
                </div>
                <div v-else class="fcal_box_padded">
                    <h3>{{ $t('UserZoomSettings/connect_zoom_desc') }}</h3>
                    <el-button @click="showingForm = true" type="primary">
                        {{ $t('Connect Your Zoom Account') }}
                    </el-button>
    
                    <el-dialog
                        :append-to-body="true"
                        :close-on-click-modal="false"
                        v-model="showingForm"
                        :title="$t('Connect Your Zoom Account')"
                        width="50%"
                        class="fcal_dialog fcal_dialog_body_p0 fcal_zoom_dialog"
                    >
                        <integration-form @connected="fetchConnection()"
                                          :calendar_id="calendar.id"
                                          v-if="showingForm"
                                          :form_fields="form_fields"/>
                    </el-dialog>
    
                </div>
            </div>
        </template>
        <ProNotice v-else/>
    </div>
</template>

<script>
import ProNotice from "@/Components/Common/ProNotice.vue";
import EachZoomAccount from "@/Modules/Settings/ZoomIntegration/EachAccount.vue";
import IntegrationForm from "@/Modules/Settings/ZoomIntegration/IntegrationForm.vue";

export default {
    name: 'UserZoomSettings',
    components: {
    IntegrationForm,
    EachZoomAccount,
    ProNotice
},
    props: ['calendar', 'disabled'],
    data() {
        return {
            connection: null,
            form_fields: {},
            loading: true,
            showingForm: false
        }
    },
    methods: {
        fetchConnection() {
            this.loading = true;
            this.$get('calendars/' + this.calendar.id + '/integrations/zoom-connection', {
                calendar_id : this.calendar.id
            })
                .then(response => {
                    this.connection = response.connection;
                    this.form_fields = response.form_fields;
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
        if (!this.disabled) {
            this.fetchConnection();
        }
    }
}
</script>
