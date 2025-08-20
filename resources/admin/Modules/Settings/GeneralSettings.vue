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
                                <el-form-item :label="$t('Mark booking as cancelled automatically after')">
                                    <el-select popper-class="fcal_select" v-model="administration.auto_cancel_timing">
                                        <el-option 
                                            v-for="time in statusChangingTimes"
                                            :value="time.value"
                                            :label="time.label">
                                        </el-option>
                                    </el-select>
                                    <p>{{ $t('if customer does not complete the payment for paid events.') }}</p>
                                </el-form-item>
                            </el-col>
                            <el-col :sm="24" :md="8">
                                <el-form-item :label="$t('Mark booking as completed automatically after')">
                                    <el-select popper-class="fcal_select" v-model="administration.auto_complete_timing">
                                        <el-option 
                                            v-for="time in statusChangingTimes"
                                            :value="time.value"
                                            :label="time.label">
                                        </el-option>
                                    </el-select>
                                    <p>{{ $t('from the event end time') }}</p>
                                </el-form-item>
                            </el-col>
                            <el-col :sm="24" :md="8">
                                <el-form-item :label="$t('Default Country for Phone Field')">
                                    <el-select v-model="administration.default_country"
                                               filterable
                                               clearable
                                               :placeholder="$t('Select Country')" popper-class="fcal_select"
                                               placement="bottom">
                                        <el-option
                                            v-for="(item, itemKey) in all_countries"
                                            :key="itemKey"
                                            :label="item"
                                            :value="itemKey"
                                        />
                                    </el-select>
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
                                        <el-form-item :label="$t('Email Frequency?')">
                                            <el-select v-model="administration.notification_frequency"
                                                       :placeholder="$t('Select Frequency')" popper-class="fcal_select"
                                                       placement="bottom">
                                                <el-option value="daily" :label="$t('Daily')"></el-option>
                                                <el-option value="weekly" :label="$t('Weekly')"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :sm="24" :md="12">
                                        <el-form-item v-if="administration.notification_frequency == 'weekly'" :label="$t('In which day to send the email?')">
                                            <el-select v-model="administration.notification_day" :placeholder="$t('Select Day')" popper-class="fcal_select" placement="bottom">
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

                    </el-form>

                    <div style="margin-top: 20px; text-align: right;" class="fcal_settings_footer">
                        <el-button :disabled="saving" v-loading="saving" @click="saveSettings()" class="fcal_primary_btn">
                            {{ $t('Save Settings') }}
                        </el-button>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-bottom: 25px;" class="fcal_settings_body_inner fcal_settings_general">
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
                        <el-button :disabled="saving" v-loading="saving" @click="saveSettings()" class="fcal_primary_btn">
                            {{ $t('Save Settings') }}
                        </el-button>
                    </div>
                </div>
            </div>
        </div>

        <div class="fcal_settings_body_inner fcal_settings_apperance">
            <div class="fcal_configure_integration_card">
                <div class="fcal_configure_integration_card_header">
                    <div class="left">
                        <div class="content">
                            <h3>{{ $t('Theme') }}</h3>
                            <p>{{ $t('This only applies to your public landing pages') }}</p>
                        </div>
                    </div>
                </div>

                <el-skeleton animated v-if="loading"></el-skeleton>
                <div v-else class="fcal_configure_integration_body">
                    <el-radio-group v-model="theme" class="fcal_appearance_theme">
                        <el-radio label="system-default">
                            <SystemDefault/>
                            <h4>{{ $t('System Default') }}</h4>
                        </el-radio>
                        <el-radio label="light-mode">
                            <ModeLight/>
                            <h4>{{ $t('Light') }}</h4>
                        </el-radio>
                        <el-radio label="dark-mode">
                            <ModeDark/>
                            <h4>{{ $t('Dark') }}</h4>
                        </el-radio>
                    </el-radio-group>

                    <div style="margin-top: 20px; text-align: right;" class="fcal_settings_footer">
                        <el-button :disabled="themeSaving" v-loading="themeSaving" @click="saveThemeSettings()" class="fcal_primary_btn">
                            {{ $t('Save Settings') }}
                        </el-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import FormBuilder from '@/Components/FormBuilder/FormBuilder.vue';
import SystemDefault from "@/Pieces/theme/SystemDefault";
import ModeLight from "@/Pieces/theme/ModeLight";
import ModeDark from "@/Pieces/theme/ModeDark";

export default {
    name: "GeneralSettings",
    components: {
        ModeDark,
        ModeLight,
        SystemDefault,
        FormBuilder
    },
    data() {
        return {
            emailing: {},
            emailingFields: {},
            administration: {},
            theme: '',
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
            themeSaving: false,
            timeFormat: '12',
            all_countries: {},
            all_currencies: {},
            statusChangingTimes: this.appVars.status_changing_times
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
                    this.theme = response.theme;
                    this.timeFormat = response.time_format;
                    this.all_countries = response.all_countries;
                    this.all_currencies = response.all_currencies;
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
                timeFormat: this.timeFormat
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
        },
        saveThemeSettings() {
            this.themeSaving = true;
            this.$post('settings/theme', {
                theme: this.theme
            })
                .then(response => {
                    this.$notify.success(response.message);
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.themeSaving = false;
                });
        }
    },
    mounted() {
        this.fetchSettings();
    }
}
</script>
