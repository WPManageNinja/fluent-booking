<template>
    <div class="fluentcrm_databox fcal_crm_table_block">
            <h3>Bookings (Fluent Booking)</h3>
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
                        <p>Scheduled Meetings <b>Fluent Booking</b> No scheduled meetings found for this subscriber</p>
                    </template>
                </el-table>
                <div class="fcal_crm_pagination">
                    <pagination popper-class="fcal_select" :pagination="pagination" @fetch="fetch" />
                </div>
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
            provider_key: 'fluent_booking',
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
            this.$get(`subscribers/${this.subscriber.id}/form-submissions`, {
                provider: this.provider_key,
                page: this.pagination.current_page,
                per_page: this.pagination.per_page
            })
                .then(response => {
                    this.meetings = response.submissions.data;
                    this.pagination.total = parseInt(response.submissions.total);

                    if (response.submissions.columns_config) {
                        this.columnsConfig = response.submissions.columns_config;
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
        this.fetch();
    }
}
</script>

<style scoped lang="scss">
.fcal_crm_table_block {
    margin-bottom: 30px;
    border-bottom: 2px solid #607D8B;
    padding-bottom: 30px;

    &:last-child {
        border-bottom: none;
    }

}

.fcal_crm_pagination {
    display: flex;
    justify-content: flex-end;
    margin-top: 15px;
}
</style>
