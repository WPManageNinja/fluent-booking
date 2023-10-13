<template>
    <div class="fcal_event_block">
        <div class="fcal_cal_header">
            <div class="fcal_cal_title">
                <img :src="calendar.author_profile.avatar"/>
                <div class="fcal_cal_info">
                    <h3>{{ calendar.author_profile.name }}</h3>
                    <p v-if="calendar.public_url && calendar.visibility == 'public'" class="fcal_profile_link">
                        <a target="_blank" rel="noopener" :href="calendar.public_url">{{calendar.public_url}}</a>
                    </p>
                    <p class="fcal_profile_link" v-else-if="!calendar.public_url"><span style="cursor: pointer;" @click="goToIntegrationSetting">Enable Landing Page</span></p>
                </div>
            </div>
            <div class="fcal_cal_actions">
                <el-button class="fcal_plain_btn" @click="goToIntegrationSetting">
                    <el-icon><Setting /></el-icon> Settings
                </el-button>

                <el-button
                    @click="isNewBookingOpen = true"
                    class="fcal_primary_btn2">
                    <span>+</span> Create New Event
                </el-button>

                <el-dropdown @command="handleCommand" popper-class="fcal_select" trigger="click">
                    <span class="el-dropdown-link">
                        <el-icon><MoreFilled /></el-icon>
                    </span>
                    <template #dropdown>
                        <el-dropdown-menu>
                            <el-dropdown-item command="delete"><el-icon><Delete /></el-icon> Delete</el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>
        </div>
        <div class="fcal_cal_slots">
            <div class="fcal_cal_slot" v-for="(slot, slotIndex) in calendar.slots" :key="slot.id">
                <each-slot @slotDeleted="slotDeleted(slotIndex)" :slot="slot" :calendarId="calendar.id" :publicUrl="calendarPublicUrl"/>
            </div>
        </div>
        <el-dialog v-model="showSettings" title="Calendar Settings">
            <calendar-settings @calendarUpdated="() => {showSettings = false}" :calendar="calendar" />
        </el-dialog>

        <el-drawer
            v-model="isNewBookingOpen"
            title="Create New Booking Type"
            :zIndex="999"
            modal-class="fcal_drawer"
        >
            <div class="fcal_create_new_booking_type_drawer">
                <el-button @click="createOneToOneSlot">
                    <div class="icons-wrap">
                        <el-icon><User /></el-icon>
                        <el-icon><Right /></el-icon>
                        <div class="icons">
                            <el-icon><User /></el-icon>
                        </div>
                    </div>
                    <div class="content">
                        <h3>One-to-One</h3>
                        <h4><strong>One host</strong> <span>with</span> <strong>One invitee</strong></h4>
                        <p>Good for: coffee chats, 1:1 interviews, etc.</p>
                        <el-icon class="icon-right"><Right /></el-icon>
                    </div>
                </el-button>
                <el-button @click="createGroupSlot">
                    <div class="icons-wrap">
                        <el-icon><User /></el-icon>
                        <el-icon><Right /></el-icon>
                        <div class="icons">
                            <el-icon><User /></el-icon>
                            <el-icon><User /></el-icon>
                        </div>
                    </div>
                    <div class="content">
                        <h3>Group</h3>
                        <h4><strong>One host</strong> <span>with</span> <strong>Group of invitees</strong></h4>
                        <p>Good for: webinars, online classes, etc.</p>
                        <el-icon class="icon-right"><Right /></el-icon>
                    </div>
                </el-button>
            </div>
        </el-drawer>
    </div>
</template>

<script type="text/babel">
import EachSlot from "./EachSlot.vue";
import { Setting, User, Right, MoreFilled, Delete } from '@element-plus/icons-vue';
import CalendarSettings from "./CalendarSettings.vue";

export default {
    name: 'CalendarEventBlock',
    props: ['calendar'],
    components: {
        EachSlot,
        Setting,
        User,
        Right,
        CalendarSettings,
        MoreFilled,
        Delete
    },
    data() {
        return {
            showSettings: false,
            isNewBookingOpen: false
        }
    },
    computed: {
        calendarPublicUrl() {
            if (this.calendar.visibility == 'public' && this.calendar.public_url) {
                return this.calendar.public_url;
            }
            return '';
        }
    },
    methods: {
        slotDeleted(slotIndex) {
            this.calendar.slots.splice(slotIndex, 1);
        },
        goToIntegrationSetting() {
            this.$router.push({
                name: 'calendar_settings',
                params: {id: this.calendar.id}
            })
        },
        createOneToOneSlot() {
            this.$router.push({
                name: 'create_slot_event',
                params: {calendar_id: this.calendar.id, event_type: 'single'}
            })
        },
        createGroupSlot() {
            this.$router.push({
                name: 'create_slot_event',
                params: {calendar_id: this.calendar.id, event_type: 'group'}
            })
        },
        handleCommand(command) {
            if (command == 'delete') {
                this.$confirm('Are you sure you want to delete this calendar? All the associate bookings and data will be deleted', 'Delete Calendar', {
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    type: 'warning'
                }).then(() => {
                    this.$del('calendars/' + this.calendar.id)
                        .then(response => {
                            this.$handleSuccess(response);
                            this.$emit('fetchCalendar')
                        })
                        .catch(errors => {
                            this.$handleError(errors);
                        });
                })
                return;
            }
        }
    }
}
</script>
