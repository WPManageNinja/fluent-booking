<template>
    <div class="fcal_section fcal_section_narrow">
        <div v-if="hasSupport('multi_users')" class="fcal_section_header">
            <div class="fcal_title">
                <h3>Booking Calendars</h3>
            </div>
            <div class="fcal_actions">
                <el-button @click="$router.push({name: 'create_calendar'})" class="fcal_primary_btn">
                    <span>+</span> Create New Host
                </el-button>
            </div>
        </div>
        <div v-loading="loading" class="fcal_section_body">
            <div class="fcal_calendars_wrap">
                <div v-for="calendar in calendars" :key="calendar.id" class="fcal_each_cal">
                    <calendar-event-block :calendar="calendar" />
                </div>
            </div>
            <template v-if="loading">
                <el-skeleton :animated="true" :rows="1" />
                <el-skeleton :animated="true" :rows="4" />
            </template>

            <div class="fcal_right fcal_tm20">
                <pagination :pagination="pagination" @fetch="getCalendars"/>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import Pagination from "../../Pieces/Pagination.vue";
import CalendarEventBlock from "./parts/CalendarEventBlock.vue";

export default {
    name: 'AllCalendars',
    components: {
        Pagination,
        CalendarEventBlock
    },
    data() {
        return {
            calendars: [],
            loading: false,
            pagination: {
                total: 0,
                per_page: 10,
                current_page: 1
            },
        }
    },
    methods: {
        getCalendars() {
            this.loading = true;
            this.$get('calendars', {
                per_page: this.pagination.per_page,
                page: this.pagination.current_page
            })
                .then(response => {
                    this.calendars = response.calendars.data;
                    this.pagination.total = response.calendars.total;
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
        this.$changeTitle('Calendars');
        this.getCalendars();
    }
}
</script>
