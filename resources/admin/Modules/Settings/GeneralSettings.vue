<template>
    <div>
        <div style="margin-bottom: 25px;" class="fcal_settings_body_inner fcal_settings_general">
            <div class="fcal_configure_integration_card">
                <div class="fcal_configure_integration_card_header">
                    <div class="left">
                        <div class="img-box">
                            <el-icon style="font-size: 30px;">
                                <Operation/>
                            </el-icon>
                        </div>
                        <div class="content">
                            <h3>{{ $t('General Settings') }}</h3>
                            <p>{{ $t('GeneralSettings/description') }}</p>
                        </div>
                    </div>
                </div>
                <el-skeleton animated v-if="loading"></el-skeleton>
                <div v-else class="fcal_configure_integration_body fc_global_form_builder">
                    <el-form v-model="administration" label-position="top">
                        <el-row :gutter="30">
                            <el-col :sm="24" :md="8">
                                <el-form-item :label="$t('Admin Email')">
                                    <el-input v-model="administration.admin_email"
                                              :placeholder="$t('Admin Email')"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :sm="24" :md="8">
                                <el-form-item :label="$t('Calendar start from')">
                                    <el-select v-model="administration.start_day" popper-class="fcal_select"
                                               :placeholder="$t('Select')" placement="bottom">
                                        <el-option
                                            v-for="item in weekdays"
                                            :key="item.value"
                                            :label="item.label"
                                            :value="item.value"
                                        />
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :sm="24" :md="8">
                                <el-form-item :label="$t('Default Time Format')">
                                    <el-radio-group v-model="timeFormat">
                                        <el-radio label="12">{{ $t('12h') }}</el-radio>
                                        <el-radio label="24">{{ $t('24h') }}</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        <el-row :gutter="30">
                            <el-col :sm="24" :md="8">
                                <el-form-item :label="$t('Summary Email')">
                                    <el-checkbox v-model="administration.summary_notification" true-label="yes"
                                                 false-label="no"> {{ $t('Enable Booking Summary Notification') }}
                                    </el-checkbox>
                                </el-form-item>
                            </el-col>
                            <el-col v-if="administration.summary_notification == 'yes'" :sm="24" :md="16">
                                <el-row :gutter="30">
                                    <el-col :sm="24" :md="12">
                                        <el-form-item :label="$t('How often to send summary email?')">
                                            <el-select v-model="administration.notification_frequency"
                                                       :placeholder="$t('Select Frequency')" popper-class="fcal_select"
                                                       placement="bottom">
                                                <el-option value="daily" :label="$t('Daily')"></el-option>
                                                <el-option value="weekly" :label="$t('Weekly')"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :sm="24" :md="12">
                                        <el-form-item v-if="administration.notification_frequency == 'weekly'"
                                                      :label="$t('In which day to send the email?')">
                                            <el-select v-model="administration.notification_day"
                                                       :placeholder="$t('Select Day')"
                                                       popper-class="fcal_select" placement="bottom">
                                                <el-option value="mon" :label="$t('Monday')"></el-option>
                                                <el-option value="tue" :label="$t('Tuesday')"></el-option>
                                                <el-option value="wed" :label="$t('Wednesday')"></el-option>
                                                <el-option value="thu" :label="$t('Thursday')"></el-option>
                                                <el-option value="fri" :label="$t('Friday')"></el-option>
                                                <el-option value="sat" :label="$t('Saturday')"></el-option>
                                                <el-option value="sun" :label="$t('Sunday')"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-col>
                        </el-row>

                        <el-row :gutter="30">
                            <el-col :sm="24" :md="8">
                                <el-form-item :label="$t('Mark booking as cancelled automatically after')">
                                    <el-select popper-class="fcal_select" v-model="administration.auto_cancel_timing">
                                        <el-option value="5" :label="$t('5 Minutes')"></el-option>
                                        <el-option value="10" :label="$t('10 Minutes')"></el-option>
                                        <el-option value="20" :label="$t('20 Minutes')"></el-option>
                                        <el-option value="30" :label="$t('30 Minutes')"></el-option>
                                        <el-option value="40" :label="$t('40 Minutes')"></el-option>
                                        <el-option value="50" :label="$t('50 Minutes')"></el-option>
                                        <el-option value="60" :label="$t('60 Minutes')"></el-option>
                                        <el-option value="120" :label="$t('2 Hours')"></el-option>
                                        <el-option value="180" :label="$t('3 Hours')"></el-option>
                                        <el-option value="360" :label="$t('6 Hours')"></el-option>
                                        <el-option value="720" :label="$t('12 Hours')"></el-option>
                                    </el-select>
                                    <p>{{ $t('if customer does not complete the payment for paid events.') }}</p>
                                </el-form-item>
                            </el-col>
                            <el-col :sm="24" :md="8">
                                <el-form-item :label="$t('Mark booking as completed automatically after')">
                                    <el-select popper-class="fcal_select" v-model="administration.auto_complete_timing">
                                        <el-option value="5" :label="$t('5 Minutes')"></el-option>
                                        <el-option value="10" :label="$t('10 Minutes')"></el-option>
                                        <el-option value="20" :label="$t('20 Minutes')"></el-option>
                                        <el-option value="30" :label="$t('30 Minutes')"></el-option>
                                        <el-option value="40" :label="$t('40 Minutes')"></el-option>
                                        <el-option value="50" :label="$t('50 Minutes')"></el-option>
                                        <el-option value="60" :label="$t('60 Minutes')"></el-option>
                                        <el-option value="120" :label="$t('2 Hours')"></el-option>
                                        <el-option value="180" :label="$t('3 Hours')"></el-option>
                                        <el-option value="360" :label="$t('6 Hours')"></el-option>
                                        <el-option value="720" :label="$t('12 Hours')"></el-option>
                                    </el-select>
                                    <p>{{ $t('from the event end time') }}</p>
                                </el-form-item>
                            </el-col>
                        </el-row>

                    </el-form>

                    <div style="margin-top: 20px; text-align: right;" class="fcal_settings_footer">
                        <el-button :disabled="saving" v-loading="saving" @click="saveSettings()"
                                   class="fcal_primary_btn">
                            {{ $t('Save Settings') }}
                        </el-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="fcal_settings_body_inner fcal_settings_general">
            <div class="fcal_configure_integration_card">
                <div class="fcal_configure_integration_card_header">
                    <div class="left">
                        <div class="content">
                            <h3>{{ $t('Emailing Settings') }}</h3>
                            <p>{{ $t('GeneralSettings/email_settings_description') }}</p>
                        </div>
                    </div>
                </div>
                <el-skeleton animated v-if="loading"></el-skeleton>
                <div v-else class="fcal_configure_integration_body">
                    <form-builder :formData="emailing" :fields="emailingFields"/>
                    <div style="margin-top: 20px; text-align: right;" class="fcal_settings_footer">
                        <el-button :disabled="saving" v-loading="saving" @click="saveSettings()"
                                   class="fcal_primary_btn">
                            {{ $t('Save Settings') }}
                        </el-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import FormBuilder from '@/Components/FormBuilder/FormBuilder.vue';

export default {
    name: "GeneralSettings",
    components: {
        FormBuilder
    },
    data() {
        return {
            emailing: {},
            emailingFields: {},
            administration: {},
            weekdays: [
                {
                    value: 'mon',
                    label: this.$t('Monday')
                },
                {
                    value: 'tue',
                    label: this.$t('Tuesday')
                },
                {
                    value: 'wed',
                    label: this.$t('Wednesday')
                },
                {
                    value: 'thu',
                    label: this.$t('Thursday')
                },
                {
                    value: 'fri',
                    label: this.$t('Friday')
                },
                {
                    value: 'sat',
                    label: this.$t('Saturday')
                },
                {
                    value: 'sun',
                    label: this.$t('Sunday')
                }
            ],
            loading: false,
            saving: false,
            timeFormat: '12'
        }
    },
    methods: {
        fetchSettings() {
            this.loading = true;
            this.$get('settings/general')
                .then(response => {
                    this.emailing = response.emailing;
                    this.administration = response.administration;
                    this.emailingFields = response.emailingFields;
                    this.timeFormat = response.time_format;
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        saveSettings() {
            this.saving = true;
            this.$post('settings/general', {
                emailing: this.emailing,
                administration: this.administration,
                timeFormat: this.timeFormat,
            })
                .then(response => {
                    this.$notify.success(response.message);
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.saving = false;
                });
        }
    },
    mounted() {
        this.fetchSettings();
    }
}
</script>
