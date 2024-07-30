<template>
    <el-dialog
        v-model="openModal"
        :title="modalTitle"
        :append-to-body="true"
        class="fcal_dialog fcal_question_dialog">
        <p v-if="fieldData.system_defined">{{ $t('EditCustomFieldModal/system_defined_field_label') }}</p>
        <el-form v-if="openModal" label-position="top" >
            <el-form-item :label="$t('Field Type')">
                <el-select
                    popper-class="fcal_select"
                    v-model="fieldData.type"
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
            </el-form-item>
            <el-form-item v-if="fieldData.limit" :label="$t('Maximum Guest Limit') + ' *'">
                <el-input v-model="fieldData.limit" type="number" @change="validateLimit(fieldData.limit)"/>
            </el-form-item>
            <el-form-item v-if="hasPlaceHolder" :label="$t('Placeholder')">
                <el-input v-model="fieldData.placeholder" :placeholder="$t('Placeholder')" />
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
            <el-form-item v-if="isDateField" :label="$t('Format')">
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
            <el-form-item v-if="hasHelpText" :label="$t('Help Message')">
                <el-input v-model="fieldData.help_text" type="text"/>
            </el-form-item>
            <el-form-item v-if="isPhoneField">
                <el-checkbox v-model="fieldData.is_sms_number">{{ $t('Use this number for sending sms notification') }}</el-checkbox>
            </el-form-item>
            <el-form-item :label="$t('Required')">
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

 <script type="text/babel">
 import { markRaw } from "vue";
import { CloseBold } from '@element-plus/icons-vue';
export default {
    name: 'EditCustomFieldModal',
    props: ['field', 'fields', 'showModal'],
    emits: ['closeModal', 'updateFieldData'],
    data() {
        return {
            openModal: this.showModal,
            fieldsTypes: this.appVars.custom_field_types,
            dateFormats: this.appVars.available_date_formats,
            defaultOptions: ['Option 1', 'Option 2'],
            CloseBoldIcon: markRaw(CloseBold),
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
            if (this.isOptionRequired && !this.fieldData.options) {
                this.fieldData.options = this.defaultOptions;
            } else {
                this.fieldData.options = {};
            }
            if (this.fieldData.type == 'checkbox') {
                this.fieldData.required = false;
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
        hasPlaceHolder() {
            return ['text', 'textarea', 'message', 'number', 'email', 'date'].includes(this.fieldData.type);
        },
        hasHelpText() {
            return !['guests', 'payment_method', 'location'].includes(this.fieldData.name);
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
