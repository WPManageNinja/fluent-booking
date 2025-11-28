<template>
    <el-form :model="notification.email" label-position="top">
        <el-form-item :label="$t('Subject')">
            <popover
                :groupTitle="$t('Shortcodes')"
                :data="smart_codes.texts"
                placement="bottom-end"
                :isVisible="subjectPopupVisible"
                class="fcal_popover_shortcode"
                @command="handleSubjectCommand">
               <template #popoverButton>
                    <el-input
                        type="text"
                        v-model="notification.email.subject">
                        <template #append>
                            <el-button :icon="MoreIcon" @click="toggleSubjectPopup"></el-button>
                        </template>
                    </el-input>
                </template>
            </popover>
        </el-form-item>
        <el-form-item :label="$t('Email Body')">
            <div class="wp_vue_editor_wrapper">
                <popover
                    v-if="hasWpEditor"
                    class="popover-wrapper"
                    :groupTitle="$t('Shortcodes')"
                    :data="smart_codes.html"
                    :isVisible="bodyPopupVisible"
                    @command="handleBodyCommand">
                    <template #popoverButton>
                        <el-button
                            type="info"
                            :icon="ArrowDownIcon"
                            @click="toggleBodyPopup"
                            class="editor-add-shortcode el-button--soft">
                            {{ $t('Add Shortcodes') }}
                        </el-button>
                    </template>
                </popover>
                <textarea
                    class="wp_vue_editor"
                    :id="editor_id"
                    v-model="notification.email.body">
                </textarea>
            </div>
        </el-form-item>
        <el-form-item v-if="notification.email.times" :label="$t('Timing')">
            <div v-for="(item, index) in notification.email.times" :key="index" class="fcal_inline_items fcal_reminder_timing">
                <el-input type="text" v-model="item.value" @input="validateInput(item)"/>
                <el-select v-model="item.unit" @change="validateInput(item)" :placeholder="$t('Select Unit')" popper-class="fcal_select">
                    <el-option value="minutes" :label="$t('Minutes Before')"></el-option>
                    <el-option value="hours" :label="$t('Hours Before')"></el-option>
                    <el-option value="days" :label="$t('Days Before')"></el-option>
                </el-select>
                <el-link v-if="isRemovable" type="danger" :title="$t('Remove')"
                    :icon="CloseBoldIcon"
                    :underline="false"
                    @click="removeReminderTime(index)">
                </el-link>
            </div>
            <el-link type="primary" :underline="false" @click="addReminderTime">
                {{ $t('+ Add Another Reminder') }}
            </el-link>
        </el-form-item>
        <el-form-item :label="$t('Additional Recipients')" v-if="notification.is_host">
            <el-input
                type="text"
                v-model="notification.email.additional_recipients"
                :placeholder="$t('Enter email addresses separated by commas')">
            </el-input>
            <p>{{ $t('EditNotificationSettings/additional_recipients_desc')}}</p>
        </el-form-item>
        <el-form-item :label="$t('Status')">
            <el-checkbox v-model="notification.enabled"> {{ $t('Enable this notification email') }}</el-checkbox>
        </el-form-item>
    </el-form>
</template>

<script type="text/babel">
import { markRaw } from "vue";
import Popover from '../../../Components/Popover.vue';
import { Plus, More, CloseBold, ArrowDown } from '@element-plus/icons-vue';
export default {
    name: 'EditEmailNotification',
    components: {
        Plus,
        More,
        CloseBold,
        ArrowDown,
        Popover
    },
    props: {
        notification: {
            type: Object,
        },
        editor_id: {
            type: String,
            default() {
                return 'wp_editor_'+ Date.now() + parseInt( Math.random() * 1000 );
            }
        },
        smart_codes: {
            type: Object,
            default() {
                return {
                    texts: {},
                    html: {}
                };
            }
        }
    },
    data() {
        return {
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
            return this.notification.email.times && this.notification.email.times.length > 1;
        }
    },
    methods: {
        initEditor() {
            wp.editor.remove(this.editor_id);
            const that = this;
            wp.editor.initialize(this.editor_id, {
                mediaButtons: true,
                tinymce: {
                    height : 300,
                    toolbar1: 'formatselect,table,bold,italic,bullist,numlist,link,hr,blockquote,alignleft,aligncenter,alignright,underline,strikethrough,forecolor,removeformat,codeformat,outdent,indent,undo,redo',
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
            this.notification.email.body = content;
        },
        toggleSubjectPopup() {
            this.subjectPopupVisible = !this.subjectPopupVisible;
        },
        toggleBodyPopup() {
            this.bodyPopupVisible = !this.bodyPopupVisible;
        },
        handleSubjectCommand(command) {
            this.notification.email.subject += command;
            this.subjectPopupVisible = false;
        },
        handleBodyCommand(command) {
            this.bodyPopupVisible = false;
            tinymce.activeEditor.insertContent(command);
        },
        addReminderTime() {
            this.notification.email.times.push({
                value: 15,
                unit: 'minutes'
            });
        },
        removeReminderTime(index) {
            this.notification.email.times.splice(index, 1);
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
    }
}
</script>
