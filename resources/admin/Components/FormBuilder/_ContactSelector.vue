<template>
    <el-select
        v-if="appReady"
        v-model="model"
        :multiple="field.is_multiple"
        filterable
        :remote="!field.cacheable"
        :clearable="field.clearable"
        :disabled="field.disabled"
        reserve-keyword
        :size="field.size"
        :placeholder="field.placeholder || $t('Search contact')"
        :remote-method="fetchOptions"
        v-loading="loading"
        :no-match-text="$t('No Data match')"
        :no-data-text="$t('No Data')">
        <el-option
            v-for="item in options"
            :key="item.id"
            :label="item.full_name + ' (' + item.email + ')'"
            :value="item.id">
        </el-option>
    </el-select>
</template>

<script type="text/babel">
import each from 'lodash/each';
export default {
    name: 'ContactSelector',
    props: ['field', 'value'],
    data() {
        return {
            model: this.value,
            loading: false,
            options: {},
            appReady: false
        }
    },
    watch: {
        model(value) {
            this.$emit('input', value);
            this.$emit('contactSelected', this.options[value]);
        }
    },
    methods: {
        fetchOptions(query) {
            this.loading = true;
            this.$get('subscribers/search-contacts', {
                search: query,
                values: this.model
            })
                .then(response => {
                    this.options = response.contacts;
                })
                .catch((errors) => {
                    this.handleError(errors)
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    mounted() {
        if (!this.model || this.model == '0') {
            if (!this.field.is_multiple) {
                this.model = '';
            }
        }

        if (this.field.pre_options && this.field.pre_options.length) {
            each(this.field.pre_options, (option) => {
                if (option && option.id) {
                    option.id = option.id.toString();
                    this.options[option.id] = option;
                }
            });
        }

        this.appReady = true;

        if (this.model && (typeof this.model != 'object' && !this.options[this.model])) {
            this.fetchOptions('');
        }
    }
}
</script>
