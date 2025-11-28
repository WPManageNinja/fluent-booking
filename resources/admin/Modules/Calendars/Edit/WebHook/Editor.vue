<template>
    <el-form v-model="settings" label-position="top" class="fcal_webhook_form">
        <el-row :gutter="24">
            <el-col :sm="24" :md="12">
                <!--Name-->
                <el-form-item :label="$t('Name')" required>
                    <el-input v-model="settings.name" :placeholder="$t('WebHook Feed Name')"></el-input>
                </el-form-item>
            </el-col>
            <el-col :sm="24" :md="12">
                <!--Request URL-->
                <el-form-item :label="$t('Request URL')" required>
                    <el-input v-model="settings.request_url" type="url" :placeholder="$t('WebHook URL')"></el-input>
                </el-form-item>
            </el-col>
        </el-row>

        <el-row :gutter="24">
            <el-col :sm="24" :md="12">
                <!--Request Method-->
                <el-form-item :label="$t('Request Method')">
                    <el-select v-model="settings.request_method" popper-class="fcal_select">
                        <el-option
                            v-for="method in request_methods"
                            :value="method"
                            :label="method"
                            :key="method"
                        ></el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col :sm="24" :md="12">
                <!--Request Format-->
                <el-form-item :label="$t('Request Format')">
                    <el-select v-model="settings.request_format" popper-class="fcal_select">
                        <el-option
                            v-for="format in ['FORM', 'JSON']"
                            :value="format"
                            :label="format"
                            :key="format"
                        ></el-option>
                    </el-select>
                </el-form-item>
            </el-col>
        </el-row>


        <!--Request Header-->
        <el-form-item :label="$t('Request Header')">
            <el-radio-group v-model="settings.with_header">
                <el-radio label="nop">{{ $t('No Headers') }}</el-radio>
                <el-radio label="yup">{{ $t('With Headers') }}</el-radio>
            </el-radio-group>
        </el-form-item>

        <!--Request Headers-->
        <el-form-item required v-if="settings.with_header=='yup'" :label="$t('Request Headers')">
            <table class="fcal_webhook_request_header_table" width="100%">
                <thead>
                    <tr>
                        <th class="text-left" width="50%">
                            <span class="lead-title mb-2">{{ $t('Header Name') }}</span>
                        </th>
                        <th class="text-left" width="50%">
                            <span class="lead-title mb-2">{{ $t('Header Value') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <tr v-for="(headerValue, headerKey) in settings.request_headers" :key="headerKey">
                    <td>
                        <el-input type="text" :placeholder="$t('Header Key')" v-model="settings.request_headers[headerKey].key"></el-input>
                    </td>
                    <td>
                        <div class="right-field">
                            <el-input
                                :placeholder="$t('Enter Value')"
                                clearable
                                v-model="settings.request_headers[headerKey].value">
                            </el-input>
                            <div class="action-btn">
                                <el-button class="fcal_plain_btn" @click="addHeaderRow(headerKey)">
                                    <el-icon><Plus /></el-icon>
                                </el-button>
                                <el-button
                                    class="fcal_plain_btn danger"
                                    v-if="settings.request_headers.length > 1"
                                    @click="removeHeaderRow(headerKey)">
                                    <el-icon><Minus /></el-icon>
                                </el-button>
                            </div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </el-form-item>

        <!--Request Body-->
        <el-form-item required :label="$t('Request Body')">
            <el-radio-group v-model="settings.request_body">
                <el-radio label="all_data">{{ $t('All Data') }}</el-radio>
                <el-radio label="selected_fields">{{ $t('Selected Fields') }}</el-radio>
            </el-radio-group>
        </el-form-item>

        <!--Request Fields-->
        <el-form-item required v-if="settings.request_body=='selected_fields'" :label="$t('Request Fields')">
            <table class="fcal_webhook_request_header_table" width="100%">
                <thead>
                    <tr>
                        <th class="text-left" width="50%">
                            <span class="lead-title mb-2">{{ $t('Field Name') }}</span>
                        </th>
                        <th class="text-left" width="50%">
                            <span class="lead-title mb-2">{{ $t('Field Value') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <tr v-for="(mappedField, mappedKey) in settings.fields" :key="mappedKey">
                    <td>
                        <el-input
                            clearable
                            v-model="settings.fields[mappedKey].key"
                            :placeholder="$t('Enter Name')"></el-input>
                    </td>
                    <td>
                        <div class="right-field">
                            <el-select
                                filterable
                                allow-create
                                v-model="settings.fields[mappedKey].value"
                                :placeholder="$t('Select Value')"
                                popper-class="fcal_select"
                            >
                                <el-option-group v-for="(group, groupKey) in smart_codes.texts"
                                                 :key="groupKey"
                                                 :label="group.title">
                                    <el-option
                                        v-for="(item, itemName) in group.shortcodes"
                                        :value="itemName"
                                        :label="item"
                                    ></el-option>
                                </el-option-group>

                            </el-select>

                            <div class="action-btn">
                                <el-button
                                    class="fcal_plain_btn" @click="addFieldRow(mappedKey)">
                                    <el-icon><Plus /></el-icon>
                                </el-button>
                                <el-button
                                    class="fcal_plain_btn danger"
                                    v-if="settings.fields.length > 1"
                                   @click="removeFieldRow(mappedKey)"
                                >
                                    <el-icon><Minus /></el-icon>
                                </el-button>
                            </div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </el-form-item>


        <!--Request Body-->
        <el-form-item required :label="$t('Event Triggers')">
            <el-checkbox-group v-model="settings.event_triggers">
                <el-checkbox v-for="trigger in event_triggers" :key="trigger.value" :label="trigger.value">
                    {{ trigger.label }}
                </el-checkbox>
            </el-checkbox-group>
        </el-form-item>

        <el-form-item>
            <el-checkbox v-model="settings.enabled">{{ $t('Enable this webhook feed') }}</el-checkbox>
        </el-form-item>

        <div class="fcal_webhook_form_footer">
            <el-button @click="saveWebHook" class="fcal_primary_btn">
                <el-icon><SuccessFilled /></el-icon> {{ $t('Save Feed') }}
            </el-button>
        </div>
    </el-form>
</template>

<script type="text/babel">
import { Plus, Minus, SuccessFilled } from '@element-plus/icons-vue';
import Popover from '../../../../Components/Popover';

export default {
    name: "Editor",
    props: {
        editing_feed: {
            default() {
                return null;
            }
        },
        event_triggers: {
            type: Array,
            required: true
        },
        calendar_event: {
            type: Object,
            required: true
        },
        smart_codes: {
            type: Object,
            default() {
                return {
                    texts: {},
                    html: {}
                };
            }
        }
    },
    components: {
        Plus,
        Minus,
        Popover,
        SuccessFilled
    },
    data() {
        return  {
            settings: this.editing_feed.settings,
            request_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            saving: false,
            webhook_id: null
        }
    },
    methods: {
        saveWebHook() {
            this.saving = true;
            this.$post(`calendars/${this.calendar_event.calendar_id}/events/${this.calendar_event.id}/webhooks`, {
                webhook: {
                    settings: this.settings,
                    id: this.editing_feed.id
                },
                calendar_id: this.calendar_event.calendar_id
            })
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.$emit('backToWebhook');
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false
                });
        },
        hideCustomHeaderValueInput(headerKey) {
            console.log(headerKey);
            this.editing_item.custom_header_values.splice(headerKey, 1, false);
            this.editing_item.request_headers[headerKey].value = null;
        },
        addCustomHeaderValueInput(headerKey, val) {
            if (val == '__webhook_custom_header_value__') {
                this.editing_item.custom_header_values[headerKey] = true;
                this.editing_item.request_headers[headerKey].value = null;
            }
        },
        addHeaderRow(headerKey) {
            let index = headerKey + 1;
            this.settings.request_headers.splice(index, 0, {
                key: null,
                value: null
            });
        },
        removeHeaderRow(headerKey) {
            this.settings.request_headers.splice(headerKey, 1);
        },
        addFieldRow(mapIndex) {
            let index = mapIndex + 1;
            this.settings.fields.splice(index, 0, {
                key: null,
                value: null
            });
        },
        removeFieldRow(mapIndex) {
            this.settings.fields.splice(mapIndex, 1);
        },
    }
}
</script>
