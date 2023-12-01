<template>
    <el-form :model="notification.sms" label-position="top" class="fcal_sms_form">
        <el-form-item v-if="notification.is_host" :label="$t('Receiver *')" class="fcal_sms_radio">
            <el-radio-group v-model="notification.sms.receiver">
                <el-radio label="host_number"> {{ $t('Host Number') }}</el-radio>
                <el-radio label="custom_number">{{ $t('Custom Number') }}</el-radio>
            </el-radio-group>
        </el-form-item>
        <el-form-item v-if="notification.sms.receiver == 'host_number'">
            <el-input
                v-if="host_phone"
                type="text"
                :disabled="true"
                :value="host_phone">
            </el-input>
            <p v-else>
                {{ $t('Please set the host phone number from') }} <span><el-link @click="goToCalendarSettings">{{ $t('here') }}</el-link></span>
            </p>
        </el-form-item>
        <el-form-item v-if="notification.sms.receiver == 'custom_number'">
            <el-input
                type="text"
                :placeholder="$t('Enter number with country code')"
                v-model="notification.sms.number">
            </el-input>
        </el-form-item>
        <p v-if="!notification.is_host">{{ $t('EditSmsNotificationSettings/custom_number_desc') }}</p>
        <el-form-item class="fcal_sms_body">
            <template #label>
                <h3 class="el-form-item__label">
                    {{ $t('SMS Body') }}
                    <el-button @click="toggleBodyPopup" class="fcal_plain_btn"><el-icon><More /></el-icon></el-button>
                </h3>
            </template>
            <popover
                :groupTitle="$t('Shortcodes')"
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
        <el-form-item v-if="notification.sms.times" :label="$t('Timing')">
            <div v-for="(item, index) in notification.sms.times" :key="index" class="fcal_inline_items fcal_reminder_timing">
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
        <el-form-item :label="$t('Send *')" class="fcal_sms_radio">
            <el-radio-group v-model="notification.sms.send_to">
                <el-radio label="phone">{{ $t('SMS') }}</el-radio>
                <el-radio label="whatsapp">{{ $t('WhatsApp') }}</el-radio>
            </el-radio-group>
        </el-form-item>
        <el-form-item :label="$t('Status')">
            <el-checkbox v-model="notification.enabled"> {{ $t('Enable this sms notification') }}</el-checkbox>
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
        },
        host_phone: {},
        calendar_id: {}
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
        toggleBodyPopup() {
            this.bodyPopupVisible = !this.bodyPopupVisible;
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
        },
        goToCalendarSettings() {
            this.$router.push({
                name: 'calendar_settings',
                params: { calendar_id: this.calendar_id }
            });
        }
    }
}
</script>
