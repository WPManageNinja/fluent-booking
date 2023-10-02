<template>
    <div class="fcal_settings_body_inner fcal_settings_availability">
        <div class="fcal_settings_header">
            <h3>Availability</h3>
        </div>


        <div class="fcal_settings_content_wrap">
            <el-form v-model="formData" label-position="top">
                <el-form-item label="Available hours">
                    <span class="sub-label">Edit the schedule below so that you can apply to your event/booking types</span>
                </el-form-item>
                <el-form-item label="Schedule" class="fcal_tab_schedule">

                    <el-button class="fcal_plain_btn fcal_add_new_tab" @click="dialogVisible = true">
                        <el-icon><Plus /></el-icon>
                    </el-button>
                    <el-tabs
                        v-model="editableTabsValue"
                        type="card"
                        class="fcal_tabs2"
                    >
                        <el-tab-pane
                            v-for="item in editableTabs"
                            :key="item.name"
                            :name="item.name"
                        >
                            <template #label>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M6.66669 1.66699V4.16699" stroke="#2653C7" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M13.3333 1.66699V4.16699" stroke="#445164" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M2.91669 7.5752H17.0834" stroke="#445164" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M17.5 7.08366V14.167C17.5 16.667 16.25 18.3337 13.3333 18.3337H6.66667C3.75 18.3337 2.5 16.667 2.5 14.167V7.08366C2.5 4.58366 3.75 2.91699 6.66667 2.91699H13.3333C16.25 2.91699 17.5 4.58366 17.5 7.08366Z" stroke="#445164" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M13.0789 11.4167H13.0864" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M13.0789 13.9167H13.0864" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.99626 11.4167H10.0037" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.99626 13.9167H10.0037" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6.91191 11.4167H6.91939" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6.91191 13.9167H6.91939" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg> {{ item.title }}
                            </template>
                            <div class="fcal_availability_body">
                                <div class="fcal_availability_header">
                                    <h3>
                                        Working Hours Schedule <span class="default-schedule-badge"><el-icon><StarFilled /></el-icon> Default schedule</span>
                                    </h3>
                                </div>
                                <div class="timezone">
                                    <div class="el-form-item__label">Timezone</div>
                                    <el-select
                                        v-model="formData.timezone"
                                        placeholder="Select timezone"
                                        popper-class="fcal_select"
                                        class="fcal_timezone"
                                    >
                                        <el-option
                                            label="Asia/Dhaka"
                                            value="asia/dhaka"
                                        />
                                        <el-option
                                            label="United State"
                                            value="us"
                                        />
                                    </el-select>
                                </div>
                                <div class="fcal_availability_setting">
                                    <WeeklySchedules
                                        :weekly_schedules="slot.settings.weekly_schedules"
                                        title="Weekly Hours"
                                    />
                                    <date-over-rides
                                        :settings="slot.settings"
                                        title="Add date overrides"
                                    />
                                </div>
                            </div>
                        </el-tab-pane>
                    </el-tabs>

                </el-form-item>

            </el-form>


            <div class="fcal_settings_footer">
                <el-button class="fcal_primary_btn">
                    Save Changes
                </el-button>
            </div>
        </div>

        <el-dialog
            v-model="dialogVisible"
            title="Tips"
            width="30%"
        >
            <el-form v-model="addTabData" label-position="top">
                <el-form-item label="Tab Title">
                    <el-input v-model="addTabData.title" />
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button class="fcal_plain_btn" @click="dialogVisible = false">
                        Cancel
                    </el-button>
                    <el-button class="fcal_primary_btn" @click="addTab(editableTabsValue)">
                        Add
                    </el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script>
import { Calendar, Plus, StarFilled } from '@element-plus/icons-vue';
import ScheduleSettings from "../Calendars/Edit/_ScheduleSettings";
import WeeklySchedules from "../Calendars/parts/WeeklySchedules";
import DateOverRides from "../Calendars/Edit/_DateOverRides";

export default {
    name: "AvailabilitySettings",
    components: {
        DateOverRides,
        WeeklySchedules,
        Calendar,
        Plus,
        ScheduleSettings,
        StarFilled
    },
    data() {
        return {
            dialogVisible: false,
            tabIndex: '2',
            editableTabsValue: '2',
            addTabData: {
                title: ''
            },
            formData: {

            },
            editableTabs: [
                {
                    title: 'Working Hours',
                    name: '1',
                    content: '<h1>test 2</h1>'
                },
                {
                    title: 'New Schedule',
                    name: '2',
                    content: '<code>test</code>'
                },
            ],
            slot: {
                settings: {
                    date_overrides: {},
                    schedule_conditions: {
                        unit: '',
                        value: 4
                    },
                    schedule_type: '',
                    weekly_schedules: {
                        sun: {
                            enabled: false,
                            slots: []
                        },
                        mon: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        tue: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        wed: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        thu: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        fri: {
                            enabled: true,
                            slots: [
                                {
                                    start: '09:00',
                                    end: '17:00'
                                }
                            ]
                        },
                        sat: {
                            enabled: false,
                            slots: []
                        }
                    },
                },
            }
        }
    },
    methods: {
        addTab(targetName) {
            const newTabName = `${++this.tabIndex}`;
            this.editableTabs.push({
                title: this.addTabData.title,
                name: newTabName,
                content: 'New Tab content',
            })
            this.editableTabsValue = newTabName;
            this.dialogVisible = false;
        }
    }
}
</script>

<style scoped>

</style>