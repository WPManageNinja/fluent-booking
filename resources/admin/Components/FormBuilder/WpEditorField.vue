<template>
    <div class="wp_vue_editor_wrapper">
        <textarea v-if="hasWpEditor" class="wp_vue_editor" :id="editor_id">{{ model }}</textarea>
        <textarea v-else
                  class="wp_vue_editor wp_vue_editor_plain"
                  v-model="plain_content"
                  @click="updateCursorPos">
        </textarea>
    </div>
</template>

<script type="text/babel">
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
            type: Array,
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
        }
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
            currentEditor: false
        }
    },
    watch: {
        plain_content() {
            this.$emit('update:modelValue', this.plain_content);
        }
    },
    methods: {
        initEditor() {
            wp.editor.remove(this.editor_id);
            const that = this;
            wp.editor.initialize(this.editor_id, {
                tinymce: {
                    height : that.height,
                    toolbar1: 'formatselect,table,bold,italic,bullist,numlist,link,blockquote,alignleft,aligncenter,alignright,underline,strikethrough,forecolor,removeformat,codeformat,outdent,indent,undo,redo',
                    setup(editor) {
                        editor.on('change', function (ed, l) {
                            that.changeContentEvent();
                        });
                    }
                },
                quicktags: true
            });
            jQuery('#'+this.editor_id).on('change', function(e) {
                that.changeContentEvent();
            });
        },
        changeContentEvent() {
            const content = wp.editor.getContent(this.editor_id);
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
                window.tinymce.activeEditor.insertContent(command);
            } else {
                var part1 = this.plain_content.slice(0, this.cursorPos);
                var part2 = this.plain_content.slice(this.cursorPos, this.plain_content.length);
                this.plain_content = part1 + command + part2;
                this.cursorPos += command.length;
            }
        },
        updateCursorPos() {
            var cursorPos = jQuery('.wp_vue_editor_plain').prop('selectionStart');
            this.$set(this, 'cursorPos', cursorPos);
        }
    },
    mounted() {
        this.initEditor();
        this.app_ready = true;
    }
}
</script>
