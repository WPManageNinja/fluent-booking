<template>
    <div class="field_general">
        <popover
            :groupTitle="$t('Shortcodes')"
            :data="editorShortcodes"
            placement="bottom-end"
            class="fcal_popover_shortcode"
            @command="handleSubjectCommand"
            :isVisible="subjectPopupVisible"
        >
            <template #popoverButton>
                <el-input :type="field_type" v-model="fieldValue">
                    <template #append>
                        <el-button :icon="MoreIcon" @click="toggleSubjectPopup"></el-button>
                    </template>
                </el-input>
            </template>
        </popover>
    </div>
</template>

<script type="text/babel">
    import Popover from '@/Components/Popover.vue';
    import { markRaw } from "vue";
    import { More } from '@element-plus/icons-vue';

    export default {
        name: 'fieldGeneral',
        components: {
            Popover,
            More,
        },
        props: {
            modelValue: [String, Number, Boolean],
            editorShortcodes: [Array, Object],
            field_type: {
                type: String,
                default: 'text',
            },
        },
        computed: {
            fieldValue: {
                get() {
                    return this.modelValue;
                },
                set(value) {
                    this.$emit('update:modelValue', value);
                },
            },
        },

        data() {
            return {
                MoreIcon: markRaw(More),
                subjectPopupVisible: false,
            };
        },

        methods: {
            handleSubjectCommand(command) {

                let value = command;
                if(this.fieldValue) {
                    value = this.fieldValue + command;
                }

                this.$emit('update:modelValue', value);
                this.subjectPopupVisible = false;
            },

            toggleSubjectPopup() {
                this.subjectPopupVisible = !this.subjectPopupVisible;
            },
        },
    };
</script>
