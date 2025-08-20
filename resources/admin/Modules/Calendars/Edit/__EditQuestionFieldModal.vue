<template>
    <el-dialog
        v-model="openModal"
        :title="modalTitle"
        :append-to-body="true"
        class="fcal_dialog fcal_question_dialog">
        <p v-if="fieldData.system_defined">{{ $t('EditQuestionField/system_defined_field_label') }}</p>
        <el-form v-if="openModal" label-position="top" >
            <el-form-item :label="$t('Field Type')">
                <el-select
                    v-model="fieldData.type"
                    filterable
                    popper-class="fcal_select"
                    :disabled="fieldData.system_defined"
                    :placeholder="$t('Select Type')">
                    <el-option
                        v-for="(type, index) in fieldsTypes"
                        :key="index"
                        :label="type.label"
                        :value="type.value"
                    >
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item :label="$t('Label *')">
                <el-input v-model="fieldData.label" type="text" :placeholder="$t('Label')"/>
                <span v-if="isHiddenField">{{ $t('This label will only be visible for admin.') }}</span>
            </el-form-item>
            <template v-if="fieldData.coupon">
                <el-form-item :label="$t('Coupon Label')">
                    <el-input v-model="fieldData.coupon.label" :placeholder="$t('Enter Coupon Label')"/>
                </el-form-item>
                <el-form-item :label="$t('Apply Coupon Button')">
                    <el-input v-model="fieldData.coupon.apply_button" :placeholder="$t('Enter Apply Button Text')"/>
                </el-form-item>
                <el-form-item :label="$t('Coupon Field Placeholder')">
                    <el-input v-model="fieldData.coupon.placeholder" :placeholder="$t('Enter Coupon Field Placeholder')"/>
                </el-form-item>
            </template>
            <el-form-item v-if="isTermsField" :label="$t('Terms & Conditions') + ' *'">
                <wp-editor v-model="fieldData.terms_and_conditions" :height="150" :media_buttons="false"/>
            </el-form-item>
            <el-form-item v-if="fieldData.limit" :label="$t('Maximum Guest Limit') + ' *'">
                <el-input v-model="fieldData.limit" type="number" @change="validateLimit(fieldData.limit)"/>
            </el-form-item>
            <el-form-item v-if="hasPlaceHolder" :label="$t('Placeholder')">
                <el-input v-model="fieldData.placeholder" :placeholder="$t('Placeholder')"/>
            </el-form-item>
            <el-form-item v-show="isOptionRequired" :label="$t('Options *')" class="fcal_question_options">
                <div class="fcal_question_option" v-for="(option, index) in fieldData.options" :key="index">
                    <el-input
                        type="text"
                        class="form-control"
                        :placeholder="`Option ${index + 1}`"
                        v-model=fieldData.options[index]
                    />
                    <el-link v-if="isRemovable" type="danger" :title="$t('Remove')"
                        :icon="CloseBoldIcon"
                        :underline="false"
                        @click="removeOption(index)">
                    </el-link>
                </div>
                <el-link type="primary" :underline="false" @click="addNewOption">
                    {{ '+ ' + $t('Add new option') }}
                </el-link>
            </el-form-item>
            <template v-if="isDateField">
                <el-form-item :label="$t('Minimum Date')">
                    <el-date-picker
                        v-model="fieldData.min_date"
                        type="date"
                        size="small"
                        :default-value="new Date()"
                        value-format="YYYY-MM-DD"
                        :placeholder="$t('Select Date')"
                    />
                    <span>{{ $t('If no date is set, default date 1900-01-01 will be used as the minimum date.') }}</span>
                </el-form-item>
                <el-form-item :label="$t('Maximum Date')">
                    <el-date-picker
                        v-model="fieldData.max_date"
                        type="date"
                        size="small"
                        :default-value="new Date()"
                        value-format="YYYY-MM-DD"
                        :placeholder="$t('Select Date')"
                    />
                    <span>{{ $t('If no date is set, last date of current year will be used as the maximum date.') }}</span>
                </el-form-item>
                <el-form-item :label="$t('Format')">
                    <el-select
                        popper-class="fcal_select"
                        v-model="fieldData.date_format"
                        :clearable="true"
                        :placeholder="$t('Select Format')">
                        <el-option
                            v-for="(type, index) in dateFormats"
                            :key="index"
                            :label="type.label"
                            :value="type.value"
                        >
                        </el-option>
                    </el-select>
                    <span>{{ $t('The default date format will be applied if no format is chosen.') }}</span>
                </el-form-item>
            </template>
            <el-form-item v-if="hasHelpText" :label="$t('Help Message')">
                <el-input v-model="fieldData.help_text" type="text"/>
            </el-form-item>
            <template v-if="isFileField">
                <el-form-item :label="$t('Max File Size')">
                    <el-input
                        type="number"
                        v-model="fieldData.file_size_value"
                        :placeholder="$t('Max File Size')"
                        class="fcal_input_with_select">
                        <template #append>
                            <el-select v-model="fieldData.file_size_unit" placeholder="Select">
                                <el-option label="KB" value="kb"/>
                                <el-option label="MB" value="mb"/>
                            </el-select>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item :label="$t('Max Files Count')">
                    <el-input v-model="fieldData.max_file_allow" type="number" :placeholder="$t('Max Files Count')"/>
                </el-form-item>
                <el-form-item :label="$t('File Type')">
                    <el-checkbox-group v-model="fieldData.allow_file_types">
                        <el-checkbox label="pdf">{{ $t('PDF') }}</el-checkbox>
                        <el-checkbox label="doc">{{ $t('Doc') }}</el-checkbox>
                        <el-checkbox label="zip">{{ $t('Zip') }}</el-checkbox>
                        <el-checkbox label="image">{{ $t('Image') }}</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
            </template>
            <template v-if="isHiddenField">
                <el-form-item :label="$t('Default Value')">
                    <popover
                        :groupTitle="$t('Shortcodes')"
                        :data="smartCodes.texts"
                        placement="bottom-end"
                        :isVisible="defaultValuePopupVisible"
                        class="fcal_popover_shortcode"
                        @command="handleDefaultValueCommand">
                        <template #popoverButton>
                            <el-input
                                type="text"
                                :placeholder="$t('Default Value')"
                                v-model="fieldData.default_value">
                                <template #append>
                                    <el-button :icon="MoreIcon" @click="toggleDefaultValuePopup"></el-button>
                                </template>
                            </el-input>
                        </template>
                    </popover>
                </el-form-item>
                <el-form-item :label="$t('Name Attribute') + ' *'">
                    <el-input v-model="fieldData.name" type="text" :placeholder="$t('Name Attribute')"/>
                    <span>{{$t('The value must be unique.')}}</span>
                </el-form-item>
            </template>
            <el-form-item v-if="isPhoneField">
                <el-checkbox v-model="fieldData.is_sms_number">{{ $t('Use this number for sending sms notification') }}</el-checkbox>
            </el-form-item>
            <el-form-item v-if="!isHiddenField" :label="$t('Required')">
                <el-radio-group :disabled="fieldData.disable_alter" v-model="fieldData.required" class="radio_desc_group radio_required_field">
                    <el-radio :label="true">{{ $t('Yes') }}</el-radio>
                    <el-radio :label="false">{{ $t('No') }}</el-radio>
                </el-radio-group>
            </el-form-item>
        </el-form>
        <template #footer>
            <div class="dialog-footer">
                <el-button class="fcal_plain_btn" @click="openModal = false">
                    {{ $t('Cancel') }}
                </el-button>
                <el-button class="fcal_primary_btn" @click="saveChanges">
                    {{ $t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script>
import { markRaw } from "vue";
import { CloseBold, More } from '@element-plus/icons-vue';
import Popover from "@/Components/Popover";
import wpEditor from '@/Components/FormBuilder/WpEditorField.vue';

export default {
    name: 'EditQuestionFieldModal',
    props: ['field', 'fields', 'smartCodes', 'showModal'],
    emits: ['closeModal', 'updateFieldData'],
    components: {
        Popover,
        wpEditor
    },
    data() {
        return {
            openModal: this.showModal,
            defaultValuePopupVisible: false,
            fieldsTypes: this.appVars.custom_field_types,
            dateFormats: this.appVars.available_date_formats,
            defaultTerms: this.appVars.default_terms,
            defaultOptions: ['Option 1', 'Option 2'],
            CloseBoldIcon: markRaw(CloseBold),
            MoreIcon: markRaw(More),
            isNewEntry: false,
            fieldData: {},
            newField: {
                index: '',
                label: '',
                name: '',
                type: 'text',
                placeholder: '',
                help_text: '',
                enabled: true,
                required: false,
                options: ['Option 1', 'Option 2']
            },
        }
    },
    watch: {
        'openModal': function () {
            this.$emit('closeModal');
        },
        'field.type': function() {
            if (this.fieldData.type == 'checkbox') {
                this.fieldData.required = false;
            }
            if (this.fieldData.type == 'file') {
                this.maybeSetDefaultFileValues();
            }
            if (this.fieldData.type == 'terms-and-conditions') {
                this.maybeSetDefaultTerms();
            }
            if (this.isOptionRequired && !this.fieldData.options) {
                this.fieldData.options = this.defaultOptions;
            } else {
                this.fieldData.options = {};
            }
        },
        'fieldData.type': function() {
            if (this.fieldData.type == 'file') {
                this.maybeSetDefaultFileValues();
            }
            if (this.fieldData.type == 'terms-and-conditions') {
                this.maybeSetDefaultTerms();
            }
        },
        'fieldData.is_sms_number': function (newValue, oldValue) {
            if (newValue && newValue != oldValue) {
                this.updateOtherSmsFields();
            }
        }
    },
    computed: {
        modalTitle() {
            return this.isNewEntry ? this.$t('Add Question') : this.$t('Update Question');
        },
        isOptionRequired() {
            if (this.fieldData.name == 'location') {
                return false;
            }
            return ['dropdown', 'multi-select', 'radio', 'checkbox-group'].includes(this.fieldData.type);
        },
        isRemovable() {
            return this.fieldData.options.length > 2;
        },
        isPhoneField() {
            return this.fieldData.type == 'phone';
        },
        isDateField() {
            return this.fieldData.type == 'date';
        },
        isFileField() {
            return this.fieldData.type == 'file';
        },
        isHiddenField() {
            return this.fieldData.type == 'hidden';
        },
        isTermsField() {
            return this.fieldData.type == 'terms-and-conditions';
        },
        hasPlaceHolder() {
            return ['text', 'textarea', 'message', 'number', 'email', 'date'].includes(this.fieldData.type);
        },
        hasHelpText() {
            return !['guests', 'payment_method', 'location'].includes(this.fieldData.name) && !this.isHiddenField;
        }
    },
    methods: {
        saveChanges() {
            if (!this.fieldData.label) {
                this.$handleError(this.$t('Label field is required'));
                return;
            }
            this.$emit('updateFieldData', this.fieldData, this.isNewEntry);
            this.openModal = false;
        },
        addNewOption() {
            const index = this.fieldData.options.length + 1;
            this.fieldData.options = [...this.fieldData.options, `Option ${index}`];
        },
        removeOption(index) {
            this.fieldData.options.splice(index, 1);
        },
        toggleDefaultValuePopup() {
            this.defaultValuePopupVisible = !this.defaultValuePopupVisible;
        },
        handleDefaultValueCommand(command) {
            this.fieldData.default_value = this.fieldData.default_value || '';
            this.fieldData.default_value += command;
            this.defaultValuePopupVisible = false;
        },
        getFieldIndex() {
            let index = 0;
            this.fields.forEach(field => {
                if (field.index > index) {
                    index = field.index;
                }
            });
            return index + 1;
        },
        updateOtherSmsFields() {
            if (this.fieldData.type != 'phone' || !this.fieldData.is_sms_number) {
                return;
            }
            this.fields.forEach(field => {
                if (field.is_sms_number && field.name != this.fieldData.name) {
                    field.is_sms_number = false;
                }
            });
        },
        validateLimit(limit) {
            this.fieldData.limit = Math.max(1, Math.min(50, limit));
        },
        maybeSetDefaultFileValues() {
            this.fieldData.file_size_unit = this.fieldData.file_size_unit || 'mb';
            this.fieldData.file_size_value = this.fieldData.file_size_value || 1;
            this.fieldData.max_file_allow = this.fieldData.max_file_allow || 1;
            this.fieldData.allow_file_types = this.fieldData.allow_file_types || ['pdf'];
        },
        maybeSetDefaultTerms() {
            this.fieldData.label = this.fieldData.label || 'Terms and Conditions';
            this.fieldData.terms_and_conditions = this.fieldData.terms_and_conditions || this.defaultTerms;
        }
    },
    mounted() {
        if (!this.field) {
            this.fieldData = this.newField;
            this.newField.index = this.getFieldIndex();
            this.isNewEntry = true;
        } else {
            this.fieldData = this.field;
            if (!this.fieldData.index) {
                this.fieldData.index = this.getFieldIndex();
            }
        }
    }
}
</script>
