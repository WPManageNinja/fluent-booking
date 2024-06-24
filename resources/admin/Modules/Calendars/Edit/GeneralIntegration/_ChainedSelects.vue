<template>
    <div v-loading="loading" class="fcal_chained_filter">
        <el-row :gutter="0">
            <el-col :span="12" v-for="(options, optionKey) in field.fields_options" :key="optionKey" class="wpf_each_filter">
                <label>{{field.options_labels[optionKey].label}}</label>
                <el-select
                    @change="handleSlectChange(optionKey)"
                    filterable
                    clearable
                    :placeholder="field.options_labels[optionKey].placeholder"
                    :multiple="field.options_labels[optionKey].type == 'multi-select'"
                    v-model="modelValue[optionKey]"
                    popper-class="fcal_select"
                >
                    <el-option v-for="(option,optionId) in options" :key="optionId" :value="optionId"
                               :label="option"></el-option>
                </el-select>
            </el-col>
        </el-row>
    </div>
</template>
<script>
import each from 'lodash/each';
export default {
    name: 'ChainedSelects',
    props: ['editingIntegration', 'calendarEvent', 'settings', 'field', 'modelValue'],
    data() {
        return {
            tmp: '',
            app_ready: false,
            loading: false
        }
    },
    methods: {
        fetchSettings() {
            this.loading = true;
            const calendarId = this.calendarEvent.calendar_id;
            const calendarEventId = this.calendarEvent.id;
            const integrationId = this.editingIntegration.integration_id;

            this.$get(`calendars/${calendarId}/events/${calendarEventId}/integrations/${integrationId}/config-field-options`, {
                calendar_id: calendarId,
                settings: this.settings,
                integration_name: this.editingIntegration.integration_name
            })
                .then(response => {
                    const dataOptions = response.field_options;
                    each(dataOptions, (data, dataKey) => {
                        this.field.fields_options[dataKey] = data;
                    });
                })
                .catch(error => {
                    console.log(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        handleSlectChange(targetId) {
            if (targetId === this.field.primary_key) {
                this.fetchSettings();
                each(this.modelValue, (itemValue, valueKey) => {
                    if (valueKey != targetId) {
                        if (this.field.options_labels[valueKey].type == 'multi-select') {
                            this.modelValue[valueKey] = [];
                        } else {
                            this.modelValue[valueKey] = '';
                        }
                    }
                });
            }
        }
    },
    mounted() {
        this.fetchSettings();
        this.app_ready = true;
    }
}
</script>

