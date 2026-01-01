<template>
    <div class="wp_vue_editor_wrapper">
        <popover
            v-if="hasWpEditor && editorShortcodes.length > 0"
            class="popover-wrapper"
            :groupTitle="$t('Shortcodes')"
            :data="editorShortcodes"
            :isVisible="isPopupVisible"
            @command="handleCommand">
            <template #popoverButton>
                <el-button
                    type="info"
                    :icon="ArrowDownIcon"
                    @click="togglePopup"
                    class="editor-add-shortcode el-button--soft">
                    {{ $t('Add Shortcodes') }}
                </el-button>
            </template>
        </popover>
        <textarea v-if="hasWpEditor"
            class="wp_vue_editor"
            :id="editor_id">{{ model }}
        </textarea>
        <textarea v-else
                  class="wp_vue_editor wp_vue_editor_plain"
                  v-model="plain_content"
                  @click="updateCursorPos">
        </textarea>
    </div>
</template>

<script>
import Popover from '../Popover.vue';
import { markRaw } from "vue";
import { ArrowDown } from '@element-plus/icons-vue';
export default {
    name: 'wp_editor',
    $emits: ['update:modelValue'],
    props: {
        editor_id: {
            type: String,
            default() {
                return 'wp_editor_' + Date.now() + parseInt(Math.random() * 1000);
            }
        },
        modelValue: {
            type: String,
            default() {
                return '';
            }
        },
        editorShortcodes: {
            type: Object,
            default() {
                return []
            }
        },
        height: {
            type: Number,
            default() {
                return 200;
            }
        },
        extra_style: {
            default() {
                return ''
            }
        },
        media_buttons: {
            type: Boolean,
            default: true
        },
        quick_tags: {
            type: Boolean,
            default: true
        },
        toolbar: {
            type: String,
            default: 'formatselect,table,bold,italic,bullist,numlist,link,blockquote,alignleft,aligncenter,alignright,underline,strikethrough,forecolor,removeformat,codeformat,outdent,indent,undo,redo'
        }
    },
    components: {
        Popover,
        ArrowDown
    },
    data() {
        return {
            model: this.modelValue,
            showButtonDesigner: false,
            hasWpEditor: (!!window.wp.editor && !!wp.editor.autop) || !!window.wp.oldEditor,
            editor: window.wp.oldEditor || window.wp.editor,
            plain_content: this.modelValue,
            cursorPos: (this.modelValue) ? this.modelValue.length : 0,
            app_ready: false,
            buttonInitiated: false,
            currentEditor: false,
            isPopupVisible: false,
            ArrowDownIcon: markRaw(ArrowDown),
            isDarkMode: localStorage.getItem('fcal_color_mode') == 'dark',
        }
    },
    watch: {
        plain_content() {
            this.$emit('update:modelValue', this.plain_content);
        }
    },
    methods: {
        initEditor() {
            if (!this.hasWpEditor) {
                return;
            }

            this.editor.remove(this.editor_id);
            const that = this;
            this.editor.initialize(this.editor_id, {
                mediaButtons: this.media_buttons,
                quicktags: this.quick_tags,
                tinymce: {
                    height : that.height,
                    toolbar1: this.toolbar,
                    content_style: this.isDarkMode ? '*{color:white;} body { background: #11171d; }' : '',
                    setup(editor) {
                        editor.on('change', function (ed, l) {
                            that.changeContentEvent();
                        });
                    }
                },
            });
            jQuery('#'+this.editor_id).on('change', function(e) {
                that.changeContentEvent();
            });
        },
        togglePopup() {
            this.isPopupVisible = !this.isPopupVisible;
        },
        changeContentEvent() {
            const content = this.editor.getContent(this.editor_id).replace(/\r?\n/g, '');
            this.$emit('update:modelValue', content);
        },
        showInsertButtonModal(editor) {
            this.currentEditor = editor;
            this.showButtonDesigner = true;
        },
        insertHtml(content) {
            this.currentEditor.insertContent(content);
        },
        handleCommand(command) {
            if (this.hasWpEditor) {
                this.isPopupVisible = false;
                tinymce.activeEditor.insertContent(command);
            } else {
                var part1 = this.plain_content.slice(0, this.cursorPos);
                var part2 = this.plain_content.slice(this.cursorPos, this.plain_content.length);
                this.plain_content = part1 + command + part2;
                this.cursorPos += command.length;
            }
        },
        updateCursorPos() {
            var cursorPos = jQuery('.wp_vue_editor_plain').prop('selectionStart');
            this.cursorPos = cursorPos;
        }
    },
    mounted() {
        this.initEditor();
        this.app_ready = true;
    }
}
</script>
