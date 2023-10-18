<template>
    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2><QuestionIcon/> Booking Questions </h2>
        </div>
        <div class="fcal_create_calendar_form_body">
            <div class="fcal_questions_wrapper">
                <el-skeleton animated v-if="loading" />
                <div v-else class="fcal_questions">
                    <div class="fcal_question" v-for="(field, index) in fields" :class="{fcal_field_type_disabled: !field.enabled}" :key="index">
                        <div class="fcal_question_sorting">
                            <el-icon @click="moveUp(index)"><Top /></el-icon>
                            <el-icon @click="moveDown(index)"><Bottom /></el-icon>
                        </div>
                        <div class="fcal_question_card">
                            <div class="fcal_question_content">
                                <h2>{{ field.label }}
                                    <span class="required" title="Required Field" v-if="field.required">Required</span>
                                    <span class="required" v-if="field.system_defined">System</span>
                                    <span class="required" v-if="!field.enabled">Hidden</span>
                                </h2>
                                <p>
                                    <span v-if="field.system_defined">{{ field.name }}</span>
                                    <span v-else>{{ field.type }}</span>
                                </p>
                            </div>
                            <div class="fcal_question_actions">
                                <el-switch v-if="!field.disable_alter" v-model="field.enabled"/>
                                <el-button class="fcal_plain_btn" @click="editField(field)">Edit</el-button>
                                <el-button v-if="!isMandatoryField(field.name)" type="danger" class="fcal_danger_btn" @click="deleteField(field.index)">
                                    <el-icon><Delete /></el-icon>
                                </el-button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="fcal_question_footer">
                    <el-link class="fcal_add_question" :underline="false" @click="addQuestion">
                        + Add more questions for invitees
                    </el-link>
                    <SaveButton :saving="saving" label="Save Changes" @save="saveSettings"/>
                </div>
            </div>
        </div>
        <EditCustomFieldModal 
            v-if="showModal"
            :field="field"
            :fields="fields"
            :showModal="showModal"
            @closeModal="closeModal"
            @updateFieldData="updateFieldData"
        />
    </div>
</template>

<script>
import QuestionIcon from "../../../Components/Icons/QuestionIcon";
import EditCustomFieldModal from "./__EditCustomFieldModal";
import SaveButton from '../../../Components/Buttons/SaveButton.vue';
import { Delete, Bottom, Top } from '@element-plus/icons-vue';

export default {
    name: 'QuestionSettings',
    props: ['slot', 'activeTab'],
    components: {
        QuestionIcon,
        EditCustomFieldModal,
        SaveButton,
        Bottom,
        Top
    },
    data() {
        return {
            loading: false,
            saving: false,
            field: '',
            fields : [],
            showModal: false
        }
    },
    watch: {
        activeTab() {
            this.fetchFields();
        },
    },
    methods: {
        addQuestion() {
            this.showModal = true;
        },
        editField(field) {
            this.field = field;
            this.showModal = true;
        },
        deleteField(index) {
            this.fields = this.fields.filter((field) => field.index !== index);
            this.saveSettings();
        },
        addNewField(field) {
            this.fields.push(field);
            this.saveSettings();
        },
        updateFieldData(field, newField) {
            if (newField) {
                this.addNewField(field);
            } else {
                this.updateField(field);
            }
        },
        updateField(updatedField) {
            this.fields = this.fields.map(field => {
                if (field.index === updatedField.index) {
                    return updatedField;
                }
                return field;
            });
            this.saveSettings();
        },
        closeModal() {
            this.field = '';
            this.showModal = false;
        },
        isMandatoryField(name) {
            const allowedFields = ['name', 'email', 'message', 'address', 'location', 'phone_number'];
            return allowedFields.includes(name);
        },
        moveUp(index) {
            if (index > 0) {
                const currentField = this.fields[index];
                const previousField = this.fields[index - 1];
                this.fields.splice(index - 1, 2, currentField, previousField);
            }
        },
        moveDown(index) {
            if (index < this.fields.length - 1) {
                const currentIndex = this.fields[index + 1];
                this.fields.splice(index, 2, currentIndex, this.fields[index]);
            }
        },
        fetchFields() {
            this.loading = true;
            this.$get('calendars/' + this.slot.calendar.id + '/slots/' + this.slot.id + '/booking-fields')
                .then(response => {
                    this.fields = response.fields;
                    console.log(response);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        saveSettings() {
            this.saving = true;
            this.$post('calendars/' + this.slot.calendar.id + '/slots/' + this.slot.id + '/booking-fields', {
                booking_fields: this.fields
            })
                .then(response => {
                    this.$handleSuccess(response);
                    this.fetchFields();
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
        this.fetchFields();
    }
}
</script>
