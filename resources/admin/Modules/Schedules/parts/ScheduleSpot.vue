<template>
    <div :class="'fcal_spot_wrapper fcal_spoot_status_' + spot[0].status">
        <div @click="showDetails()" class="fcal_spot_line">
            <div class="fcal_spot_timing">
                <div class="fcal_spot_color">
                    <span :class="'fcal_'+spot[0].status"></span>
                </div>
                {{ formattedTimeRange }}
                <div v-if="spot[0].happening_status" class="fcal_spot_happening">
                    <span :class="'fcal_'+spot[0].happening_status">
                        {{ getTextFromSlug(spot[0].happening_status) }}
                    </span>
                </div>
            </div>
            <div class="fcal_spot_desc">
                <h3 class="fcal_spot_title">
                    {{ spotTitle }}
                </h3>
                <h3 class="fcal_spot_desc_text">
                    Event: <b>{{ spot[0].slot.title }}</b>
                </h3>
            </div>
            <div class="fcal_spot_meeting_with">
                <h3 v-if="multi_host">
                    Host: <b>{{spot[0].author.name}}</b>
                </h3>
            </div>
            <div class="fcal_spot_actions">
                <el-button class="fcal_plain_btn">
                    View Details
                </el-button>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'ScheduleSpot',
    props: ['spot', 'multi_host'],
    methods: {
        showDetails() {
            this.$emit('showDetails', this.spot);
        }
    },
    computed: {
        formattedTimeRange() {
            const startTime = this.toCurrentTimezone(this.spot[0].start_time, 'hh:mma');
            const endTime = this.toCurrentTimezone(this.spot[0].end_time, 'hh:mma');
            return `${startTime} - ${endTime}`;
        },
        spotTitle() {
            const eventType = this.spot[0].slot?.event_type;
            const guestName = this.spot[0].first_name + ' ' + this.spot[0].last_name;
            if (eventType === 'group') {
                const booked = this.spot.length;
                const totalSpots = this.spot[0].slot.max_book_per_slot;
                return booked + ' of ' + totalSpots + ' guests with you';
            }
            return guestName;
        }
    }
}
</script>
