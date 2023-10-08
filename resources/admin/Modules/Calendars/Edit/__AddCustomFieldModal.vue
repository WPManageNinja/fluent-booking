<template>
    <el-dialog
        v-model="openModal"
        title="Add New Question"
        :append-to-body="true"
        class="fcal_dialog">
        <el-form v-if="openModal" label-position="top" >
            <el-form-item label="Location">
                <el-select
                    popper-class="fcal_select"
                    v-model="newField.type"
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
                <el-input v-model="newField.label" type="text" placeholder="Label" />
            </el-form-item>
            <el-form-item label="Placeholder">
                <el-input v-model="newField.placeholder" type="textarea" placeholder="Placeholder" />
            </el-form-item>
            <el-form-item v-if="isOptionRequired" label="Options" class="fcal_question_options">
                <div class="fcal_question_option" v-for="(option, index) in newField.options" :key="index">
                    <el-input
                        type="text"
                        class="form-control"
                        :placeholder="`Option ${index + 1}`"
                        v-model=newField.options[index]
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
                <el-radio-group v-model="newField.required" class="ml-4">
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
                <el-button class="fcal_primary_btn" @click="addNewField">
                    Save
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

 <script>
 import { markRaw } from "vue";
import { CloseBold } from '@element-plus/icons-vue';
export default {
    name: 'AddCustomFieldModal',
    props: ['fields', 'showModal'],
    emits: ['closeModal', 'newFieldData'],
    data() {
        return {
            openModal: this.showModal,
            newField: {
                index: this.fields.length+1,
                label: '',
                name: '',
                type: 'checkbox',
                placeholder: '',
                status: true,
                required: false,
                options: ['Option 1', 'Option 2']
            },
            fieldsTypes: this.appVars.custom_field_types,
            CloseBoldIcon: markRaw(CloseBold),
        }
    },
    watch: {
        openModal() {
            this.$emit('closeModal');
        } 
    },
    computed: {
        isOptionRequired() {
            return ['checkbox', 'dropdown', 'multi_select_checkbox'].includes(this.newField.type);
        },
        isRemovable() {
            return this.newField.options.length > 2;
        }
    },
    methods: {
        addNewField() {
            this.updateFieldName();
            this.$emit('newFieldData', this.newField);
            this.openModal = false;
        },
        updateFieldName() {
            let fieldName = this.newField.type;
            let totalMatch = this.fields.filter(field => field.name.startsWith(fieldName)).length;
            if (totalMatch) {
                fieldName = `${fieldName}_${totalMatch}`;
            }
            this.newField.name = fieldName;
        },
        addNewOption() {
            const index = this.newField.options.length + 1;
            this.newField.options = [...this.newField.options, `Option ${index}`];
        },
        removeOption(index) {
            this.newField.options.splice(index, 1);
        }
    }
}
</script>
