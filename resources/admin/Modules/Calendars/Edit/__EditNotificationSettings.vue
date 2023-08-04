<template>
    <div class="fcal_form_section">
        <div class="fcal_section_body">
            <el-form :model="email" label-position="top">
                <el-col :sm="24" :md="15">
                    <el-form-item label="Subject">
                        <popover
                            groupTitle="Shortcodes"
                           :data="editorShortcodes"
                           :isVisible="subjectPopupVisible"
                           @command="handleSubjectCommand">
                           <template #popoverButton>
                                <el-input 
                                    type="text"
                                    v-model="email.subject">
                                    <template #append>
                                        <el-button :icon="MoreIcon" @click="toggleSubjectPopup"></el-button>
                                    </template>
                                </el-input>
                            </template>
                        </popover>
                    </el-form-item>
                </el-col>
                <el-col :sm="24" :md="15">
                    <el-form-item label="Email Body">
                        <div class="wp_vue_editor_wrapper">
                            <popover
                                v-if="hasWpEditor"
                                class="popover-wrapper"
                                groupTitle="Shortcodes"
                                :data="editorShortcodes"
                                :isVisible="bodyPopupVisible"
                                @command="handleBodyCommand">
                                <template #popoverButton>
                                    <el-button 
                                        type="info"
                                        :icon="ArrowDownIcon"
                                        @click="toggleBodyPopup"
                                        class="editor-add-shortcode el-button--soft">
                                        Add Shortcodes
                                    </el-button>
                                </template>
                            </popover>
                            <textarea 
                                class="wp_vue_editor"
                                :id="editor_id" 
                                v-model="email.body">
                            </textarea>
                        </div>
                    </el-form-item>
                </el-col>
                <el-col v-if="email.times" :sm="24" :md="5">
                    <el-form-item label="Timing">
                        <div v-for="(item, index) in email.times" :key="index" class="fcal_inline_items fcal_reminder_timing">
                            <el-col :span="6">
                                <el-input type="text" v-model="item.value" @input="validateInput(item)"/>
                            </el-col>
                            <el-col :span="18">
                                <el-select v-model="item.unit" @change="validateInput(item)" placeholder="Select Unit">
                                    <el-option value="minutes" label="Minutes Before"></el-option>
                                    <el-option value="hours" label="Hours Before"></el-option>
                                    <el-option value="days" label="Days Before"></el-option>
                                </el-select>
                            </el-col>
                            <el-col v-if="isRemovable">
                                <el-link type="danger" title="Remove" 
                                    :icon="CloseBoldIcon" 
                                    :underline="false"
                                    @click="removeReminderTime(index)">
                                </el-link>
                            </el-col>
                        </div>
                        <div class="fcal_add_reminder">
                            <el-link type="primary" :underline="false" @click="addReminderTime" :icon="PlusIcon">
                                 Add Another Reminder
                            </el-link>
                        </div>
                    </el-form-item>
                </el-col>
            </el-form>
        </div>
    </div>
</template>

<script>
import { markRaw } from "vue";
import Popover from '../../../Components/Popover.vue';
import { Plus, More, CloseBold, ArrowDown } from '@element-plus/icons-vue';
export default {
    name: 'EditNotificationEmail',
    components: {
        Plus,
        More,
        CloseBold,
        ArrowDown,
        Popover
    },
    props: {
        email: {
            type: Object,
        },
        editor_id: {
            type: String,
            default() {
                return 'wp_editor_'+ Date.now() + parseInt( Math.random() * 1000 );
            }
        },
    },
    data() {
        return {
            editorShortcodes: [],
            subjectPopupVisible: false,
            bodyPopupVisible: false,
            hasWpEditor: !!window.wp.editor,
            PlusIcon: markRaw(Plus),
            MoreIcon: markRaw(More),
            CloseBoldIcon: markRaw(CloseBold),
            ArrowDownIcon: markRaw(ArrowDown),
        }
    },
    computed: {
        isRemovable() {
            return this.email.times.length > 1;
        }
    },
    methods: {
        fetchShortcodes() {
            this.$get('calendars/shortcodes')
                .then(response => {
                    this.editorShortcodes = response;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
        },
        initEditor() {
            wp.editor.remove(this.editor_id);
            const that = this;
            wp.editor.initialize(this.editor_id, {
                tinymce: {
                    height : 300,
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
            this.email.body = content;
        },
        toggleSubjectPopup() {
            this.subjectPopupVisible = !this.subjectPopupVisible;
        },
        toggleBodyPopup() {
            this.bodyPopupVisible = !this.bodyPopupVisible;
        },
        handleSubjectCommand(command) {
            this.email.subject += command;
            this.subjectPopupVisible = false;
        },
        handleBodyCommand(command) {
            this.bodyPopupVisible = false;
            tinymce.activeEditor.insertContent(command);
        },
        addReminderTime() {
            this.email.times.push({
                value: 15,
                unit: 'minutes'
            });
        },
        removeReminderTime(index) {
            this.email.times.splice(index, 1);
        },
        validateInput(item) {
            const limitValues = {
                minutes: 59,
                hours: 24,
                days: 99,
            };
            if (isNaN(item.value) || item.value < 0) {
                item.value = '';
            } else if (item.unit in limitValues && item.value > limitValues[item.unit]) {
                item.value = limitValues[item.unit];
            }
        }
    },
    mounted() {
        if (this.hasWpEditor) {
            this.initEditor();
        }
        this.fetchShortcodes();
    }
}
</script>
