<template>
    <el-input :type="field.data_type"
              :min="field.min"
              :max="field.max"
              :placeholder="field.placeholder"
              v-model="model"></el-input>
</template>

<script type="text/babel">
export default {
    name: 'InputText',
    props: ['field', 'value'],
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
