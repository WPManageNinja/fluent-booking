<template>
    <el-radio-group :class="field.wrapper_class" v-model="model">
        <el-radio v-for="(item,itemIndex) in field.options" :key="itemIndex" :label="item.id">
            {{ item.label }}
        </el-radio>
    </el-radio-group>
</template>

<script type="text/babel">
export default {
    name: 'InputRadio',
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
