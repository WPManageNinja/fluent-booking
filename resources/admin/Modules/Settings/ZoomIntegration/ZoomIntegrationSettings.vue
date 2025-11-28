<template>
    <div class="fcal_settings_body_inner fcal_settings_general">
        <div class="fcal_settings_content_wrap">
            <div class="fcal_configure_integrations">
                <div class="fcal_configure_integration_card">
                    <div class="fcal_configure_integration_card_header">
                        <div class="left">
                            <div class="img-box">
                                <img :src="appVars.asset_url + 'images/zoom.svg'"/>
                            </div>
                            <div class="content">
                                <h3>{{ $t('Zoom Integrations Settings') }}</h3>
                                <p>
                                    {{ $t('ZoomIntegrationSettings/description') }}
                                    <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/zoom-integration-with-fluentbooking/">{{ $t('Please read the documentation') }}</a> {{ $t('ZoomIntegrationSettings/sub_description') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-if="isEnabled" class="fcal_configure_integration_body">
                        <el-skeleton v-if="loading" :rows="3" animated></el-skeleton>
                        <template v-else>
                            <div v-if="!connected_users.length" class="fcal_box_padded">
                                <h3>{{ $t('ZoomIntegrationSettings/didnot_connect_zoom_desc') }}</h3>
                                <p>{{ $t('ZoomIntegrationSettings/connect_zoom_desc') }}</p>
                                <el-button @click="showingForm = true" type="primary">{{ $t('Connect Your Zoom Account') }}
                                </el-button>
                            </div>
                            <template v-else>
                                <div class="fcal_section_header">
                                    <div class="fcal_title">
                                        <h3 style="font-size: 18px;">{{ $t('Connected Zoom Accounts') }}</h3>
                                    </div>
                                    <div class="fcal_actions">
<!--                                        <el-button @click="showingForm = true" type="primary">Add New User Account-->
<!--                                        </el-button>-->
                                    </div>
                                </div>
                                <div class="fcal_section_body">
                                    <div v-for="connectedAccount in connected_users" class="fcal_remote_calendar_block">
                                        <each-zoom-account @disconnected="fetchConnectedUsers()" :connectedAccount="connectedAccount"/>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>
                    <ProNotice v-else/>
                </div>
            </div>
        </div>

        <el-dialog :append-to-body="true" :close-on-click-modal="false" class="fcal_dialog" v-model="showingForm"
                   :title="$t('Add New Zoom User Account')" width="50%">
            <integration-form @connected="fetchConnectedUsers()" v-if="showingForm" :form_fields="form_fields" @close="showingForm = false"/>
        </el-dialog>
    </div>
</template>

<script>
import IntegrationForm from "@/Modules/Settings/ZoomIntegration/IntegrationForm.vue";
import EachZoomAccount from './EachAccount.vue';
import ProNotice from "@/Components/Common/ProNotice.vue";

export default {
    name: 'ZoomIntegrationSettings',
    props: ['disabled'],
    components: {
    EachZoomAccount,
    IntegrationForm,
    ProNotice
},
    data() {
        return {
            connected_users: [],
            loading: true,
            is_current_user_connected: false,
            form_fields: {},
            showingForm: false,
            disconnecting: false,
            isEnabled: !this.disabled && this.appVars.has_pro
        }
    },
    methods: {
        fetchConnectedUsers() {
            this.showingForm = false;
            this.$get('integrations/zoom/connected-users')
                .then(response => {
                    this.connected_users = response.zoom_users;
                    this.is_current_user_connected = response.is_current_user_configured;
                    this.form_fields = response.form_fields;
                })
                .catch((errors) => {
                    this.$handleError(errors)
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
    mounted() {
        if (this.isEnabled) {
            this.fetchConnectedUsers();
        }
    }
}
</script>
