<template>
    <div class="fcal_schedlues">
        <div class="fcal_create_calendar fcal_section_narrow fcal_section">
            <div class="fcal_section_header">
                <div class="fcal_head_nav">
                    <ul class="fcal_secendary_nav_items">
                        <li @click="changePeriod('upcoming')" :class="{fcal_active : filters.period == 'upcoming' }">Upcoming</li>
                        <li @click="changePeriod('past')" :class="{fcal_active : filters.period == 'past' }">Past</li>
                    </ul>
                </div>
            </div>
            <div v-loading="loading" class="fcal_section_body">
                <div class="fcal_schedule_wrapper">
                    <div v-for="(schedules, scheduleDate) in formattedSchedules" :key="scheduleDate" class="fcal_schedule">
                        <div class="fcal_schedule_header">
                            <h3 class="fcal_schedule_data">{{scheduleDate}}</h3>
                        </div>
                        <div class="fcal_schedule_items">
                            <div v-for="spot in schedules" :key="spot.id" class="fcal_each_spot">
                                <schedule-spot :spot="spot" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="fcal_right fcal_tm20">
                    <pagination :pagination="pagination" @fetch="fetchSchedules"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import Pagination from "../../Pieces/Pagination.vue";
import ScheduleSpot from "./parts/ScheduleSpot.vue";
export default {
    name: 'AllSchedules',
    components: {
        ScheduleSpot,
        Pagination
    },
    data() {
        return {
            schedules: [],
            loading: true,
            filters: {
                period: 'upcoming',
                author: 'me'
            },
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 15
            }
        }
    },
    computed: {
        formattedSchedules() {
            const items = {};
            this.schedules.forEach(schedule => {
                const date = this.toCurrentTimezone(schedule.start_date, 'MMMM D, YYYY');
                if (!items[date]) {
                    items[date] = [];
                }
                items[date].push(schedule);
            });
            return items;
        }
    },
    methods: {
        fetchSchedules() {
            this.loading = true;
            this.$get('schedules', {
                per_page: this.pagination.per_page,
                page: this.pagination.current_page,
                filters: this.filters
            })
                .then(response => {
                    this.schedules = response.schedules.data;
                    this.pagination.total = response.schedules.total;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        changePeriod(period) {
            if(this.filters.period != period) {
                this.filters.period = period;
                this.fetchSchedules();
            }
        }
    },
    mounted() {
        this.$changeTitle('Schedules');
        this.fetchSchedules();
    }
}
</script>
