<template>
    <div class="fcal_schedule_event_infos">
        <div class="fcal_schedule_details_header">
            <h1 class="fcal_header_title">
                {{ $t('Meeting Activities') }}
            </h1>
        </div>

        <div class="fcal_schedule_event_infos_body">
            <div v-if="!loading" class="fcal_booking_activities_list">
                <div v-if="activities.length" class="fcal_booking_activity" v-for="activity in activities" :key="activity.id">
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
                    <p>{{ $t('No activities has been recorded for this booking') }}</p>
                </div>
            </div>
            <div v-else>
                <el-skeleton :row="5" animated/>
            </div>
        </div>
    </div>
</template>

<script>
import { Check } from '@element-plus/icons-vue';

export default {
    name: "ReportsActivities",
    components: {
        Check
    },
    data() {
        return {
            loading: false,
            activities: []
        }
    },
    methods: {
        fetchActivities() {
            this.loading = true;
            this.$get('reports/activities')
                .then(response => {
                    this.activities = response.activities;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    mounted() {
        this.fetchActivities();
    }
}
</script>
