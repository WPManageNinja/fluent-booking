<template>
    <el-table :data="schedules" class="fcal_schedule_meeting_table">
        <el-table-column label="Invitees">
            <template #default="scope">
                <span class="invitees">{{ scope.row.invitees }}</span>
            </template>
        </el-table-column>
        <el-table-column label="Event Name">
            <template #default="scope">
                <h4 class="event-name">
                    <span class="badge-color" style="background: #EF4444;"></span> {{ scope.row.slot.title }}
                </h4>
            </template>
        </el-table-column>
        <el-table-column label="Date">
            <template #default="scope">
                <span class="date">{{ scope.row.date }}</span>
            </template>
        </el-table-column>
        <el-table-column label="Time">
            <template #default="scope">
                <span class="time">{{ scope.row.time }}</span>
            </template>
        </el-table-column>
        <el-table-column>
            <template #default="scope">
                <el-button class="fcal_plain_btn" @click="showDetails(scope.row.event_id)">View Details</el-button>
            </template>
        </el-table-column>
    </el-table>
</template>

<script type="text/babel">
import each from 'lodash/each';
import ScheduleSpotDetails from './ScheduleSpotDetails.vue';
export default {
    name: 'ScheduleSpots',
    props: ['spots'],
    components: {
        ScheduleSpotDetails
    },
    data() {
        return {
            schedules: [],
        }
    },
    watch: {
        spots() {
            this.formattedSchedules();
        }
    },
    methods: {
        showDetails(spotId) {
            this.$router.push({name: 'scheduled_event_details', params: { spot_id: spotId }});
        },
        inviteeInfo(spot) {
            const eventType = spot[0].slot?.event_type;
            const inviteeName = spot[0].first_name + ' ' + spot[0].last_name;
            if (eventType === 'group') {
                const booked = spot.length;
                const totalSpots = spot[0].slot.max_book_per_slot;
                return booked + ' of ' + totalSpots + ' guests with you';
            }
            return inviteeName;
        },
        formattedSchedules() {
            const items = [];
            each(this.spots, (spot) => {
                const startTime = spot[0].start_time;
                spot[0].date = this.toCurrentTimezone(startTime, 'DD/MM/YYYY');
                spot[0].time = this.toCurrentTimezone(startTime, 'hh:mm A');
                spot[0].invitees = this.inviteeInfo(spot);
                items.push(spot[0]);
            });
            this.schedules = items;
        }
    },
}
</script>
