<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header">
                <h2>
                    <el-icon class="operation"><Operation /></el-icon>
                    {{ $t('Advanced Settings') }}
                </h2>
            </div>
            <template v-if="!disabled">
                <div class="fcal_create_calendar_form_body fcal_advanced_settings">
                    <el-form label-position="top">
    
                        <el-form-item>
                            <div class="fcal_event_card">
                                <el-form-item :label="$t('Booking Title')">
                                    <popover
                                        :groupTitle="$t('Shortcodes')"
                                        :data="smart_codes.texts"
                                        placement="bottom-end"
                                        :isVisible="titlePopupVisible"
                                        class="fcal_popover_shortcode"
                                        @command="handleBookingTitleCommand">
                                        <template #popoverButton>
                                            <el-input
                                                type="text"
                                                :placeholder="bookingTitle"
                                                v-model="settings.booking_title">
                                                <template #append>
                                                    <el-button :icon="MoreIcon" @click="toggleTitlePopupVisible"></el-button>
                                                </template>
                                            </el-input>
                                        </template>
                                    </popover>
                                </el-form-item>
                            </div>
                        </el-form-item>
                        <el-form-item>
                            <div class="fcal_event_card">
                                <el-form-item :label="$t('Submit Button Text')">
                                    <el-input
                                        type="text"
                                        v-model="settings.submit_button_text"
                                        :placeholder="$t('Schedule Meeting')"
                                    />
                                </el-form-item>
                            </div>
                        </el-form-item>
                        <el-form-item>
                            <div class="fcal_event_card fcal_event_card_wrap">
                                <div class="fcal_event_card_header">
                                    <div class="card_contents">
                                        <span class="sub-label card-title">{{ $t("Redirect After Booking") }}</span>
                                        <span>{{ $t("AdvancedSettings/redirect_url_description") }}</span>
                                    </div>
                                    <div class="card_action">
                                        <el-switch v-model="settings.custom_redirect.enabled"/>
                                    </div>
                                </div>
                                <div class="fcal_event_child_card" v-if="settings.custom_redirect.enabled">
                                    <el-form-item :label="$t('Redirect URL')">
                                        <popover
                                            :groupTitle="$t('Shortcodes')"
                                            :data="smart_codes.texts"
                                            placement="bottom-end"
                                            :isVisible="urlPopupVisible"
                                            class="fcal_popover_shortcode"
                                            @command="handleRedirectUrlCommand">
                                            <template #popoverButton>
                                                <el-input
                                                    type="text"
                                                    :placeholder="$t('AdvancedSettings/redirect_url_placeholder')"
                                                    v-model="settings.custom_redirect.redirect_url">
                                                    <template #append>
                                                        <el-button :icon="MoreIcon" @click="toggleUrlPopupVisible"></el-button>
                                                    </template>
                                                </el-input>
                                            </template>
                                        </popover>
                                    </el-form-item>
                                    <el-form-item :label="$t('Redirect Query String')">
                                        <el-checkbox true-label="yes" false-label="no" v-model="settings.custom_redirect.is_query_string">{{ $t('Pass Field Data Via Query String') }}</el-checkbox>
                                        <popover
                                            v-if="enabledQueryString"
                                            :groupTitle="$t('Shortcodes')"
                                            :data="smart_codes.texts"
                                            placement="bottom-end"
                                            :isVisible="queryPopupVisible"
                                            class="fcal_popover_shortcode"
                                            @command="handleRedirectQueryCommand">
                                            <template #popoverButton>
                                                <el-input
                                                    type="text"
                                                    :placeholder="$t('Redirect Query String')"
                                                    v-model="settings.custom_redirect.query_string">
                                                    <template #append>
                                                        <el-button :icon="MoreIcon" @click="toggleQueryPopupVisible"></el-button>
                                                    </template>
                                                </el-input>
                                            </template>
                                        </popover>
                                        <p v-if="enabledQueryString" class="fcal_event_input_hint">
                                            <em>{{ $t('AdvancedSettings/redirect_query_string_hint') }}</em>
                                        </p>
                                    </el-form-item>
                                </div>
                            </div>
                        </el-form-item>
    
                        <el-form-item v-if="showRequiresConfirmation">
                            <div class="fcal_event_card fcal_event_card_wrap">
                                <div class="fcal_event_card_header">
                                    <div class="card_contents">
                                        <span class="sub-label card-title">{{ $t("Requires Confirmation") }}</span>
                                        <span>{{ $t("LimitSettings/requires_confirmation_description") }}</span>
                                    </div>
                                    <div class="card_action">
                                        <el-switch v-model="settings.requires_confirmation.enabled"/>
                                    </div>
                                </div>
                                <div class="fcal_event_child_card" v-if="settings.requires_confirmation.enabled">
                                    <el-radio-group v-model="settings.requires_confirmation.type">
                                        <el-radio label="always"> {{ $t('Always') }}</el-radio>
                                        <el-radio label="conditional">{{ $t('When booking notice is less than') }}
                                            <span>
                                                <el-input v-model="settings.requires_confirmation.condition.value" @input="validateInput(settings.requires_confirmation)"></el-input>
                                                <el-select v-model="settings.requires_confirmation.condition.unit" @change="validateInput(settings.requires_confirmation)" :placeholder="$t('Select Unit')" popper-class="fcal_select">
                                                    <el-option value="minutes" :label="$t('Minutes')"></el-option>
                                                    <el-option value="hours" :label="$t('Hours')"></el-option>
                                                </el-select>
                                            </span>
                                        </el-radio>
                                    </el-radio-group>
                                </div>
                            </div>
                        </el-form-item>

                        <el-form-item v-if="showMultipleBooking">
                            <div class="fcal_event_card fcal_event_card_wrap">
                                <div class="fcal_event_card_header">
                                    <div class="card_contents">
                                        <span class="sub-label card-title">{{ $t("Allow Multiple Booking") }}</span>
                                        <span>{{ $t("AdvancedSettings/single_multi_booking_description") }}</span>
                                    </div>
                                    <div class="card_action">
                                        <el-switch v-model="settings.multiple_booking.enabled"/>
                                    </div>
                                </div>
                                <div class="fcal_event_child_card" v-if="settings.multiple_booking.enabled">
                                    <el-form-item :label="$t('Maximum Booking Limit')">
                                        <el-input v-model="settings.multiple_booking.limit" @input="validateLimit(settings.multiple_booking)"></el-input>
                                        <p class="fcal_event_input_hint">{{ $t("AdvancedSettings/multiple_booking_limit_hint") }}</p>
                                    </el-form-item>
                                </div>
                            </div>
                        </el-form-item>

                        <el-form-item>
                            <div class="fcal_event_card fcal_event_card_wrap">
                                <div class="fcal_event_card_header">
                                    <div class="card_contents">
                                        <span class="sub-label card-title">{{ $t("Attendee Cannot Cancel") }}</span>
                                        <span v-if="settings.can_not_cancel.enabled">{{ $t("AdvancedSettings/cannot_cancel_description") }}</span>
                                        <span v-else>{{ $t("AdvancedSettings/can_cancel_description") }}</span>
                                    </div>
                                    <div class="card_action">
                                        <el-switch v-model="settings.can_not_cancel.enabled"/>
                                    </div>
                                </div>
                                <template v-if="settings.can_not_cancel.enabled">
                                    <div class="fcal_event_child_card">
                                        <el-radio-group v-model="settings.can_not_cancel.type">
                                            <el-radio label="always"> {{ $t('Always') }}</el-radio>
                                            <el-radio label="conditional">{{ $t('When meeting starts in less than') }}
                                                <span>
                                                    <el-input v-model="settings.can_not_cancel.condition.value" @input="validateInput(settings.can_not_cancel)"></el-input>
                                                    <el-select v-model="settings.can_not_cancel.condition.unit" @change="validateInput(settings.can_not_cancel)" :placeholder="$t('Select Unit')" popper-class="fcal_select">
                                                        <el-option value="minutes" :label="$t('Minutes')"></el-option>
                                                        <el-option value="hours" :label="$t('Hours')"></el-option>
                                                    </el-select>
                                                </span>
                                            </el-radio>
                                        </el-radio-group>
                                    </div>
                                    <div class="fcal_event_child_card">
                                        <el-form-item :label="$t('Permission Denied Message')">
                                            <popover
                                                :groupTitle="$t('Shortcodes')"
                                                :data="smart_codes.texts"
                                                placement="bottom-end"
                                                :isVisible="cancelPopupVisible"
                                                class="fcal_popover_shortcode"
                                                @command="handleCancelCommand">
                                                <template #popoverButton>
                                                    <el-input
                                                        type="text"
                                                        :placeholder="$t('Sorry! you can not cancel this')"
                                                        v-model="settings.can_not_cancel.message">
                                                        <template #append>
                                                            <el-button :icon="MoreIcon" @click="toggleCancelPopupVisible"></el-button>
                                                        </template>
                                                    </el-input>
                                                </template>
                                            </popover>
                                            <p class="fcal_help_text">{{ $t("AdvancedSettings/cannot_cancel_message_hint") }}</p>
                                        </el-form-item>
                                    </div>
                                </template>
                            </div>
                        </el-form-item>
    
                        <el-form-item v-if="showReschedulingCondition">
                            <div class="fcal_event_card fcal_event_card_wrap">
                                <div class="fcal_event_card_header">
                                    <div class="card_contents">
                                        <span class="sub-label card-title">{{ $t("Attendee Cannot Reschedule") }}</span>
                                        <span v-if="settings.can_not_reschedule.enabled">{{ $t("AdvancedSettings/cannot_reschedule_description") }}</span>
                                        <span v-else>{{ $t("AdvancedSettings/can_reschedule_description") }}</span>
                                    </div>
                                    <div class="card_action">
                                        <el-switch v-model="settings.can_not_reschedule.enabled"/>
                                    </div>
                                </div>
                                <template v-if="settings.can_not_reschedule.enabled">
                                    <div class="fcal_event_child_card">
                                        <el-radio-group v-model="settings.can_not_reschedule.type">
                                            <el-radio label="always"> {{ $t('Always') }}</el-radio>
                                            <el-radio label="conditional">{{ $t('When meeting starts in less than') }}
                                                <span>
                                                    <el-input v-model="settings.can_not_reschedule.condition.value" @input="validateInput(settings.can_not_reschedule)"></el-input>
                                                    <el-select v-model="settings.can_not_reschedule.condition.unit" @change="validateInput(settings.can_not_reschedule)" :placeholder="$t('Select Unit')" popper-class="fcal_select">
                                                        <el-option value="minutes" :label="$t('Minutes')"></el-option>
                                                        <el-option value="hours" :label="$t('Hours')"></el-option>
                                                    </el-select>
                                                </span>
                                            </el-radio>
                                        </el-radio-group>
                                    </div>
                                    <div class="fcal_event_child_card">
                                        <el-form-item :label="$t('Permission Denied Message')">
                                            <popover
                                                :groupTitle="$t('Shortcodes')"
                                                :data="smart_codes.texts"
                                                placement="bottom-end"
                                                :isVisible="reschedulePopupVisible"
                                                class="fcal_popover_shortcode"
                                                @command="handleRescheduleCommand">
                                                <template #popoverButton>
                                                    <el-input
                                                        type="text"
                                                        :placeholder="$t('Sorry! you can not reschedule this')"
                                                        v-model="settings.can_not_reschedule.message">
                                                        <template #append>
                                                            <el-button :icon="MoreIcon" @click="toggleReschedulePopupVisible"></el-button>
                                                        </template>
                                                    </el-input>
                                                </template>
                                            </popover>
                                            <p class="fcal_help_text">{{ $t("AdvancedSettings/cannot_reschedule_message_hint") }}</p>
                                        </el-form-item>
                                    </div>
                                </template>
                            </div>
                        </el-form-item>
    
                        <el-form-item>
                            <div class="fcal_event_card fcal_event_card_wrap">
                                <div class="fcal_event_card_header">
                                    <div class="card_contents">
                                        <span class="sub-label card-title">{{ $t("Landing Page")  }} {{ $t("Settings") }}</span>
                                        <span>{{ $t('AdvancedSettings/slug_setting_description') }}</span>
                                    </div>
                                    <div class="card_action">
                                        <el-button @click="editSlug = !editSlug" class="fcal_plain_btn">
                                            <el-icon><EditPen/></el-icon> {{ $t('Edit') }}
                                        </el-button>
                                    </div>
                                </div>
                                <div class="fcal_event_child_card" v-if="editSlug">
                                    <el-form-item :label="$t('Slug')">
                                        <el-input v-model="calendarEventSlug"/>
                                        <p class="fcal_event_input_hint">{{ $t('AdvancedSettings/slug_setting_hint') }}</p>
                                    </el-form-item>
                                </div>
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
import Popover from "@/Components/Popover";
import { CloseBold, More } from '@element-plus/icons-vue';
import { markRaw } from "vue";

export default {
    name: '_AdvancedSettings',
    components: {
        SaveButton,
        Popover,
        ProNotice
    },
    props: ['calendar_event', 'disabled'],
    data() {
        return {
            saving: false,
            loading: false,
            editSlug: false,
            titlePopupVisible: false,
            urlPopupVisible: false,
            queryPopupVisible: false,
            cancelPopupVisible: false,
            reschedulePopupVisible: false,
            calendarEventSlug: this.calendar_event.slug,
            settings: this.calendar_event.settings,
            CloseBoldIcon: markRaw(CloseBold),
            MoreIcon: markRaw(More),
            smart_codes: {
                texts: {},
                html: {}
            }
        }
    },
    computed: {
        enabledQueryString() {
            return this.settings?.custom_redirect?.is_query_string == 'yes';
        },
        showRequiresConfirmation() {
            return this.calendar_event.event_type == 'single';
        },
        showMultipleBooking () {
            return this.calendar_event.event_type == 'single';
        },
        showReschedulingCondition() {
            const eventType = this.calendar_event.event_type;
            return eventType != 'single_event' && eventType != 'group_event';
        },
        bookingTitle() {
            return this.calendar_event.title + ' ' + this.$t('meeting between') + ' ' + this.calendar_event.author_profile?.name + ' and {{guest.first_name}}';
        }
    },
    methods: {
        toggleTitlePopupVisible() {
            this.titlePopupVisible = !this.titlePopupVisible;
        },
        handleBookingTitleCommand(command) {
            this.settings.booking_title += command;
            this.titlePopupVisible = false;
        },
        toggleCancelPopupVisible() {
            this.cancelPopupVisible = !this.cancelPopupVisible;
        },
        handleCancelCommand(command) {
            this.settings.can_not_cancel.message += command;
            this.cancelPopupVisible = false;
        },
        toggleReschedulePopupVisible() {
            this.reschedulePopupVisible = !this.reschedulePopupVisible;
        },
        handleRescheduleCommand(command) {
            this.settings.can_not_reschedule.message += command;
            this.reschedulePopupVisible = false;
        },
        toggleUrlPopupVisible() {
            this.urlPopupVisible = !this.urlPopupVisible;
        },
        handleRedirectUrlCommand(command) {
            this.settings.custom_redirect.redirect_url += command;
            this.urlPopupVisible = false;
        },
        toggleQueryPopupVisible() {
            this.queryPopupVisible = !this.queryPopupVisible;
        },
        handleRedirectQueryCommand(command) {
            this.settings.custom_redirect.query_string += command;
            this.queryPopupVisible = false;
        },
        checkSlugUpdated(res) {
            if (res.event.slug != this.calendar_event.slug) {
                window.location.reload();
            }
        },
        validateInput(item) {
            const limitValues = {
                minutes: 1000,
                hours: 24
            };
            if (isNaN(item.condition.value) || item.condition.value <= 0) {
                item.condition.value = '';
            } else if (item.condition.value > limitValues[item.condition.unit]) {
                item.condition.value = limitValues[item.condition.unit];
            }
        },
        validateLimit (item) {
            if (isNaN(item.limit) || item.limit <= 0) {
                item.limit = '';
            } else {
                item.limit = Math.min(50, item.limit);
            }
        },
        checkValidation() {
            if (this.settings.custom_redirect?.enabled && !this.calendar_event.settings.custom_redirect?.redirect_url) {
                this.$handleError(this.$t('Redirect URL field is required'));
                return false;
            }
            if (this.enabledQueryString && !this.settings.custom_redirect?.query_string) {
                this.$handleError(this.$t('Redirect Query String field is required'));
                return false;
            }
            return true;
        },
        fetchSettings() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id, {
                calendar_id: this.calendar_event.calendar_id,
                with: ['smart_codes']
            })
                .then(response => {
                    this.smart_codes = response.smart_codes;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        saveSettings() {
            if (!this.checkValidation()) return;
            this.saving = true;
            this.$post('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/advanced-settings', {
                calendar_id: this.calendar_event.calendar_id,
                booking_title: this.settings.booking_title,
                submit_button_text: this.settings.submit_button_text,
                custom_redirect: this.settings.custom_redirect,
                requires_confirmation: this.settings.requires_confirmation,
                multiple_booking: this.settings.multiple_booking,
                can_not_cancel: this.settings.can_not_cancel,
                can_not_reschedule: this.settings.can_not_reschedule,
                slug: this.calendarEventSlug
            })
                .then(response => {
                    this.checkSlugUpdated(response);
                    this.$handleSuccess(response);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        }
    },
    mounted() {
        if (!this.disabled) {
            this.fetchSettings();
        }
    }
}
</script>
