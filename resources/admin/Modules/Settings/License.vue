<template>
    <div class="fcal_settings_body_inner fcal_license_settings fcal_configure_integration_card">
        <div class="fcal_settings_header fcal_configure_integration_card_header">
            <div class="fcal_settings_head">
                <h3>{{$t('License Management')}}</h3>
            </div>
            <div v-if="isEnabled" class="fcal_settings_actions">
                <el-button
                    class="refresh-setting-icon"
                    @click="getSettings">
                    <el-icon><Refresh /></el-icon>
                </el-button>
            </div>
        </div>

        <div v-if="isEnabled" v-loading="verifying" class="fcal_configure_integration_body">
            <div v-if="fetching" v-loading="fetching" class="text-align-center">
                <h3>{{$t("Fetching License Information Please wait")}}</h3>
            </div>

            <div v-else class="fcal_license_box" :class="'fc_license_'+licenseData.status">
                <div v-if="licenseData.status == 'expired'">
                    <h3>{{$t("Looks like your license has been expired")}} {{licenseData.expires}}</h3>
                    <a :href="licenseData.renew_url" target="_blank" class="el-button el-button--danger el-button--small">{{$t("Click Here to Renew your License")}}</a>

                    <p v-if="!showNewLicenseInput">{{$t('Have a new license Key?')}} <a @click.prevent="showNewLicenseInput = !showNewLicenseInput" href="#">{{$t('Click here')}}</a></p>
                    <div v-else>
                        <h3>{{$t('Your License Key')}}</h3>
                        <el-input v-model="licenseKey" :placeholder="$t('License Key')">
                            <template #append>
                                <el-button type="success" @click="verifyLicense()">
                                    <el-icon><Lock /></el-icon>
                                    {{$t('Verify License')}}
                                </el-button>
                            </template>
                        </el-input>
                    </div>
                </div>

                <div v-else-if="licenseData.status == 'valid'">
                    <div class="text-align-center">
                        <el-icon style="font-size: 50px;"><CircleCheck /></el-icon>
                        <!-- <span style="font-size: 50px;" class="el-icon el-icon-circle-check"></span> -->
                        <h2>{{$t('You license key is valid and activated')}}</h2>
                        <p>{{$t('Want to deactivate this license?')}} <a @click.prevent="deactivateLicense()" href="#">{{$t('Click here')}}</a></p>
                    </div>
                </div>
                
                <div v-else>
                    <h3>
                        {{ $t('Please Provide a license key of') }} FluentBooking
                    </h3>
                    <el-input v-model="licenseKey" :placeholder="$t('License Key')">
                        <template #append>
                            <el-button type="success" @click="verifyLicense()">
                                <el-icon><Lock /></el-icon>
                                {{$t('Verify License')}}
                            </el-button>
                        </template>
                    </el-input>
                    <p v-if="!showNewLicenseInput">{{ $t("Don't have a license key?") }} <a target="_blank" :href="licenseData.purchase_url">{{$t('Purchase one here')}}</a></p>
                </div>
            </div>

            <p class="fcal_warning" v-html="errorMessage"></p>
        </div>
        <ProNotice v-else/>
    </div>
</template>

<script>
import ProNotice from '@/Components/Common/ProNotice.vue';
import { Refresh, Lock, CircleCheck } from '@element-plus/icons-vue';
export default {
    name: 'License',
    props: ['disabled'],
    components: {
        Lock,
        Refresh,
        CircleCheck,
        ProNotice
    },
    data() {
        return {
            fetching: false,
            verifying: false,
            licenseData: {},
            licenseKey: '',
            showNewLicenseInput: false,
            errorMessage: '',
            isEnabled: !this.disabled && this.appVars.has_pro
        };
    },
    methods: {
        getSettings() {
            this.errorMessage = '';
            this.fetching = true;

            this.$get('settings/license', { verify: true })
                .then(response => {
                    this.licenseData = response;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.fetching = false;
                });
        },
        verifyLicense() {
            if (!this.licenseKey) {
                this.$handleError(this.$t('Please provide a license key'));
                this.errorMessage = this.$t('Please provide a license key');
                return;
            }

            this.verifying = true;

            this.errorMessage = '';

            this.$post('settings/license', {
                license_key: this.licenseKey,
            })
                .then(response => {
                    this.licenseData = response.license_data;
                    this.$handleSuccess(response.message);
                })
                .catch(errorResponse => {
                    this.errorMessage = this.$handleError(errorResponse);
                })
                .finally(() => {
                    this.verifying = false;
                });
        },
        deactivateLicense() {
            this.verifying = true;

            this.$del('settings/license')
                .then(response => {
                    this.licenseData = response.license_data;
                    this.$handleSuccess(response.message);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.verifying = false;
                });
        },
    },
    mounted() {
        if (this.isEnabled) {
            this.getSettings();
        }
    }
};
</script>
