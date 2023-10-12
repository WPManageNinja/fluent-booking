<template>
    <el-form-item :class="field.wrapper_class">
        <template #label>
            <div v-if="field.label">
                {{ field.label }}
                <el-tooltip v-if="field.help" popper-class="sidebar-popper" effect="dark" placement="top">
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

<script type="text/babel">
import {InfoFilled} from '@element-plus/icons-vue';
export default {
    name: 'withLabelField',
    props: ['field'],
    components: {
        InfoFilled
    }
}
</script>
