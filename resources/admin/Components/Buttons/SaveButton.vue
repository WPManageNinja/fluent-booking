<template>
    <el-button
        @click="handleClick"
        :disabled="saving"
        v-loading="saving"
        class="fcal_primary_btn">
        {{ label }}
    </el-button>
</template>
  
<script>
export default {
    props: ['saving', 'label'],
    methods: {
        handleClick() {
            this.$emit('save');
        },
    },
};
</script>
  