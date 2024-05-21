<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header">
                <h2>
                    <el-icon class="operation"><Operation /></el-icon>
                    {{ $t('Advanced Settings') }}
                </h2>
            </div>
            <div class="fcal_create_calendar_form_body">
                <el-form label-position="top">

                    <el-form-item>
                        <div class="fcal_event_card fcal_event_card_wrap">
                            <div class="card_contents">
                                <span class="sub-label card-title">{{ $t("Redirect after booking") }}</span>
                                <span>{{ $t("EventDetails/redirect_url_description") }}</span>
                            </div>
                            <div class="card_action">
                                <el-switch v-model="settings.custom_redirect.enabled"/>
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
                                                :placeholder="$t('EventDetails/redirect_url_placeholder')"
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
                                        <em>{{ $t('EventDetails/redirect_query_string_hint') }}</em>
                                    </p>
                                </el-form-item>
                            </div>
                        </div>
                    </el-form-item>

                    <el-form-item v-if="showRequiresConfirmation">
                        <div class="fcal_event_card fcal_event_card_wrap">
                            <div class="card_contents">
                                <span class="sub-label card-title">{{ $t("Requires Confirmation") }}</span>
                                <span>{{ $t("LimitSettings/requires_confirmation_description") }}</span>
                            </div>
                            <div class="card_action">
                                <el-switch v-model="settings.requires_confirmation.enabled"/>
                            </div>
                            <div class="fcal_event_child_card" v-if="settings.requires_confirmation.enabled">
                                <el-radio-group v-model="settings.requires_confirmation.type">
                                    <el-radio label="always"> {{ $t('Always') }}</el-radio>
                                    <el-radio label="conditional">{{ $t('When booking notice is less than') }}
                                        <span>
                                            <el-input v-model="settings.requires_confirmation.condition.value"></el-input>
                                            <el-select v-model="settings.requires_confirmation.condition.unit" :placeholder="$t('Select Unit')" popper-class="fcal_select">
                                                <el-option value="minutes" :label="$t('Minutes')"></el-option>
                                                <el-option value="hours" :label="$t('Hours')"></el-option>
                                            </el-select>
                                        </span>
                                    </el-radio>
                                </el-radio-group>
                            </div>
                        </div>
                    </el-form-item>

                    <el-form-item>
                        <div class="fcal_event_card fcal_event_card_wrap">
                            <div class="card_contents">
                                <span class="sub-label card-title">{{ $t("Landing Page")  }} {{ $t("Settings") }}</span>
                                <span>{{ $t('EventDetails/slug_setting_description') }}</span>
                            </div>
                            <div class="card_action">
                                <el-button @click="editSlug = !editSlug" class="fcal_plain_btn">
                                    <el-icon><EditPen/></el-icon> {{ $t('Edit') }}
                                </el-button>
                            </div>
                            <div class="fcal_event_child_card" v-if="editSlug">
                                <el-form-item :label="$t('Slug')">
                                    <el-input v-model="calendarEventSlug"/>
                                    <p class="fcal_event_input_hint">{{ $t('EventDetails/slug_setting_hint') }}</p>
                                </el-form-item>
                            </div>
                        </div>
                    </el-form-item>

                </el-form>
            </div>
            <div class="fcal_create_calendar_form_footer">
                <SaveButton :saving="saving" :label="$t('Save Changes')" @click="saveSettings"/>
            </div>
        </div>
    </div>
</template>

<script>
import SaveButton from "@/Components/Buttons/SaveButton";
import Popover from "@/Components/Popover";
import { CloseBold, Operation, More, EditPen } from '@element-plus/icons-vue';
import { markRaw } from "vue";

export default {
    name: '_AdvancedSettings',
    components: {
        SaveButton,
        CloseBold,
        Operation,
        More,
        EditPen,
        Popover
    },
    props: ['calendar_event'],
    data() {
        return {
            saving: false,
            loading: false,
            editSlug: false,
            urlPopupVisible: false,
            queryPopupVisible: false,
            calendarEventSlug: this.calendar_event.slug,
            settings: this.calendar_event.settings,
            hasWpEditor: !!window.wp.editor,
            editor_id: 'wp_editor_'+ Date.now() + parseInt( Math.random() * 1000 ),
            CloseBoldIcon: markRaw(CloseBold),
            MoreIcon: markRaw(More),
            smart_codes: {
                texts: {},
                html: {}
            },
        }
    },
    computed: {
        showRequiresConfirmation() {
            return this.calendar_event.event_type != 'group';
        },
        enabledQueryString() {
            return (this.settings?.custom_redirect?.is_query_string == 'yes')
        },
        showRequiresConfirmation() {
            return this.calendar_event.event_type != 'group';
        }
    },
    methods: {
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
        changeContentEvent() {
            const content = wp.editor.getContent(this.editor_id);
            this.settings.custom_redirect.redirect_url = content;
        },
        initEditor() {
            wp.editor.remove(this.editor_id);
            const that = this;
            wp.editor.initialize(this.editor_id, {
                mediaButtons: true,
                tinymce: {
                    height : 300,
                    toolbar1: 'formatselect,table,bold,italic,bullist,numlist,link,hr,blockquote,alignleft,aligncenter,alignright,underline,strikethrough,forecolor,removeformat,codeformat,outdent,indent,undo,redo',
                    setup(editor) {
                        editor.on('change', function (ed, l) {
                            that.changeContentEvent();
                        });
                    }
                },
                quicktags: true
            });
            jQuery('#'+this.editor_id).on('change', function(e) {
                that.changeContentEvent();
            });
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
                custom_redirect: this.settings.custom_redirect,
                requires_confirmation: this.settings.requires_confirmation,
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
    }
}
</script>
