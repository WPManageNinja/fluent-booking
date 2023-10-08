<template>
    <el-form label-position="top" class="fcal_webhook_form">

        <el-row :gutter="24">
            <el-col :sm="24" :md="12">
                <!--Name-->
                <el-form-item label="Name" required>
                    <el-input v-model="editing_item.name" placeholder="WebHook Feed Name"></el-input>
                </el-form-item>
            </el-col>
            <el-col :sm="24" :md="12">
                <!--Request URL-->
                <el-form-item label="Request URL" required>
                    <el-input v-model="editing_item.request_url" placeholder="WebHook URL"></el-input>
                </el-form-item>
            </el-col>
        </el-row>

        <el-row :gutter="24">
            <el-col :sm="24" :md="12">
                <!--Request Method-->
                <el-form-item label="Request Method">
                    <el-select v-model="editing_item.request_method" popper-class="fcal_select">
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
                <el-form-item label="Request Format">
                    <el-select v-model="editing_item.request_format" popper-class="fcal_select">
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
        <el-form-item label="Request Header">
            <el-radio-group v-model="editing_item.with_header">
                <el-radio label="nop">No Headers</el-radio>
                <el-radio label="yup">With Headers</el-radio>
            </el-radio-group>
        </el-form-item>

        <!--Request Headers-->
        <el-form-item required v-if="editing_item.with_header=='yup'" label="Request Headers">
            <table class="fcal_webhook_request_header_table" width="100%">
                <thead>
                    <tr>
                        <th class="text-left" width="50%">
                            <span class="lead-title mb-2">Header Name</span>
                        </th>
                        <th class="text-left" width="50%">
                            <span class="lead-title mb-2">Header Value</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <tr v-for="(headerValue, headerKey) in editing_item.request_headers" :key="headerKey">
                    <td>
                        <el-select
                            clearable
                            style="width: 95%"
                            placeholder="Select Header"
                            v-model="editing_item.request_headers[headerKey].key"
                            v-if="!editing_item.custom_header_keys[headerKey]"
                            popper-class="fcal_select"
                            >
                            <el-option
                                v-for="(header, index) in request_headers"
                                :value="header.value"
                                :label="header.label"
                                :key="index"
                            ></el-option>
                        </el-select>

                        <el-input
                            style="width: 95%"
                            placeholder="Enter Custom Header"
                            clearable
                            v-if="editing_item.custom_header_keys[headerKey]"
                            v-model="editing_item.request_headers[headerKey].key">
                        </el-input>
                    </td>
                    <td>
                        <el-input
                            style="width: 84%"
                            placeholder="Enter Value"
                            clearable
                            v-model="editing_item.request_headers[headerKey].value">
<!--                            <template #append>-->
<!--                                <el-button @click="hideCustomHeaderValueInput(headerKey)"><el-icon><Minus /></el-icon></el-button>-->
<!--                            </template>-->
                        </el-input>
                        <div class="action-btns">
                            <el-button @click="addHeaderRow(headerKey)">
                                <el-icon><Plus /></el-icon>
                            </el-button>
                            <el-button
                                v-if="editing_item.request_headers.length > 1"
                                @click="removeHeaderRow(headerKey)">
                                <el-icon><Minus /></el-icon>
                            </el-button>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </el-form-item>

        <!--Request Body-->
        <el-form-item required label="Request Body">
            <el-radio-group v-model="editing_item.request_body">
                <el-radio label="all_data">All Data</el-radio>
                <el-radio label="selected_fields">Selected Fields</el-radio>
            </el-radio-group>
        </el-form-item>

        <!--Request Fields-->
        <el-form-item required v-if="editing_item.request_body=='selected_fields'" label="Request Fields">
            <table class="fcal_webhook_request_header_table" width="100%">
                <thead>
                    <tr>
                        <th class="text-left" width="50%">
                            <span class="lead-title mb-2">Field Name</span>
                        </th>
                        <th class="text-left" width="50%">
                            <span class="lead-title mb-2">Field Value</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <tr v-for="(mappedField, mappedKey) in editing_item.fields" :key="mappedKey">
                    <td>
                        <el-input
                            clearable
                            v-model="editing_item.fields[mappedKey].key"
                            placeholder="Enter Name"></el-input>
                    </td>
                    <td>
                        <div class="right-field">

                            <el-select
                                filterable
                                allow-create
                                v-model="editing_item.fields[mappedKey].value"
                                placeholder="Select Value"
                                popper-class="fcal_select"
                            >
                                    <template v-for="(value, index) in editorShortcodes">
                                        <el-option
                                            v-if="index!='{all_data}'"
                                            :value="index"
                                            :label="value"
                                            :key="index"
                                        ></el-option>
                                    </template>
                            </el-select>

                            <div class="action-btn">
                                <el-button
                                    class="fcal_plain_btn" @click="addFieldRow(mappedKey)">
                                    <el-icon><Plus /></el-icon>
                                </el-button>
                                <el-button
                                    class="fcal_plain_btn danger"
                                    v-if="editing_item.fields.length > 1"
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
        <el-form-item required label="Event Triggers">
            <el-checkbox-group v-model="editing_item.event_triggers">
                <el-checkbox v-for="trigger in event_triggers" :key="trigger.value" :label="trigger.value">
                    {{ trigger.label }}
                </el-checkbox>
            </el-checkbox-group>

        </el-form-item>

        <div class="fcal_webhook_form_footer">
            <el-button @click="saveWebHook" class="fcal_primary_btn">
                Save Feed
            </el-button>
        </div>
    </el-form>
</template>

<script>
import { Plus, Minus } from '@element-plus/icons-vue';
import Popover from '../../../../Components/Popover';

export default {
    name: "Editor",
    props: {
        edit_item: {
            default() {
                return null;
            }
        },
        selected_index: {
            default() {
                return 1;
            }
        },
        setSelectedId: {
            type: Function,
            required: true
        },
        selected_id: {
            default() {
                return 0;
            }
        },
        request_headers: {
            type: Array,
            required: true
        },
        event_triggers: {
            type: Array,
            required: true
        },
        slot_id: {
            type: String,
            required: true
        }
    },
    components: {
        Plus,
        Minus,
        Popover
    },
    data() {
        return  {
            request_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            editing_item: false,
            saving: false,
            webhook_id: null,
            editorShortcodes: this.appVars.editor_shortcodes,
        }
    },
    methods: {
        saveWebHook() {
            this.saving = true;

            let data = {
                slot_id: this.slot_id,
                webhook_id: this.selected_id,
                webhook: this.editing_item
            };

            this.$post('webhooks', data)
                .then(response => {
                    this.setSelectedId(response.webhook_id);
                    this.$handleSuccess(response.message);
                    // this.$success(response.data.message);
                })
                .catch(error => {
                })
                .finally(() => this.saving = false);
        },
        hideCustomHeaderValueInput(headerKey) {
            this.editing_item.custom_header_values.splice(headerKey, 1, false);
            this.editing_item.request_headers[headerKey].value = null;
        },
        addHeaderRow(headerKey) {
            let index = headerKey + 1;
            this.editing_item.request_headers.splice(index, 0, {
                key: null,
                value: null
            });
            this.editing_item.custom_header_keys.splice(index, 0, false);
            this.editing_item.custom_header_values.splice(index, 0, false);

            // this.header_shortcodes[index] = this.cloneheaderShortCodes();
        },
        removeHeaderRow(headerKey) {
            this.editing_item.request_headers.splice(headerKey, 1);
            this.editing_item.custom_header_keys.splice(headerKey, 1);
            this.editing_item.custom_header_values.splice(headerKey, 1);

            // this.header_shortcodes.splice(headerKey, 1);
        },
        addFieldRow(mapIndex) {
            let index = mapIndex + 1;
            this.editing_item.fields.splice(index, 0, {
                key: null,
                value: null
            });
        },
        removeFieldRow(mapIndex) {
            this.editing_item.fields.splice(mapIndex, 1);
        },

        loadApp() {
            if (this.edit_item) {
                this.editing_item = Object.assign({}, this.editing_item, this.edit_item);
                for (let i = 0, l = this.editing_item.request_headers.length; i < l; i++) {
                    // this.header_shortcodes[i] = this.cloneheaderShortCodes();
                    this.addCustomHeaderKeyInput(i, this.editing_item.request_headers[i].key);
                }
            } else {
                // this.header_shortcodes[0] = this.headerShortCodes;
                this.editing_item = {
                    name: '',
                    request_url: '',
                    with_header: 'nop',
                    request_method: 'GET',
                    request_format: 'FORM',
                    request_body: 'all_data',
                    custom_header_keys: [false],
                    custom_header_values: [false],
                    fields: [{key:null, value:null}],
                    request_headers: [{key: null, value: null}],
                    event_triggers: [],
                    enabled: true
                };
            }
        },
        addCustomHeaderKeyInput(headerKey, val) {
            let header;
            if (val == '__webhook_custom_header__') {
                this.editing_item.custom_header_keys[headerKey] = true;
                this.editing_item.request_headers[headerKey].key = null;
            } else if (header = this.request_headers.find(h => h.value == val)) {
                if (header.hasOwnProperty('possible_values')) {
                    // let shortcodes = this.cloneheaderShortCodes();
                    // shortcodes.unshift(header.possible_values);
                    // this.header_shortcodes[headerKey] = shortcodes;
                } else {
                    // this.header_shortcodes[headerKey] = this.cloneheaderShortCodes();
                }
            } else {
                // this.header_shortcodes[headerKey] = this.cloneheaderShortCodes();
            }
        },
    },
    mounted() {
        this.loadApp();
    }
}
</script>

<style scoped>

</style>