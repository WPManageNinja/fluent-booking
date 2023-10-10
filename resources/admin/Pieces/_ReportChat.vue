<template>
    <VueApexCharts :options="options" height="375" :series="options.series" />
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
                series: [{
                    name: 'sales',
                    data: [30,40,35,50,49,60,70,91,125]
                }],
                xaxis: {
                    categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul', 'Aug','Sep']
                }
            }
        }
    }
}
</script>
