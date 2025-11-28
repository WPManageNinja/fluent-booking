<template>
    <el-icon class="fcal_icon_btn" :class="[isSize, isType]" @click="$emit('clicked')">
        <Plus />
    </el-icon>
</template>

<script type="text/babel">
import {Plus} from '@element-plus/icons-vue';
export default {
    name: 'ActionBtnAdd',
    $emits: ['clicked'],
    components: {
        Plus
    },
    props: ['size', 'type'],
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
