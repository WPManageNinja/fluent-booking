<template>
    <el-form-item :class="field.wrapper_class">
        <template #label>
            <div v-if="field.label" :class="field.label_class">
                {{ field.label }}
                <el-tooltip v-if="field.help" popper-class="fcal_input_popper" effect="dark" placement="top">
                    <template #content>
                        <div v-html="field.help"></div>
                    </template>
                    <el-icon><InfoFilled /></el-icon>
                </el-tooltip>
            </div>
        </template>
        <slot></slot>
        <p v-if="field.inline_help" class="fc_inline_help" v-html="field.inline_help"></p>
    </el-form-item>
</template>

<script>
export default {
    name: 'withLabelField',
    props: ['field']
}
</script>
