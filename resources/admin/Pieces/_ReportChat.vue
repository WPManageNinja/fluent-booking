<template>
    <div class="fcal_dashboard_chat fcal_dashboard_box">
        <div class="fcal_section_header">
            <div class="fcal_title">
                <h3>{{ $t('Booking Trends') }}</h3>
            </div>
            <div class="fcal_actions">
                <el-date-picker
                    v-model="filterChartsDate"
                    type="daterange"
                    unlink-panels
                    clearable
                    range-separator="-"
                    :start-placeholder="$t('Start date')"
                    :end-placeholder="$t('End date')"
                    :shortcuts="shortcuts"
                    :placeholder="$t('Select Date')"
                    popper-class="fcal_daterange_popover"
                    format="YYYY/MM/DD"
                    value-format="YYYY-MM-DD"
                    :disabled-date="disabledDate"
                    @change="fetchGraphReports"
                />
            </div>
        </div>
        <el-skeleton v-if="loading" animated />
        <div v-else>
            <VueApexCharts :options="options" height="375" :series="options.series" />
        </div>
    </div>
</template>

<script>
import VueApexCharts from "vue3-apexcharts";

export default {
    name: "_ReportChat",
    props: ['data', 'categories'],
    components: {
        VueApexCharts
    },
    data() {
        return {
            loading: false,
            options: {
                chart: {
                    type: 'area',
                    toolbar: {
                        show: false,
                    }
                },
                dataLabels: {
                    enabled: false,
                },
                grid: {
                    borderColor: '#F3F3F3',
                    yaxis: {
                        lines: {
                            show: true
                        }
                    },
                },
                series: [
                    {
                        name: this.$t('Booked'),
                        data: []
                    },
                    {
                        name: this.$t('Completed'),
                        data: []
                    },
                    {
                        name: this.$t('Cancelled'),
                        data: []
                    }
                ],
                xaxis: {
                    categories: []
                }
            },
            filterChartsDate: '',
            shortcuts: [
                {
                    text: this.$t('Last week'),
                    value: () => {
                        const end = new Date()
                        const start = new Date()
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 7)
                        return [start, end]
                    },
                },
                {
                    text: this.$t('Last month'),
                    value: () => {
                        const end = new Date()
                        const start = new Date()
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 30)
                        return [start, end]
                    },
                }
            ],
        }
    },
    methods: {
        disabledDate(time) {
            return time.getTime() > Date.now();
        },
        fetchGraphReports() {
            this.loading = true;
            this.$get('reports/graph-reports', {
                date_range: this.filterChartsDate
            })
                .then(response => {
                    const { booked_stats, completed_stats, cancelled_stats } = response;

                    // Helper function to extract data from an object and return as arrays
                    const extractData = (stats) => Object.entries(stats).map(([key, value]) => ({ key, value }));

                    // Extract data and categories for booked, completed, and cancelled stats
                    const bookedData    = extractData(booked_stats);
                    const completedData = extractData(completed_stats);
                    const cancelledData = extractData(cancelled_stats);

                    // Update the options object
                    this.options.xaxis.categories = bookedData.map((item) => item.key);
                    this.options.series[0].data = bookedData.map((item) => item.value);
                    this.options.series[1].data = completedData.map((item) => item.value);
                    this.options.series[2].data = cancelledData.map((item) => item.value);
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
        this.fetchGraphReports();
    }
}
</script>
