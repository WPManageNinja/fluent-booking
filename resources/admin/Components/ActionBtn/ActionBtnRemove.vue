<template>
    <el-icon class="fcal_icon_btn" :class="[isSize, isType]" @click="$emit('clicked')">
        <Minus />
    </el-icon>
</template>

<script type="text/babel">
import {Minus} from '@element-plus/icons-vue';
export default {
    name: 'ActionBtnRemove',
    props: ['size', 'type'],
    $emits: ['clicked'],
    components: {
        Minus
    },
    computed: {
        isSize() {
            return this.size ? this.size : 'small';
        },
        isType() {
            return this.type ? this.type : 'info';
        }
    }
}
</script>
