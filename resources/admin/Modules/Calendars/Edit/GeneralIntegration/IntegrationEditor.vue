<template>
    <div class="fcal_edit_integration">
        <h3 class="fcal_title">{{ title }}</h3>

        <el-form v-if="!loading_app" label-position="top" class="fcal_webhook_form">
            <template v-for="(field, fieldIndex) in settings_fields?.fields">
                <el-form-item
                    :class="'fcal_field_' + field.component"
                    class="fcal-form-item"
                    v-if="(field.require_list && merge_fields) || !field.require_list"
                    :required="field.required"
                    :key="fieldIndex"
                >
                    <template #label>
                        {{ field.label }}
                        <el-tooltip v-if="field.tips" class="item" popper-class="ff_tooltip_wrap"
                                    placement="top-start">
                            <template #content>
                                <div>
                                    <p v-html="field.tips"></p>
                                </div>
                            </template>
                            <el-icon>
                                <InfoFilled/>
                            </el-icon>
                        </el-tooltip>
                    </template>

                    <template v-if="field.component == 'text'">
                        <el-input :placeholder="field.placeholder" v-model="settings[field.key]"></el-input>
                    </template>

                    <template v-else-if="field.component == 'list_ajax_options'">
                        <el-select
                            class="w-100"
                            v-loading="loading_list"
                            @change="loadMergeFields()"
                            v-model="settings.list_id"
                            :placeholder="field.placeholder"
                            popper-class="fcal_select"
                            :no-match-text="$t('No Data match')"
                            :no-data-text="$t('No Data')"
                        >
                            <el-option
                                v-for="(list_name, list_key) in field.options"
                                :key="list_key"
                                :value="list_key"
                                :label="list_name"
                            ></el-option>
                        </el-select>
                    </template>

                    <template v-else-if="field.component == 'refresh'">
                        <el-select
                            class="w-100"
                            v-loading="loading_list"
                            @change="refresh()"
                            v-model="settings.list_id"
                            :placeholder="field.placeholder"
                            popper-class="fcal_select"
                            :no-match-text="$t('No Data match')"
                            :no-data-text="$t('No Data')"
                        >
                            <el-option
                                v-for="(list_name, list_key) in field.options"
                                :key="list_key"
                                :value="list_key"
                                :label="list_name"
                            ></el-option>
                        </el-select>
                    </template>

                    <template v-else-if="field.component == 'select'">
                        <el-select
                            class="w-100"
                            filterable
                            clearable
                            :multiple="field.is_multiple"
                            v-model="settings[field.key]"
                            :placeholder="field.placeholder"
                            popper-class="fcal_select"
                            :no-match-text="$t('No Data match')"
                            :no-data-text="$t('No Data')"
                        >
                            <el-option
                                v-for="(list_name, list_key) in field.options"
                                :key="list_key"
                                :value="list_key"
                                :label="list_name"
                            ></el-option>
                        </el-select>
                    </template>

                    <template v-else-if="field.component == 'map_fields'">
                        <merge-field-mapper
                            :errors="errors"
                            :inputs="inputs"
                            :field="field"
                            :settings="settings"
                            :editorShortcodes="editorShortcodes"
                            :merge_model="getMergeModel(settings[field.key])"
                            :merge_fields="merge_fields"/>
                    </template>

                    <template v-else-if="field.component == 'checkbox-single'">
                        <el-checkbox v-model="settings[field.key]">
                            {{ field.checkbox_label }}
                        </el-checkbox>
                    </template>

                    <template v-else-if="field.component == 'checkbox-multiple'">
                        <el-checkbox-group v-model="settings[field.key]">
                            <el-checkbox
                                v-for="(fieldValue, i) in field.options"
                                :key="i"
                                :label="Number(i)"
                            >{{ fieldValue }}
                            </el-checkbox>
                        </el-checkbox-group>
                    </template>

                    <template v-else-if="field.component == 'checkbox-multiple-text'">
                        <el-checkbox-group v-model="settings[field.key]">
                            <el-checkbox
                                v-for="(fieldValue, i) in field.options"
                                :key="i"
                                :label="i"
                            >{{ fieldValue }}
                            </el-checkbox>
                        </el-checkbox-group>
                    </template>

                    <template v-else-if="field.component == 'conditional_block'">
                        <filter-fields
                            :fields="inputs"
                            :conditionals="settings[field.key]"
                            :hasPro="has_pro"/>
                    </template>

                    <template v-else-if="field.component == 'value_text'">
                        <filed-general
                            :editorShortcodes="editorShortcodes"
                            v-model="settings[field.key]"
                        />
                    </template>

                    <template v-else-if="field.component == 'value_textarea'">
                        <filed-general
                            field_type="textarea"
                            :editorShortcodes="editorShortcodes"
                            v-model="settings[field.key]"
                        />
                    </template>

                    <template v-else-if="field.component == 'list_select_filter'">
                        <list-select-filter :settings="settings" :field="field"/>
                    </template>

                    <template v-else-if="field.component == 'dropdown_label_repeater'">
                        <drop-down-label-repeater
                            :errors="errors"
                            :inputs="inputs"
                            :field="field"
                            :settings="settings"
                            :editorShortcodes="editorShortcodes"
                        />
                    </template>

                    <template v-else-if="field.component == 'dropdown_many_fields'">
                        <drop-down-many-fields
                            :errors="errors"
                            :inputs="inputs"
                            :field="field"
                            :settings="settings"
                            :editorShortcodes="editorShortcodes"
                        />
                    </template>

                    <template v-else-if="field.component == 'radio_choice'">
                        <el-radio-group v-model="settings[field.key]">
                            <el-radio
                                v-for="(fieldLabel, fieldValue) in field.options"
                                :key="fieldValue"
                                :label="fieldValue"
                            >{{ fieldLabel }}
                            </el-radio>
                        </el-radio-group>
                    </template>

                    <template v-else-if="field.component == 'number'">
                        <el-input-number v-model="settings[field.key]"></el-input-number>
                    </template>

                    <template v-else-if="field.component == 'chained_fields'">
                        <chained-fields
                            select_class="flex-grow-1"
                            :settings="settings"
                            v-model="settings[field.key]"
                            :field="field"
                        ></chained-fields>
                    </template>

                    <div class="ff_chained_ajax_field" v-else-if="field.component == 'chained-ajax-fields'">
                        <template v-for="(optionValue, optionKey) in field.options_labels" :key="optionKey">
                            <el-select
                                v-loading="loading_list"
                                @change="chainedAjax(optionKey)"
                                v-model="settings.chained_config[optionKey]"
                                :placeholder="optionValue.placeholder"
                                :no-match-text="$t('No Data match')"
                                :no-data-text="$t('No Data')"
                            >
                                <el-option
                                    v-for="(list_name, list_key) in optionValue.options"
                                    :key="list_key"
                                    :value="list_key"
                                    :label="list_name"
                                ></el-option>
                            </el-select>
                        </template>
                    </div>

                    <template v-else-if="field.component == 'chained_select'">
                        <chained-selects
                            :editingIntegration="editingIntegration"
                            :calendarEvent="calendar_event"
                            :settings="settings"
                            :field="field"
                            v-model="settings[field.key]"
                        ></chained-selects>
                    </template>

                    <template v-else-if="field.component == 'html_info'">
                        <div v-html="field.html_info"></div>
                    </template>

                    <template v-else-if="field.component == 'selection_routing'">
                        <selection-routing
                            :inputs="inputs"
                            :field="field"
                            :editorShortcodes="editorShortcodes"
                            :settings="settings"/>
                    </template>

                    <template v-else-if="field.component == 'datetime'">
                        <el-date-picker
                            v-model="settings[field.key]"
                            type="datetime"
                            format="yyyy/MM/dd HH:mm:ss"
                            :placeholder="field.placeholder"
                            v-on:change="handleChange($event, field.key)"
                        >
                        </el-date-picker>
                    </template>

                    <template v-else-if="field.component == 'wp_editor'">
                        <wp_editor
                            :editorShortcodes="editorShortcodes"
                            :height="field.height || 200"
                            v-model="settings[field.key]">
                        </wp_editor>
                    </template>

                    <template v-else>
                        <p>{{ $t('No Template found. Please make sure you are using latest version of Fluent Forms') }}</p>
                        <pre>{{ field.component }}</pre>
                        <pre>{{ field }}</pre>
                    </template>

                    <p class="mt-1 fs-14" v-if="field.inline_tip" v-html="field.inline_tip"></p>
                    <error-view :field="field.key" :errors="errors"></error-view>

                </el-form-item>
            </template>

            <template v-if="maybeShowSaveButton">
                <div class="fcal_integration_save_btn">
                    <el-button
                        type="primary"
                        :loading="saving"
                        class="fcal_primary_btn"
                        @click="saveNotification"
                        >
                        <el-icon><SuccessFilled /></el-icon> {{ $t('Save Feed') }}
                    </el-button>
                </div>
            </template>
        </el-form>
        <el-skeleton v-else animated :rows="6"></el-skeleton>
    </div>
</template>

<script type="text/babel">
import Errors from "@common/Errors";
import ErrorView from '@common/ErrorView.vue';
import inputPopover from '@/Components/Common/InputPopover.vue';
import FilterFields from '@/Components/Common/FilterFields.vue';
import MergeFieldMapper from './_field_maps.vue';
import FiledGeneral from './_FieldGeneral.vue';
import ListSelectFilter from './_ListSelectFilter.vue';
import DropDownLabelRepeater from './_DropdownLabelRepeater.vue';
import DropDownManyFields from './_DropdownManyFields.vue';
import SelectionRouting from "./_SelectionRouting.vue";
import ChainedSelects from './_ChainedSelects.vue';
import ChainedFields from "./_ChainedFields.vue";

import BtnGroup from '@/Components/Common/BtnGroup/BtnGroup.vue';
import BtnGroupItem from '@/Components/Common/BtnGroup/BtnGroupItem.vue';

import Notice from '@/Components/Notice/Notice.vue';
import wpEditor from '@/Components/FormBuilder/WpEditorField.vue';
import { SuccessFilled } from '@element-plus/icons-vue';

export default {
    name: 'general_notification_edit',
    components: {
        ErrorView,
        inputPopover,
        FilterFields,
        MergeFieldMapper,
        FiledGeneral,
        ListSelectFilter,
        DropDownLabelRepeater,
        DropDownManyFields,
        SelectionRouting,
        ChainedSelects,
        ChainedFields,
        BtnGroup,
        BtnGroupItem,
        Notice,
        'wp_editor': wpEditor,
        SuccessFilled
    },
    props: ['editingIntegration', 'calendar_event', 'inputs', 'has_pro', 'smart_codes'],
    data() {
        return {
            loading_app: false,
            loading_list: false,
            errors: new Errors(),
            saving: false,
            merge_fields: false,
            settings: {},
            settings_fields: {},
            attachedForms: [],
            fromChainedAjax: false,
            refreshQuery: null,
            editorShortcodes: this.smart_codes.texts
        }
    },
    computed: {
        title() {
            let integrationName = this.settings_fields?.integration_title || '';
            if (this.editingIntegration.integration_id) {
                return `${this.$t('Update')} ${integrationName} ${this.$t('Integration Feed')}`;
            } else {
                return `${this.$t('Add New')} ${integrationName} ${this.$t('Integration Feed')}`;
            }
        },
        maybeShowSaveButton() {
            let fields = this.settings_fields;
            let mergeFields = this.merge_fields;
            return (fields?.button_require_list && mergeFields) || !fields?.button_require_list;
        }
    },
    methods: {
        loadIntegrationSettings() {
            this.loading_app = true;
            let data = {
                integration_id: this.editingIntegration.integration_id,
                integration_name: this.editingIntegration.integration_name,
            };

            // add chained ajax configs query
            if (this.fromChainedAjax) {
                data = {...data, configs: this.settings.chained_config}
            }

            if (this.refreshQuery) {
                data = {...data, ...this.refreshQuery}
            }

            data = {...data, calendar_id: this.calendar_event.calendar_id};

            const url = 'calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/integrations/' + this.editingIntegration.integration_id;

            this.$get(url, data)
                .then(response => {
                    this.settings_fields = response.settings_fields;
                    this.settings = response.settings;
                    if (!this.settings.name) {
                        this.settings.name = response.settings_fields.integration_title + ' '+this.$t('Integration Feed') || '';
                    }
                    this.merge_fields = response.merge_fields;
                })
                .catch(error => {
                    // when failed show default field if available
                    if (this.fromChainedAjax && error.data?.settings_fields) {
                        this.settings_fields = error.data.settings_fields;
                    }
                    this.$handleError(error || this.$t('Error on integration settings'));
                })
                .finally(() => {
                    this.loading_app = false;
                });
        },
        refresh() {
            this.refreshQuery = {
                serviceName: this.settings['name'],
                serviceId: this.settings['list_id']
            };
            this.loadIntegrationSettings();
        },
        chainedAjax(key) {
            for (const key in this.settings.chained_config) {
                if (this.settings.chained_config[key] == '') {
                    return;
                }
            }
            if (key == 'base_id') {
                this.settings.chained_config['table_id'] = '';
            }
            this.fromChainedAjax = true;
            this.loadIntegrationSettings();
        },
        loadMergeFields() {
            this.loading_list = true;
            const url = 'calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/integrations/' + this.editingIntegration.integration_id + '/merge-fields';
            this.$get(url, {
                calendar_id: this.calendar_event.calendar_id,
                list_id: this.settings.list_id,
                integration_name: this.editingIntegration.integration_name
            })
                .then(response => {
                    const result = response?.merge_fields || response?.data?.merge_fields
                    this.merge_fields = result
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading_list = false;
                });
        },
        saveNotification() {
            this.errors.clear();
            this.saving = true;
            let data = {
                calendar_id: this.calendar_event.calendar_id,
                integration_name: this.editingIntegration.integration_name,
                integration: JSON.stringify(this.settings),
                data_type: 'stringify',
            };

            const url = 'calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/integrations/' + this.editingIntegration.integration_id;
            this.$post(url, data)
                .then(response => {
                    this.$handleSuccess(response);
                    this.$emit('back');
                })
                .catch((error) => {
                    const getError = error?.errors || error?.data?.errors
                    this.errors.record(getError)
                    this.$handleError(error);
                })
                .finally(() => {
                    this.saving = false
                });
        },
        getMergeModel(merge_model) {
            if (Array.isArray(merge_model) || !merge_model) {
                merge_model = {};
            }

            return merge_model;
        }
    },
    mounted() {
        this.loadIntegrationSettings();
    }
}
</script>

