<template>
    <el-form :model="notification.sms" label-position="top" class="fcal_sms_form">
        <el-form-item label="Number * (with country code)">
            <popover
                groupTitle="Shortcodes"
               :data="smart_codes.texts"
                placement="bottom-end"
                :isVisible="numberPopupVisible"
                class="fcal_popover_shortcode"
               @command="handleNumberCommand">
               <template #popoverButton>
                    <el-input
                        type="text"
                        v-model="notification.sms.number">
                        <template #append>
                            <el-button :icon="MoreIcon" @click="toggleNumberPopup"></el-button>
                        </template>
                    </el-input>
                </template>
            </popover>
        </el-form-item>
        <el-form-item class="fcal_sms_body">
            <template #label>
                <h3 class="el-form-item__label">
                    SMS Body
                    <el-button @click="toggleBodyPopup"><el-icon><More /></el-icon></el-button>
                </h3>
            </template>
            <popover
                groupTitle="Shortcodes"
                :data="smart_codes.texts"
                placement="bottom-end"
                :isVisible="bodyPopupVisible"
                class="fcal_popover_shortcode"
                @command="handleBodyCommand">
                <template #popoverButton>
                    <el-input
                        type="textarea"
                        v-model="notification.sms.body">
                    </el-input>
                </template>
            </popover>
        </el-form-item>
        <el-form-item v-if="notification.sms.times" label="Timing">
            <div v-for="(item, index) in notification.sms.times" :key="index" class="fcal_inline_items fcal_reminder_timing">
                <el-input type="text" v-model="item.value" @input="validateInput(item)"/>
                <el-select v-model="item.unit" @change="validateInput(item)" placeholder="Select Unit" popper-class="fcal_select">
                    <el-option value="minutes" label="Minutes Before"></el-option>
                    <el-option value="hours" label="Hours Before"></el-option>
                    <el-option value="days" label="Days Before"></el-option>
                </el-select>
                <el-link v-if="isRemovable" type="danger" title="Remove"
                    :icon="CloseBoldIcon"
                    :underline="false"
                    @click="removeReminderTime(index)">
                </el-link>
            </div>
            <el-link type="primary" :underline="false" @click="addReminderTime">
                    + Add Another Reminder
            </el-link>
        </el-form-item>
        <el-form-item label="Status">
            <el-checkbox v-model="notification.enabled"> Enable this sms notification</el-checkbox>
        </el-form-item>
    </el-form>
</template>

<script>
import { markRaw } from "vue";
import Popover from '../../../Components/Popover.vue';
import { Plus, More, CloseBold, ArrowDown } from '@element-plus/icons-vue';
export default {
    name: 'EditSmsNotificationEmail',
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
            numberPopupVisible: false,
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
            return this.notification.sms.times && this.notification.sms.times.length > 1;
        }
    },
    methods: {
        toggleNumberPopup() {
            this.numberPopupVisible = !this.numberPopupVisible;
        },
        toggleBodyPopup() {
            this.bodyPopupVisible = !this.bodyPopupVisible;
        },
        handleNumberCommand(command) {
            this.notification.sms.number += command;
            this.numberPopupVisible = false;
        },
        handleBodyCommand(command) {
            this.notification.sms.body += command;
            this.bodyPopupVisible = false;
        },
        addReminderTime() {
            this.notification.sms.times.push({
                value: 15,
                unit: 'minutes'
            });
        },
        removeReminderTime(index) {
            this.notification.sms.times.splice(index, 1);
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
    }
}
</script>
