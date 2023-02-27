<template>
    <div class="fcal_booking_activities">
        <div v-if="loading" class="fcal_loading">
            <el-skeleton :rows="5" animated />
        </div>
        <div v-else class="fcal_booking_activities_list">
            <div v-if="activities.length" v-for="activity in activities" :key="activity.id" class="fcal_booking_activity">
                <div class="fcal_booking_activity_time">
                    {{ toCurrentTimezone(activity.created_at, 'DD MMM YYYY, hh:mma') }}
                </div>
                <div class="fcal_booking_activity_content">
                    <div class="fcal_booking_activity_title">
                        {{ activity.title }}
                    </div>
                    <div class="fcal_booking_activity_description">
                        {{ activity.description }}
                    </div>
                </div>
            </div>
            <div v-else class="fcal_no_activities">
                <p>No activities has been recorded for this booking</p>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'BookingActivities',
    props: ['booking_id'],
    watch: {
        booking_id() {
            this.fetchActivities();
        }
    },
    data() {
        return {
            activities: [],
            loading: false
        }
    },
    methods: {
        fetchActivities() {
            this.loading = true;
            this.$get(`schedules/${this.booking_id}/activities`)
                .then(response => {
                    this.activities = response.activities;
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                })
        }
    },
    mounted() {
        this.fetchActivities();
    }
}
</script>

<style lang="scss">
.fcal_booking_activities_list {
    padding: 0;
    .fcal_booking_activity {
        display: flex;
        align-items: flex-start;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        flex-direction: column;
        align-content: flex-start;
        .fcal_booking_activity_time {
            font-size: 12px;
            color: #999;
        }
        .fcal_booking_activity_content {
            flex: 1;
            .fcal_booking_activity_title {
                font-size: 16px;
                font-weight: 500;
                margin-bottom: 5px;
            }
            .fcal_booking_activity_description {
                font-size: 14px;
                color: #626262;
            }
        }
    }
    .fcal_no_activities {
        padding: 20px 0;
        text-align: center;
        font-size: 16px;
        color: #999;
    }
}
</style>
