<template>
    <div class="fcal_settings_body_inner fcal_settings_availability">
        <div class="fcal_settings_header">
            <h3>Availability</h3>
        </div>
        <div class="fcal_settings_content_wrap">
            <el-form label-position="top">
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
                                <el-icon><ScheduleIcon/></el-icon> {{ item.title }}
                            </template>
                            <div class="fcal_availability_body">
                                <div class="fcal_availability_header">
                                    <h3> Working Hours Schedule
                                        <span class="default-schedule-badge"><el-icon><StarFilled /></el-icon> Default schedule</span>
                                    </h3>
                                </div>
                                <div class="timezone">
                                    <div class="fcal_timezone_text">
                                        <el-icon><TimezoneIcon/></el-icon>
                                        <p>{{ slot.calendar.author_timezone }}</p>
                                    </div>
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
import ScheduleIcon from "../../Components/Icons/ScheduleIcon";
import TimezoneIcon from '../../Components/Icons/TimezoneIcon';

export default {
    name: "AvailabilitySettings",
    components: {
        DateOverRides,
        WeeklySchedules,
        TimezoneIcon,
        Calendar,
        Plus,
        ScheduleSettings,
        ScheduleIcon,
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
                calendar: {
                    author_timezone: 'Asia/Dhaka'
                },
                settings: {
                    date_overrides: {},
                    schedule_type: '',
                    weekly_schedules: this.appVars.schedule_schema,
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