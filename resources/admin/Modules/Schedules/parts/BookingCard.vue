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
                <div class="fcal_spot_desc_sub_info">
                    <div v-if="booking.happening_status" class="fcal_spot_happening">
                        <span :class="'fcal_'+booking.happening_status">
                            {{ getTextFromSlug(booking.happening_status) }}
                        </span>
                    </div>

                    <span class="fcal_spot_period_status" :class="booking.status=='no_show'?'no_show':''" v-else-if="currentStatus">
                        {{ $t(currentStatus) }}
                    </span>
                    
                    <span class="fcal_spot_source" :class="'fcal_spot_source_' + booking.source" v-if="booking.source != 'web'">
                        {{ booking.source }}
                    </span>

                    <p v-if="booking.payment_status" class="fcal_spot_payment_status" :class="booking.payment_status">{{ $t(booking.payment_status) }}</p>
                </div>
            </div>
            <div class="fcal_spot_actions">
                <el-button class="fcal_plain_btn">
                    {{ $t('View Details') }}
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
            const formatStartTime = this.toCurrentTimezone(this.booking.start_time, this.appVars.time_format);
            const formatEndTime   = this.toCurrentTimezone(this.booking.end_time, this.appVars.time_format);

            if(this.period == 'latest_bookings') {
                return `${this.toCurrentTimezone(this.booking.start_time, 'D MMM, YYYY')} <br /> ${formatStartTime} - ${formatEndTime}`;
            }

            return `${formatStartTime} - ${formatEndTime}`;
        },
        spotTitle() {
            const eventType = this.booking.event_type;
            const guestName = this.booking.first_name + ' ' + this.booking.last_name;
            if (eventType === 'group') {
                const booked = this.booking.booked_count;
                return booked + ' ' + this.$t('guests with') + ' ' + this.booking.author.name + ' ' + this.$t('as group booking type');
            }
            if(this.showing_id) {
                return guestName;
            }
            return '<b>' + this.booking?.calendar_event?.title +'</b> ' + this.$t('meeting between') + ' ' + guestName + ' & '+ this.booking.author.name;
        },
        currentStatus() {
            const statusLabels = {
                scheduled: this.$t('Upcoming'),
                completed: this.$t('Completed'),
                cancelled: this.$t('Cancelled'),
                pending: this.$t('Pending'),
                no_show: this.$t('No Show')
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
