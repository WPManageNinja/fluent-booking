<template>
    <div>
        <div style="margin-bottom: 25px;" class="fcal_settings_body_inner fcal_settings_general">
            <div class="fcal_configure_integration_card">
                <div class="fcal_configure_integration_card_header">
                    <div class="left">
                        <div class="img-box">
                            <el-icon style="font-size: 30px;">
                                <Operation />
                            </el-icon>
                        </div>
                        <div class="content">
                            <h3>General Settings</h3>
                            <p>Manage your settings related emails, notifications and other general settings</p>
                        </div>
                    </div>
                </div>
                <el-skeleton animated v-if="loading"></el-skeleton>
                <div v-else class="fcal_configure_integration_body fc_global_form_builder">
                    <el-form v-model="administration" label-position="top">
                        <el-row :gutter="30">
                            <el-col :sm="24" :md="8">
                                <el-form-item label="Admin Email">
                                    <el-input v-model="administration.admin_email" placeholder="Admin Email"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :sm="24" :md="8">
                                <el-form-item label="Calendar start from">
                                    <el-select v-model="administration.start_day" popper-class="fcal_select" placeholder="Select" placement="bottom">
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
                                <el-form-item label="Time Format">
                                    <el-radio-group v-model="timeFormat">
                                        <el-radio label="12">12h</el-radio>
                                        <el-radio label="24">24h</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :sm="24" :md="8">
                                <el-form-item label="Summary Email">
                                <el-checkbox v-model="administration.summary_notification" true-label="yes"
                                             false-label="no"> Enable Booking Summary Notification
                                </el-checkbox>
                            </el-form-item>
                            </el-col>

                            <el-col v-if="administration.summary_notification == 'yes'" :sm="24" :md="16">
                                <el-row :gutter="30">
                                    <el-col :sm="24" :md="12">
                                        <el-form-item label="How often to send summary email?">
                                            <el-select v-model="administration.notification_frequency"
                                                       placeholder="Select Frequency" popper-class="fcal_select" placement="bottom">
                                                <el-option value="daily" label="Daily"></el-option>
                                                <el-option value="weekly" label="Weekly"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :sm="24" :md="12">
                                        <el-form-item v-if="administration.notification_frequency == 'weekly'"
                                                      label="In which day to send the email?">
                                            <el-select v-model="administration.notification_day" placeholder="Select Day"
                                                       popper-class="fcal_select" placement="bottom">
                                                <el-option value="mon" label="Monday"></el-option>
                                                <el-option value="tue" label="Tuesday"></el-option>
                                                <el-option value="wed" label="Wednesday"></el-option>
                                                <el-option value="thu" label="Thursday"></el-option>
                                                <el-option value="fri" label="Friday"></el-option>
                                                <el-option value="sat" label="Saturday"></el-option>
                                                <el-option value="sun" label="Sunday"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-col>
                        </el-row>

                    </el-form>

                    <div style="margin-top: 20px; text-align: right;" class="fcal_settings_footer">
                        <el-button :disabled="saving" v-loading="saving" @click="saveSettings()"
                                   class="fcal_primary_btn">
                            Save Settings
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
                            <h3>Emailing Settings</h3>
                            <p>Configure your email settings for booking related emails</p>
                        </div>
                    </div>
                </div>
                <el-skeleton animated v-if="loading"></el-skeleton>
                <div v-else class="fcal_configure_integration_body">
                    <form-builder :formData="emailing" :fields="emailingFields"/>
                    <div style="margin-top: 20px; text-align: right;" class="fcal_settings_footer">
                        <el-button :disabled="saving" v-loading="saving" @click="saveSettings()"
                                   class="fcal_primary_btn">
                            Save Settings
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
                label: 'Monday'
              },
              {
                value: 'tue',
                label: 'Tuesday'
              },
              {
                value: 'wed',
                label: 'Wednesday'
              },
              {
                value: 'thu',
                label: 'Thursday'
              },
              {
                value: 'fri',
                label: 'Friday'
              },
              {
                value: 'sat',
                label: 'Saturday'
              },
              {
                value: 'sun',
                label: 'Sunday'
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
