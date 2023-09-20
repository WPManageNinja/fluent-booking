<template>
    <div class="fcal_event_block">
        <div class="fcal_cal_header">
            <div class="fcal_cal_title">
                <img :src="calendar.author_profile.avatar"/>
                <div class="fcal_cal_info">
                    <h3>{{ calendar.author_profile.name }}</h3>
                    <p v-if="calendar.public_url && calendar.visibility == 'public'" class="fcal_profile_link"><a target="_blank" rel="noopener" :href="calendar.public_url">{{calendar.public_url}}</a></p>
                </div>
            </div>
            <div class="fcal_cal_actions">
                <el-button @click="showSettings = true" class="fcal_plain_btn">
                    <el-icon><Setting /></el-icon> Settings
                </el-button>
                <el-button
                    @click="isNewBookingOpen = true"
                    class="fcal_primary_btn2">
                    <span>+</span> Create New Booking Type
                </el-button>
            </div>
        </div>
        <div class="fcal_cal_slots">
            <div class="fcal_cal_slot" v-for="(slot, slotIndex) in calendar.slots" :key="slot.id">
                <each-slot @slotDeleted="slotDeleted(slotIndex)" :slot="slot" />
            </div>
        </div>
        <el-dialog v-model="showSettings" title="Calendar Settings">
            <calendar-settings @calendarUpdated="() => { showSettings = false; }" :calendar="calendar" />
        </el-dialog>

        <el-drawer
            v-model="isNewBookingOpen"
            title="Create New Booking Type"
            :zIndex="99999"
            modal-class="fcal_drawer"
        >
            <div class="fcal_create_new_booking_type_drawer">
                <el-button @click="$router.push({ name: 'create_slot_event', params: { calendar_id: calendar.id, event_type: 'one-on-one' } })">
                    <div class="icons-wrap">
                        <el-icon><User /></el-icon>
                        <el-icon><Right /></el-icon>
                        <div class="icons">
                            <el-icon><User /></el-icon>
                        </div>
                    </div>
                    <div class="content">
                        <h3>One-on-One</h3>
                        <h4><strong>One host</strong> <span>with</span> <strong>One invitee</strong></h4>
                        <p>Good for: coffee chats, 1:1 interviews, etc.</p>
                        <el-icon class="icon-right"><Right /></el-icon>
                    </div>
                </el-button>
                <el-button @click="$router.push({ name: 'create_slot_event', params: { calendar_id: calendar.id, event_type: 'group' } })">
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
                        <p>Good for: coffee chats, 1:1 interviews, etc.</p>
                        <el-icon class="icon-right"><Right /></el-icon>
                    </div>
                </el-button>
            </div>
        </el-drawer>
    </div>
</template>

<script type="text/babel">
import EachSlot from "./EachSlot.vue";
import { Setting, User, Right } from '@element-plus/icons-vue';
import CalendarSettings from "./CalendarSettings.vue";
export default {
    name: 'CalendarEventBlock',
    props: ['calendar'],
    components: {
        EachSlot,
        Setting,
        User,
        Right,
        CalendarSettings
    },
    data() {
        return {
            showSettings: false,
            isNewBookingOpen: false
        }
    },
    methods: {
        slotDeleted(slotIndex) {
            this.calendar.slots.splice(slotIndex, 1);
        }
    }
}
</script>
