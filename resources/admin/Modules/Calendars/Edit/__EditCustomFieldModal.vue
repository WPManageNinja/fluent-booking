<template>
    <el-dialog
        v-model="openModal"
        title="Update Question"
        :append-to-body="true"
        class="fcal_dialog">
        <el-form v-if="openModal" label-position="top" >
            <el-form-item label="Location">
                <el-select
                    popper-class="fcal_select"
                    v-model="field.type"
                    :disabled="isMandatoryField"
                    placeholder="Select Type">
                    <el-option
                        v-for="(type, index) in fieldsTypes"
                        :key="index"
                        :label="type.label"
                        :value="type.value"
                    >
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="Label">
                <el-input v-model="field.label" type="text" placeholder="Label" />
            </el-form-item>
            <el-form-item label="Placeholder">
                <el-input v-model="field.placeholder" type="textarea" placeholder="Placeholder" />
            </el-form-item>
            <el-form-item v-show="isOptionRequired" label="Options" class="fcal_question_options">
                <div class="fcal_question_option" v-for="(option, index) in field.options" :key="index">
                    <el-input
                        type="text"
                        class="form-control"
                        :placeholder="`Option ${index + 1}`"
                        v-model=field.options[index]
                    />
                    <el-link v-if="isRemovable" type="danger" title="Remove"
                        :icon="CloseBoldIcon"
                        :underline="false"
                        @click="removeOption(index)">
                    </el-link>
                </div>
                <el-link type="primary" :underline="false" @click="addNewOption">
                    + Add new option
                </el-link>
            </el-form-item>
            <el-form-item label="Required">
                <el-radio-group v-model="field.required" class="ml-4">
                    <el-radio label="yes">Yes</el-radio>
                    <el-radio label="no">No</el-radio>
                </el-radio-group>
            </el-form-item>
        </el-form>
        <template #footer>
            <div class="dialog-footer">
                <el-button class="fcal_plain_btn" @click="openModal = false">
                    Cancel
                </el-button>
                <el-button class="fcal_primary_btn" @click="updateFieldSettings">
                    Update
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

 <script>
 import { markRaw } from "vue";
import { CloseBold } from '@element-plus/icons-vue';
export default {
    name: 'EditCustomFieldModal',
    props: ['field', 'fields', 'phoneRequired', 'showModal'],
    emits: ['closeModal', 'updatedFieldData'],
    data() {
        return {
            openModal: this.showModal,
            fieldsTypes: this.appVars.custom_field_types,
            defaultOptions: ['Option 1', 'Option 2'],
            CloseBoldIcon: markRaw(CloseBold),
        }
    },
    watch: {
        'openModal': function () {
            this.$emit('closeModal');
        },
        'field.type': function() {
            if (this.isOptionRequired && !this.field.options) {
                this.field.options = this.defaultOptions;
            } else {
                this.field.options = {};
            }
        }
    },
    computed: {
        isOptionRequired() {
            return ['checkbox', 'dropdown', 'multi_select_checkbox'].includes(this.field.type);
        },
        isRemovable() {
            return this.field.options.length > 2;
        },
        isMandatoryField() {
            const allowedFields = ['name', 'email'];
            if (this.phoneRequired) {
                allowedFields.push('phone');
            }
            return allowedFields.includes(this.field.name);
        },
    },
    methods: {
        updateFieldSettings() {
            this.updateFieldName();
            this.$emit('updatedFieldData', this.field);
            this.openModal = false;
        },
        updateFieldName() {
            let fieldName = this.field.type;
            let totalMatch = this.fields.filter(field => field.name.startsWith(fieldName)).length;
            if (totalMatch) {
                fieldName = `${fieldName}_${totalMatch}`;
            }
            this.field.name = fieldName;
        },
        addNewOption() {
            const index = this.field.options.length + 1;
            this.field.options = [...this.field.options, `Option ${index}`];
        },
        removeOption(index) {
            this.field.options.splice(index, 1);
        }
    }
}
</script>
