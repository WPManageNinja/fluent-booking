<template>
    <div :class="'fcal_spoot_status_' + spot.status" class="fcal_spot_wrapper">
        <div @click="showDetails()" class="fcal_spot_line">
            <div class="fcal_spot_color">
                <span :class="'fcal_'+spot.status"></span>
            </div>
            <div class="fcal_spot_timing">
                {{ toCurrentTimezone(spot.start_time, 'hh:mma') }} - {{ toCurrentTimezone(spot.end_time, 'hh:mma') }}
                <div v-if="spot.happening_status" class="fcal_spot_happening">
                    <span :class="'fcal_'+spot.happening_status">{{ getTextFromSlug(spot.happening_status) }}</span>
                </div>
            </div>
            <div class="fcal_spot_desc">
                <div class="fcal_spot_title">
                    {{ spot.first_name }} {{ spot.last_name }}
                </div>
                <div class="fcal_spot_desc_text">
                    Event: <b>{{ spot.slot.title }}</b>
                </div>
            </div>
            <div class="fcal_spot_actions">
                <el-button text>Details</el-button>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'ScheduleSpot',
    props: ['spot'],
    $emits: ['showDetails'],
    data() {
        return {
            updating: false
        }
    },
    methods: {
        showDetails() {
            this.$emit('showDetails', this.spot);
        }
    }
}
</script>
