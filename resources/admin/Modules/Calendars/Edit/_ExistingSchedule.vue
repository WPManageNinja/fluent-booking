<template>
    <div class="fcal_availability_setting">
        <div class="fcal_weekly_schedule_wrap">
            <h2 class="fcal_availability_title">{{ $t('Weekly Hours') }}</h2>
            <div class="fcal_weekly_existing_schedule">
                <div class="fcal_weekly_existing_schedule_header">
                    <div class="fcal_timezone_text">
                        <el-icon><TimezoneIcon /></el-icon>
                        <p>{{ timezone }}</p>
                    </div>

                    <el-button class="fcal_plain_btn" @click="goToEdit">
                        <el-icon><Edit /></el-icon> {{ $t('Edit Availability') }}
                    </el-button>
                </div>
                <ul>
                    <li v-for="(schedule, i) in existing_schedules.weekly_schedules" :key="i">
                        <span class="day">{{ $t(i) }}</span>
                        <span class="date">
                            <span v-if="schedule.slots.length" v-for="(time, index) in schedule.slots" :key="index">
                                {{ time.start }} - {{ time.end }}
                            </span>
                            <span v-else class="unavailable">{{ $t('Unavailable') }}</span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="fcal_override_table">
            <h2 class="fcal_availability_title">{{ $t('Date Overrides') }}</h2>
            <div class="fcal_override_date">
                <table v-if="dateOverridesNotEmpty" class="fcal_table_compact fcal_table_stripe">
                    <tbody>
                        <tr v-for="(date, index) in existing_schedules.date_overrides" style="cursor: pointer;">
                            <td>
                                <span class="date">{{index}}</span>
                            </td>
                            <td style="text-align: right;">
                                <ul class="fcal_slots_list">
                                    <li v-for="time in date">
                                        {{toDateFormat('2022-12-12 ' + time.start, 'HH:mma')}} - {{toDateFormat('2022-12-12 ' + time.end, 'HH:mma')}}
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-else>{{ $t('No specific date overrides found for this schedule') }}</div>
            </div>
        </div>
    </div>
</template>

<script>
import TimezoneIcon from "../../../Components/Icons/TimezoneIcon";
import { Edit } from '@element-plus/icons-vue';

export default {
    name: "_ExistingSchedule.vue",
    props: ['existing_schedules', 'timezone', 'availability_id'],
    components: {
        Edit,
        TimezoneIcon
    },
    computed: {
        dateOverridesNotEmpty() {
            if (this.existing_schedules.date_overrides) {
                return Object.keys(this.existing_schedules?.date_overrides).length;
            }
            return false;
        }
    },
    methods: {
        goToEdit() {
            this.$router.push({
                name: 'availability_details',
                params: { schedule_id: this.availability_id }
            })
        }
    }
}
</script>
