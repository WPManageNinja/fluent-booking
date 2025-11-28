<template>
    <el-select
        filterable
        clearable
        remote
        v-model="selected"
        reserve-keyword
        :placeholder="$t('Search & Select Product')"
        :remote-method="remoteFetch"
        v-loading="loading"
    >
        <el-option
            v-for="item in options"
            :key="item.id"
            :label="item.title"
            :value="item.id"
        >
            <span style="float: left">{{ item.title }}</span>
            <span class="fcal_select_price">
                <span v-html="currency"></span>{{ item.price }}
            </span>
        </el-option>
    </el-select>
</template>

<script>
export default {
    name: 'WooProductSelector',
    props: ['modelValue'],
    $emits: ['update:modelValue'],
    data() {
        return {
            loading: false,
            selected: this.modelValue,
            options: [],
            currency: ''
        }
    },
    watch: {
        selected(value) {
            this.$emit('update:modelValue', value);
        }
    },
    methods: {
        remoteFetch(search = '') {
            this.loading = true;
            this.$get('integrations/options/woo-products', {
                search: search,
                include_id: this.selected
            })
                .then(response => {
                    this.options = response.items;
                    this.currency = response.currency;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                })
        }
    },
    mounted() {
        this.remoteFetch();
    }
}
</script>
