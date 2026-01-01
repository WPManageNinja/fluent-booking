<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header">
                <h2><RecurringIcon />{{ $t('Recurring Settings') }}</h2>
            </div>
            <template v-if="!disabled">
                <div class="fcal_create_calendar_form_body fcal_recurring_settings">
                    <el-form label-position="top">
                        <el-form-item>
                            <div class="fcal_event_card fcal_event_card_wrap">
                                <div class="fcal_event_card_header">
                                    <div class="card_contents">
                                        <span class="sub-label card-title">{{ $t("Recurring Event") }}</span>
                                        <span>{{ $t("RecurringSettings/recurring_event_description") }}</span>
                                    </div>
                                    <div class="card_action">
                                        <el-switch v-model="settings.recurring_config.enabled"/>
                                    </div>
                                </div>
                                <template v-if="settings.recurring_config.enabled">
                                    <div class="fcal_event_child_card">
                                        <div class="fcal_inline_items">
                                            <p>{{ $t('Repeats every') }}</p>
                                            <el-input v-model="settings.recurring_config.interval" type="number" @input="validateInterval()"/>
                                            <el-select v-model="settings.recurring_config.frequency" @change="validateFrequency()" :placeholder="$t('Select Unit')" popper-class="fcal_select">
                                                <el-option value="daily" :label="$t('Days')"></el-option>
                                                <el-option value="weekly" :label="$t('Weeks')"></el-option>
                                                <el-option value="monthly" :label="$t('Months')"></el-option>  
                                                <el-option value="yearly" :label="$t('Years')"></el-option>
                                            </el-select>
                                        </div>
                                        <div class="fcal_inline_items">
                                            <p>{{ $t('For a maximum of') }}</p>
                                            <el-input v-model="settings.recurring_config.max_count" type="number" @input="validateMaxCount()"/>
                                            <p>{{ $t('Events') }}</p>
                                        </div>
                                        <div class="fcal_inline_items">
                                            <el-checkbox v-model="settings.recurring_config.is_count_fixed"
                                                :label="$t('Require All Maximum Occurrences')"
                                            />
                                        </div>
                                        <p class="fcal_help_text">
                                            <strong>{{ $t('Note:') }} </strong> {{ $t('Ensure your calendar has at least') }} <strong>{{ getMinAvailableDays }}</strong> {{ $t('days of future availability to support all recurring occurrences') }}
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </el-form-item>
                    </el-form>
                </div>
                <div class="fcal_create_calendar_form_footer">
                    <SaveButton :saving="saving" :label="$t('Save Changes')" @click="saveSettings"/>
                </div>
            </template>
            <ProNotice v-else/>
        </div>
    </div>
</template>

<script>
import SaveButton from "@/Components/Buttons/SaveButton";
import ProNotice from "@/Components/Common/ProNotice.vue";
import RecurringIcon from "@/Components/Icons/RecurringIcon.vue";

export default {
    name: '_RecurringSettings',
    components: {
        SaveButton,
        ProNotice,
        RecurringIcon
    },
    props: ['calendar_event', 'disabled'],
    data() {
        return {
            saving: false,
            settings: this.calendar_event.settings
        }
    },
    computed: {
        getMinAvailableDays() {
            const frequency = this.settings.recurring_config.frequency;
            const interval = this.settings.recurring_config.interval;
            const maxCount = this.settings.recurring_config.max_count;
            const frequencyValues = {
                daily: 1,
                weekly: 7,
                monthly: 30,
                yearly: 365
            };
            return frequencyValues[frequency] * interval * maxCount;
        }
    },
    methods: {
        validateInterval() {
            const interval = this.settings.recurring_config.interval;
            const frequency = this.settings.recurring_config.frequency;
            const frequencyValues = {
                daily: 365,
                weekly: 52,
                monthly: 12,
                yearly: 1
            };
            if (isNaN(interval) || interval <= 0) {
                this.settings.recurring_config.interval = '';
            } else if (interval > frequencyValues[frequency]) {
                this.settings.recurring_config.interval = parseInt(frequencyValues[frequency]);
            }
            this.validateMaxCount();
        },
        validateMaxCount() {
            const maxCount = this.settings.recurring_config.max_count;
            const frequency = this.settings.recurring_config.frequency;
            const interval = this.settings.recurring_config.interval;
            const frequencyValues = {
                daily: 365 / interval,
                weekly: 52 / interval,
                monthly: 12 / interval,
                yearly: 1 / interval
            };
            if (isNaN(maxCount) || maxCount <= 0) {
                this.settings.recurring_config.max_count = '';
            } else if (maxCount > frequencyValues[frequency]) {
                this.settings.recurring_config.max_count = parseInt(frequencyValues[frequency]);
            }
        },
        validateFrequency() {
            this.validateInterval();
            this.validateMaxCount();
        },
        saveSettings() {
            this.saving = true;
            this.$post('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/recurring-settings', {
                calendar_id: this.calendar_event.calendar_id,
                recurring_config: this.settings.recurring_config
            })
                .then(response => {
                    this.$handleSuccess(response);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        }
    }
}
</script>
