<template>
    <div class="fcal_event_block">
        <div class="fcal_cal_header fcal_section_header">
            <div class="fcal_cal_title">
                <img :src="calendar.author_profile.avatar"/>
                <div class="fcal_cal_info">
                    <h3>{{ calendar.author_profile.name }}</h3>
                    <p v-if="calendar.public_url" class="fcal_profile_link"><a target="_blank" rel="noopener" :href="calendar.public_url">{{calendar.public_url}}</a></p>
                </div>
            </div>
            <div class="fcal_cal_actions">
                <el-button @click="$router.push({ name: 'create_slot_event', params: { calendar_id: calendar.id } })"
                           type="primary">+ New Booking Type
                </el-button>
            </div>
        </div>
        <div class="fcal_cal_slots">
            <el-row :gutter="30">
                <el-col v-for="(slot, slotIndex) in calendar.slots" :key="slot.id" :sm="12" :md="8">
                    <each-slot @slotDeleted="slotDeleted(slotIndex)" :slot="slot" />
                </el-col>
            </el-row>
        </div>
    </div>
</template>

<script type="text/babel">
import EachSlot from "./EachSlot.vue";
export default {
    name: 'CalendarEventBlock',
    props: ['calendar'],
    components: {
        EachSlot
    },
    methods: {
        slotDeleted(slotIndex) {
            this.calendar.slots.splice(slotIndex, 1);
        }
    }
}
</script>
