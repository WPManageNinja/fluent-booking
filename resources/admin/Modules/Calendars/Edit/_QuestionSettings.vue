<template>
    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2><EventIcon/> Booking Questions </h2>
        </div>
        <div class="fcal_create_calendar_form_body">
            <div class="fcal_questions_wrapper">
                <div class="fcal_questions">
                    <div class="fcal_question" v-for="(field, index) in fields" :key="index">
                        <div class="fcal_question_sorting">
                            <el-icon @click="moveUp(index)"><Top /></el-icon>
                            <el-icon @click="moveDown(index)"><Bottom /></el-icon>
                        </div>
                        <div class="fcal_question_card">
                            <div class="fcal_question_content">
                                <h2>{{ field.label }} <span class="required" v-if="field.required == 'yes'">Required</span></h2>
                                <p>{{ field.type }}</p>
                            </div>
                            <div class="fcal_question_actions">
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
        <AddCustomFieldModal 
            v-if="showAddModal"
            :showModal="showAddModal"
            :fields="fields"
            @closeModal="showAddModal = false"
            @newFieldData="newFieldData"
        />
        <EditCustomFieldModal 
            v-if="showEditModal"
            :field="field"
            :fields="fields"
            :phoneRequired="isPhoneRequired"
            :showModal="showEditModal"
            @closeModal="showEditModal = false"
            @updatedFieldData="updatedFieldData"
        />
    </div>
</template>

<script>
import EventIcon from "../../../Components/Icons/EventIcon";
import AddCustomFieldModal from "./__AddCustomFieldModal";
import EditCustomFieldModal from "./__EditCustomFieldModal";
import SaveButton from '../../../Components/Buttons/SaveButton.vue';
import { Bottom, Top } from '@element-plus/icons-vue';

export default {
    name: 'QuestionSettings',
    props: ['slot', 'activeTab'],
    components: {
        EventIcon,
        AddCustomFieldModal,
        EditCustomFieldModal,
        SaveButton,
        Bottom,
        Top
    },
    data() {
        return {
            loading: false,
            saving: false,
            field: {},
            fields : [],
            showAddModal: false,
            showEditModal: false,
            isPhoneRequired: ''
        }
    },
    watch: {
        activeTab() {
            this.fetchFields();
        },
    },
    methods: {
        addQuestion() {
            this.showAddModal = true;
        },
        editField(field) {
            this.field = field;
            this.showEditModal = true;
        },
        deleteField(index) {
            this.fields = this.fields.filter((field) => field.index !== index);
            this.saveSettings();
        },
        newFieldData(field) {
            this.fields.push(field);
            this.saveSettings();
        },
        updatedFieldData(updatedField) {
            this.fields = this.fields.map(field => {
                if (field.index === updatedField.index) {
                    return updatedField;
                }
                return field;
            });
            this.saveSettings();
        },
        updateStatus(status, index) {
            const fieldIndex = this.fields.findIndex(field => field.index === index);
            this.fields[fieldIndex].status = true;
            console.log(this.fields[fieldIndex]);
        },
        closeEdtiModal(field) {
            this.field = {};
            this.showEditModal = false;
        },
        isMandatoryField(name) {
            const allowedFields = ['name', 'email'];
            if (this.isPhoneRequired) {
                allowedFields.push('phone');
            }
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
        this.isPhoneRequired = this.slot?.location_type == 'phone' && this.slot?.location_settings?.call_type == 'outbound';
    }
}
</script>