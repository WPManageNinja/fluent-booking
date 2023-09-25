<template>
    <div class="fcal_schedule_event_infos">
        <div class="fcal_schedule_event_infos_body">
            <div class="fcal_schedule_details_header">
                <h1 class="fcal_header_title">
                    Meeting Activities
                </h1>
            </div>
            <div v-if="loading" class="fcal_loading">
                <el-skeleton :rows="5" animated />
            </div>
            <div v-else class="fcal_booking_activities_list">
                <div v-if="activities.length" v-for="activity in activities" :key="activity.id" class="fcal_booking_activity">
                    <el-icon class="fcal_activity_complete_icon"><Check /></el-icon>

                    <div class="fcal_booking_activity_time">
                        {{ toCurrentTimezone(activity.created_at, 'DD MMM YYYY, hh:mma') }}
                    </div>
                    <div class="fcal_booking_activity_content">
                        <div class="fcal_booking_activity_title">
                            {{ activity.title }}
                        </div>
                        <div class="fcal_booking_activity_description" v-html="activity.description"></div>
                    </div>
                </div>
                <div v-else class="fcal_no_activities">
                    <p>No activities has been recorded for this booking</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import { Check } from '@element-plus/icons-vue';

export default {
    name: 'BookingActivities',
    props: ['event_id'],
    components: {
        Check
    },
    watch: {
        event_id() {
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
            this.$get(`schedules/3/activities`)
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

