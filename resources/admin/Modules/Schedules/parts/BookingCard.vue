<template>
    <div :class="'fcal_spot_wrapper fcal_spoot_status_' + booking.status">
        <div @click="showDetails()" class="fcal_spot_line">
            <div class="fcal_spot_timing">
                <div v-if="booking.slot" class="fcal_spot_color">
                    <span :style="{background: booking.slot.color_schema}"></span>
                </div>
                <span style="line-height: 120%;" v-html="formattedTimeRange"></span>
            </div>
            <div class="fcal_spot_desc">
                <h3 v-html="spotTitle" class="fcal_spot_title"></h3>
                <div v-if="booking.happening_status" class="fcal_spothappening">
                    <span :class="'fcal'+booking.happening_status">
                        {{ getTextFromSlug(booking.happening_status) }}
                    </span>
                </div>
                <div class="fcal_spot_desc_sub_info">
                    <span class="fcal_spot_period_status" v-if="currentStatus">
                        {{ currentStatus }}
                    </span>
                    <p v-if="booking.payment_status" class="fcal_spot_payment_status" :class="booking.payment_status">{{ booking.payment_status }} | <span v-html="booking.currency"></span>{{ orderPrice }}</p>
                </div>
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
    props: ['booking', 'multi_host', 'showing_id', 'period'],
    data() {
        return {
            booking_id: this.$route.query.booking_id,
        }
    },
    methods: {
        showDetails() {
            this.$emit('showDetails', this.booking);
        }
    },
    computed: {
        formattedTimeRange() {
            const startTime = this.toCurrentTimezone(this.booking.start_time, 'hh:mma');
            const endTime = this.toCurrentTimezone(this.booking.end_time, 'hh:mma');

            if(this.period == 'latest_bookings') {
                return `${this.toCurrentTimezone(this.booking.start_time, 'D MMM, YYYY')} <br /> ${startTime} - ${endTime}`;
            }

            return `${startTime} - ${endTime}`;
        },
        spotTitle() {
            const eventType = this.booking.event_type;
            const guestName = this.booking.first_name + ' ' + this.booking.last_name;
            if (eventType === 'group') {
                const booked = this.booking.booked_count;
                return booked + ' guests with '+ this.booking.author.name + 'as group booking type';
            }
            if(this.showing_id) {
                return guestName;
            }
            return '<b>' + this.booking?.slot.title +'</b> meeting between ' + guestName + ' & '+ this.booking.author.name;
        },
        currentStatus() {
            const statusLabels = {
                scheduled: 'Upcoming',
                completed: 'Completed',
                cancelled: 'Cancelled',
                pending: 'Pending'
            };
            if (this.period === 'latest_bookings' || this.period === 'all') {
                return statusLabels[this.booking.status] || '';
            }
            return '';
        },
        orderPrice() {
            if(!this.booking.payment_order) {
                return '';
            }
            const price = Math.floor(this.booking.payment_order?.total_amount/100);
            if (!price) {
                return '';
            }
            return price;
        }
    }
}
</script>
