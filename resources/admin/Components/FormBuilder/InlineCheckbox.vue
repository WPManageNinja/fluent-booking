<template>
    <el-checkbox :true-label="field.true_label" :disabled="field.disabled" :false-label="field.false_label" v-model="model">
        {{ field.checkbox_label }}
    </el-checkbox>
</template>

<script type="text/babel">
export default {
    name: 'InlineCheckbox',
    props: ['field', 'modelValue'],
    $emits: ['update:modelValue'],
    data() {
        return {
            model: this.modelValue
        }
    },
    watch: {
        model(value) {
            this.$emit('update:modelValue', value);
        }
    }
}
</script>
