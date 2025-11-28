<template>
    <div>

        <div class="fcal_integration_instruction">
            <p>{{ $t('Please') }} <a target="_blank" rel="noopener"
                         href="https://fluentbooking.com/docs/zoom-integration-with-fluentbooking/">{{ $t('read the documentation here') }}</a> {{ $t('zoom_integration_step_by_step_guide') }}
            </p>
        </div>

        <form-builder :formData="form" :fields="form_fields"/>

        <p>
            <el-icon>
                <Lock/>
            </el-icon>
            {{ $t('The above app secret key will be encrypted and stored securely.') }}
        </p>

        <div class="fcal_integration_form_footer">
            <el-button :disabled="saving" v-loading="saving" @click="saveSettings()"
                       class="fcal_primary_btn">
                {{ $t('Save & Validate Credentials') }}
            </el-button>
        </div>
    </div>
</template>

<script type="text/babel">
import FormBuilder from '@/Components/FormBuilder/FormBuilder.vue';
import {Lock} from '@element-plus/icons-vue';

export default {
    name: 'IntegrationForm',
    $emits: ['connected'],
    components: {
        FormBuilder,
        Lock
    },
    props: ['form_fields', 'calendar_id'],
    data() {
        return {
            form: {},
            saving: false,
            user_id: null
        }
    },
    methods: {
        saveSettings() {
            this.saving = true;
            let url = 'integrations/zoom/save-user-account';

            if (this.calendar_id) {
                url = 'calendars/' + this.calendar_id + '/integrations/zoom-connection/add';
            }

            this.$post(url, {
                calendar_id : this.calendar_id,
                zoom_credentials: this.form,
                user_id: this.user_id
            })
                .then(response => {
                    this.$notify.success(response);
                    this.$emit('connected');
                })
                .catch((errors) => {
                    this.$handleError(errors)
                })
                .finally(() => {
                    this.saving = false;
                });
        }
    },
    mounted() {
        this.form = {
            account_id: '',
            client_id: '',
            client_secret: ''
        };
    }
}
</script>
