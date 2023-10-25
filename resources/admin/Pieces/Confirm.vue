<template>
    <el-popconfirm
        width="220"
        :confirm-button-text="$t('Confirm')"
        :cancel-button-text="$t('No')"
        :title="message"
        @confirm="confirm"
    >
        <template #reference>
            <slot name="reference">
                <el-button>{{ $t('Delete') }}</el-button>
            </slot>
        </template>
    </el-popconfirm>
</template>

<script>
export default {
    name: 'Confirm',
    props: {
        placement: {
            default: 'top-end'
        },
        message: {
            default: 'Are you sure to delete this?'
        }
    },
    data() {
        return {
            visible: false
        }
    },
    methods: {
        hide() {
            this.visible = false;
        },
        confirm() {
            this.hide();
            this.$emit('yes');
        },
        cancel() {
            this.hide();

            this.$emit('no');
        }
    }
};
</script>
