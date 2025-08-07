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
                        <tr v-for="date in overrides">
                            <td>
                                <span class="date">{{date.label}}</span>
                            </td>
                            <td style="text-align: right;">
                                <ul class="fcal_slots_list">
                                    <li v-for="time in date.slots">
                                        {{ overriddenDate(time) }}
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-else>{{ $t('No specific date overrides found for this schedule') }}</p>
            </div>
        </div>
    </div>
</template>

<script>
import TimezoneIcon from "../../../Components/Icons/TimezoneIcon";
import each from 'lodash/each';

export default {
    name: "_ExistingSchedule.vue",
    props: ['existing_schedules', 'timezone', 'availability_id'],
    components: {
        TimezoneIcon
    },
    computed: {
        dateOverridesNotEmpty() {
            if (this.existing_schedules.date_overrides) {
                return Object.keys(this.existing_schedules?.date_overrides).length;
            }
            return false;
        },
        overrides() {
            const formatted = [];
            let firstDay = false;
            let dateGroups = [];
            const overrides = this.existing_schedules.date_overrides;
            each(overrides, (slot, day) => {
                if(!firstDay) {
                    firstDay = day;
                }
                dateGroups.push(day);

                let nextDay = new Date(day);
                nextDay.setDate(nextDay.getDate() + 1);
                nextDay = nextDay.toISOString().slice(0, 10);

                if(JSON.stringify(overrides[day]) === JSON.stringify(overrides[nextDay])) {
                    return;
                }

                let label = this.toDateFormat(day, 'D MMM, YYYY ');
                if(firstDay != day) {
                    label = this.toDateFormat(firstDay, 'D MMM ') + ' - ' + label;
                }

                formatted.push({
                    dates: dateGroups,
                    label: label,
                    slots: overrides[day]
                });
                firstDay = false;
                dateGroups = [];
            });

            return formatted;
        },
        overriddenDate() {
            return (slot) => {
                if (slot.start === "00:00" && slot.end === "00:00") {
                    return this.$t('Unavailable');
                }
                return this.toDateFormat('2022-12-12 ' + slot.start, 'HH:mma') + ' - ' + this.toDateFormat('2022-12-12 ' + slot.end, 'HH:mma');
            }
        },
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
