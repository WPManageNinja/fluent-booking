<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header">
                <h2><QuestionIcon/> {{ $t('Question Settings') }} </h2>
            </div>
            <div class="fcal_create_calendar_form_body">
                <div class="fcal_questions_wrapper">
                    <div class="fcal_questions_title">
                        <h3>{{ $t('Booking Questions') }}</h3>
                        <p>{{ $t('Customize the questions asked on the booking page') }}</p>
                    </div>
                    <el-skeleton animated v-if="loading" />
                    <div v-else class="fcal_questions">
                        <div class="fcal_question" v-for="(field, index) in questionFields" :class="{fcal_field_type_disabled: !field.enabled}" :key="index">
                            <div class="fcal_question_sorting">
                                <el-icon @click="moveUp(index)"><Top /></el-icon>
                                <el-icon @click="moveDown(index)"><Bottom /></el-icon>
                            </div>
                            <div class="fcal_question_card">
                                <div class="fcal_question_content">
                                    <h2>{{ field.label }}
                                        <span class="required" title="Required Field" v-if="field.required">{{ $t('Required') }}</span>
                                        <span class="required" v-if="field.system_defined">{{ $t('System') }}</span>
                                        <span class="required" v-if="!field.enabled">{{ $t('Hidden') }}</span>
                                    </h2>
                                    <p>
                                        <span v-if="displayFieldName(field)">{{ field.name }}</span>
                                        <span v-else>{{ field.type }}</span>
                                    </p>
                                </div>
                                <div class="fcal_question_actions">
                                    <el-switch v-if="isHideable(field)" v-model="field.enabled" @change="saveSettings"/>
                                    <el-button class="fcal_plain_btn" @click="editField(field)">{{ $t('Edit') }}</el-button>
                                    <el-button v-if="!isMandatoryField(field.name)" type="danger" class="fcal_danger_btn" @click="deleteField(field.index)">
                                        <el-icon><Delete /></el-icon>
                                    </el-button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="fcal_question_footer">
                        <el-link class="fcal_add_question" :underline="false" @click="addQuestion">
                            {{ $t('+ Add more questions for invitees') }}
                        </el-link>
                        <SaveButton :saving="saving" :label="$t('Save Changes')" @save="saveSettings"/>
                    </div>
                </div>

                <div class="fcal_questions_wrapper">
                    <div class="fcal_questions_title">
                        <h3>{{ $t('Other Questions') }}</h3>
                        <p>{{ $t('Customize booking cancel and reschedule fields') }}</p>
                    </div>
                    <el-skeleton animated v-if="loading" />
                    <div v-else class="fcal_questions">
                        <div class="fcal_question" v-for="(field, index) in otherFields" :class="{fcal_field_type_disabled: !field.enabled}" :key="index">
                            <div class="fcal_question_card">
                                <div class="fcal_question_content">
                                    <h2>{{ field.label }}
                                        <span class="required" title="Required Field" v-if="field.required">{{ $t('Required') }}</span>
                                        <span class="required" v-if="field.system_defined">{{ $t('System') }}</span>
                                        <span class="required" v-if="!field.enabled">{{ $t('Hidden') }}</span>
                                    </h2>
                                    <p>
                                        <span v-if="displayFieldName(field)">{{ field.name }}</span>
                                        <span v-else>{{ field.type }}</span>
                                    </p>
                                </div>
                                <div class="fcal_question_actions">
                                    <el-switch v-if="isHideable(field)" v-model="field.enabled" @change="saveSettings"/>
                                    <el-button class="fcal_plain_btn" @click="editField(field)">{{ $t('Edit') }}</el-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <EditQuestionFieldModal
                v-if="showModal"
                :field="field"
                :fields="fields"
                :smartCodes="smartCodes"
                :showModal="showModal"
                @closeModal="closeModal"
                @updateFieldData="updateFieldData"
            />
        </div>
        <el-dialog
            v-model="buyModal"
            :title="$t('Upgrade to Pro')"
            class="fcal_dialog pro_dialog"
            :close-on-click-modal="false">
            <div>
                <p class="fcal_need_pro">{{ $t('UnlockWithPro') + ' ' + $t('NeedProVersion')}}</p>
                <a target="_blank" :href="appVars.upgrade_url" class="el-button fcal_primary_btn">
                    {{$t('Upgrade to Pro')}}
                </a>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import QuestionIcon from "../../../Components/Icons/QuestionIcon";
import EditQuestionFieldModal from "./__EditQuestionFieldModal";
import SaveButton from '../../../Components/Buttons/SaveButton.vue';
import { Delete, Bottom, Top } from '@element-plus/icons-vue';

export default {
    name: 'QuestionSettings',
    props: ['calendar_event'],
    components: {
        QuestionIcon,
        EditQuestionFieldModal,
        SaveButton,
        Delete,
        Bottom,
        Top
    },
    data() {
        return {
            loading: false,
            saving: false,
            field: '',
            fields : [],
            smartCodes: {},
            showModal: false,
            buyModal: false,
            otherFieldNames: ['cancellation_reason', 'rescheduling_reason']
        }
    },
    computed: {
        questionFields() {
            return this.fields.filter(field => !this.otherFieldNames.includes(field.name));
        },
        otherFields() {
            return this.fields.filter(field => this.otherFieldNames.includes(field.name));
        },
        displayFieldName() {
            return (field) => {
                return field.system_defined || field.type == 'hidden';
            }
        }
    },
    methods: {
        addQuestion() {
            if (this.appVars.has_pro) {
                this.showModal = true;
            } else {
                this.buyModal = true;
            }
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
            const allowedFields = ['name', 'email', 'message', 'guests', 'address', 'location', 'phone_number', 'payment_method'];
            allowedFields.push(...this.otherFieldNames);
            return allowedFields.includes(name);
        },
        updateFields() {
            const questionFields = this.fields.filter(field => !this.otherFieldNames.includes(field.name));
            const otherFields = this.fields.filter(field => this.otherFieldNames.includes(field.name));
            this.fields = [...questionFields, ...otherFields];
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
                const currentField = this.fields[index];
                const nextField = this.fields[index + 1];
                if (!this.otherFieldNames.includes(nextField.name)) {
                    this.fields.splice(index, 2, nextField, currentField);
                }
            }
        },
        isHideable(field) {
            return !field.disable_alter && !this.otherFieldNames.includes(field.name);
        },
        fetchFields() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/booking-fields', {
                calendar_id: this.calendar_event.calendar_id,
                with: ['smart_codes']
            })
                .then(response => {
                    this.fields = response.fields;
                    this.smartCodes = response.smart_codes;
                    this.updateFields();
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
            this.$post('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/booking-fields', {
                calendar_id: this.calendar_event.calendar_id,
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
