<template>
    <div :class="'fcal_spot_wrapper fcal_spoot_status_' + booking.status">
        <div @click="showDetails()" class="fcal_spot_line">
            <div class="fcal_spot_timing">
                <div v-if="booking.slot" class="fcal_spot_color">
                    <span :style="{background: booking.slot.color_schema}"></span>
                </div>
                {{ formattedTimeRange }}
                <div v-if="booking.happening_status" class="fcal_spot_happening">
                    <span :class="'fcal_'+booking.happening_status">
                        {{ getTextFromSlug(booking.happening_status) }}
                    </span>
                </div>
            </div>
            <div class="fcal_spot_desc">
                <h3 class="fcal_spot_title">
                    {{ spotTitle }}
                </h3>
                <h3 v-if="booking.slot" class="fcal_spot_desc_text">
                    Event: <b>{{ booking.slot.title }}</b>
                </h3>
            </div>
            <div class="fcal_spot_meeting_with">
                <h3 v-if="multi_host">
                    Host: <b>{{booking.author.name}}</b>
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
    name: 'BookingCard',
    props: ['booking', 'multi_host'],
    methods: {
        showDetails() {
            this.$emit('showDetails', this.booking);
        }
    },
    computed: {
        formattedTimeRange() {
            const startTime = this.toCurrentTimezone(this.booking.start_time, 'hh:mma');
            const endTime = this.toCurrentTimezone(this.booking.end_time, 'hh:mma');
            return `${startTime} - ${endTime}`;
        },
        spotTitle() {
            const eventType = this.booking.event_type;
            const guestName = this.booking.first_name + ' ' + this.booking.last_name;
            if (eventType === 'group') {
                const booked = this.booking.booked_count;
                const totalSpots = this.booking.slot.max_book_per_slot;
                return booked + ' of ' + totalSpots + ' guests with you';
            }
            return guestName;
        }
    }
}
</script>
