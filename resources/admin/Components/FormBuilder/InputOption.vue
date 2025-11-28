<template>
    <el-select clearable filterable :multiple="field.multiple" :class="field.wrapper_class" v-model="model"
               :no-match-text="$t('No Data match')"
               :no-data-text="$t('No Data')">
        <el-option v-for="item in field.options" :key="item.id" :value="item.id" :label="item.label">
        </el-option>
    </el-select>
</template>

<script type="text/babel">
export default {
    name: 'InputOption',
    props: ['field', 'modelValue'],
    $emits: ['update:modelValue'],
    data() {
        return {
            model: this.value
        }
    },
    watch: {
        model(value) {
            this.$emit('update:modelValue', value);
        }
    }
}
</script>
