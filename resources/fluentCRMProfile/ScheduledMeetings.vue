<template>
    <div class="purchase_history_block">
        <h3 class="history_title">Title</h3>
        <div class="provider_data">
            <el-table :empty-text="$t('No Data Found')" v-loading="loading" border stripe :data="meetings">
                <el-table-column v-for="(column,columnKey) in table_columns" :key="columnKey"
                     :width="(columnsConfig[columnKey]) ? columnsConfig[columnKey].width : ''"
                     :label="(columnsConfig[columnKey] && columnsConfig[columnKey].label) ? columnsConfig[columnKey].label : ucFirst(columnKey)">
                >
                    <template slot-scope="scope">
                        <div v-html="scope.row[columnKey]"></div>
                    </template>
                </el-table-column>
                <template slot="empty">
                    <p>{{$t('Scheduled Meetings')}} <b>Fluent Booking</b> {{$t('no_scheduled_meetings_found_for_this_subscriber')}}</p>
                </template>
            </el-table>
            <pagination :pagination="pagination" @fetch="fetch" />
        </div>
    </div>
</template>
<script type="text/babel">
import Pagination from "../admin/Pieces/Pagination.vue";
export default {
    name: 'ScheduledMeetings',
    props: ['subscriber'],
    components: {
        Pagination
    },
    data() {
        return {
            loading: false,
            meetings: [],
            pagination: {
                per_page: 10,
                current_page: 1,
                total: 0
            },
            columnsConfig: {}
        }
    },
    computed: {
        table_columns() {
            let columns = [];
            if (this.meetings.length) {
                columns = this.meetings[0];
            }
            return columns;
        }
    },
    methods: {
        fetch() {
            this.loading = true;
            this.$get(`subscribers/${this.subscriber.id}/scheduled-meetings`, {
                page: this.pagination.current_page,
                per_page: this.pagination.per_page
            })
                .then(response => {
                    this.meetings = response.meetings.data;
                    this.pagination.total = parseInt(response.meetings.total);

                    if (response.meetings.columns_config) {
                        this.columnsConfig = response.meetings.columns_config;
                    }
                })
                .catch((errors) => {
                    console.log(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    mounted() {
        console.log("Yes.. ScheduledMeetings.vue is loadedn");
        this.fetch();
    }
}
</script>
