<template>
    <div>
        <el-select :placeholder="field.placeholder" filterable allow-create v-if="field.options.length"
                   v-model="model"
                   :no-match-text="$t('No Data match')"
                   :no-data-text="$t('No Data')"
                   popper-class="fcal_select"
                    >
            <el-option v-for="option in field.options" :key="option" :value="option"></el-option>
        </el-select>
        <el-input v-else :type="field.data_type" :placeholder="field.placeholder" v-model="model"></el-input>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'InputOrSelect',
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
