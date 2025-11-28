<template>
    <div class="fcal_photo_card">
        <div v-if="app_ready" class="fcal_photo_holder">
            <img v-if="image_url" :src="image_url" />
            <el-button size="small" class="fcal_upload_btn" @click="initUploader">
                <el-icon><CameraFilled /></el-icon>
            </el-button>
            <slot name="after"></slot>
        </div>
    </div>
</template>

<script type="text/babel">
import { CameraFilled } from '@element-plus/icons-vue';

export default {
    name: 'PhotoWidget',
    emits: ['update:modelValue'],
    props: {
        modelValue: {
            required: false,
            type: String
        }
    },
    components: {
        CameraFilled
    },
    data() {
        return {
            app_ready: false,
            image_url: this.modelValue
        }
    },
    watch: {
        modelValue(newVal) {
            this.image_url = newVal;
        }
    },
    methods: {
        initUploader(event) {
            const that = this;
            const sendAttachmentBkp = wp.media.editor.send.attachment;
            wp.media.editor.send.attachment = function (props, attachment) {
                that.$emit('update:modelValue', attachment.url);
                that.image_url = attachment.url;
                wp.media.editor.send.attachment = sendAttachmentBkp;
            }
            wp.media.editor.open(jQuery(event.target));
            return false;
        },
        getThumb(attachment) {
            return attachment.url;
        }
    },
    mounted() {
        if (!window.wpActiveEditor) {
            window.wpActiveEditor = null;
        }
        this.app_ready = true;
    }
};
</script>