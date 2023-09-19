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
                    @click="$router.push({ name: 'create_slot_event', params: { calendar_id: calendar.id } })"
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
    </div>
</template>

<script type="text/babel">
import EachSlot from "./EachSlot.vue";
import { Setting } from '@element-plus/icons-vue';
import CalendarSettings from "./CalendarSettings.vue";
export default {
    name: 'CalendarEventBlock',
    props: ['calendar'],
    components: {
        EachSlot,
        Setting,
        CalendarSettings
    },
    data() {
        return {
            showSettings: false
        }
    },
    methods: {
        slotDeleted(slotIndex) {
            this.calendar.slots.splice(slotIndex, 1);
        }
    }
}
</script>
