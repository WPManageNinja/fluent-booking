<template>
    <el-table :data="data" class="fcal_schedule_meeting_table">
        <el-table-column label="Invitees" width="200">
            <template #default="scope">
                <span class="invitees">{{ scope.row.invite }}</span>
            </template>
        </el-table-column>
        <el-table-column label="Event Name" width="260">
            <template #default="scope">
                <h4 class="event-name">
                    <span class="badge-color" style="background: #EF4444;"></span> {{ scope.row.event_name }}
                </h4>
            </template>
        </el-table-column>
        <el-table-column label="Date" width="220">
            <template #default="scope">
                <span class="date">{{ scope.row.date }}</span>
            </template>
        </el-table-column>
        <el-table-column label="Time" width="180">
            <template #default="scope">
                <span class="time">{{ scope.row.time }}</span>
            </template>
        </el-table-column>
        <el-table-column>
            <el-button class="fcal_plain_btn" @click="shodetails">View Details</el-button>
        </el-table-column>
    </el-table>
</template>

<script type="text/babel">
export default {
    name: 'ScheduleSpot',
    props: ['spot', 'spots', 'multi_host'],
    data() {
        return {
            updating: false,
            data: [
                {
                    id: 0,
                    invite: 'Alex Hamster',
                    event_name: 'Free Consultation',
                    date: 'Mar 30, 2023',
                    time: '09:00am - 09:30am'
                },
                {
                    id: 1,
                    invite: '1 of 2 Invitees',
                    event_name: 'Free Consultation',
                    date: 'Mar 30, 2023',
                    time: '09:00am - 09:30am'
                }
            ]
        }
    },
    methods: {
        shodetails() {
            this.$router.push({name: 'scheduled_event_details', params: {spot_id: 3}})
        }
    },
    computed: {
        // spotTitle() {
        //     const eventType = this.spot[0].slot?.event_type;
        //     const guestName = this.spot[0].first_name + ' ' + this.spot[0].last_name;
        //
        //     if (eventType === 'group') {
        //         const booked = this.spot.length;
        //         const totalSpots = this.spot[0].slot.max_book_per_slot;
        //         return booked + ' of ' + totalSpots + ' guests with you';
        //     }
        //     return guestName;
        // }
    }
}
</script>
