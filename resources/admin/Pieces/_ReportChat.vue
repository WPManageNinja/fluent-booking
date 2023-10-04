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
                    categories: [1991,1992,1993,1994,1995,1996,1997, 1998,1999]
                }
            }
        }
    }
}
</script>
