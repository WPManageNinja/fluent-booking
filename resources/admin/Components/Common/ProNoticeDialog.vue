<template>
    <el-dialog
        v-model="showModal"
        :title="modalTitle"
        width="40%"
        class="fcal_dialog fcal_pro_notice_dialog">
        <div>
            <p class="fcal_need_pro">{{ modalTitle + ' ' + $t('NeedProVersion')}}</p>
            <a target="_blank" :href="appVars.upgrade_url" class="el-button fcal_primary_btn">
                {{$t('Upgrade to Pro')}}
            </a>
        </div>
    </el-dialog>
</template>

<script>
export default {
    name: 'ProNoticeDialog',
    props: ['title', 'openModal'],
    data() {
        return {
            showModal: this.openModal,
            modalTitle: this.title || this.$t('This feature')
        };
    },
    watch: {
        showModal(val) {
            if (!val) {
                this.$emit('update:openModal', false);
            }
        }
    }
};
</script>