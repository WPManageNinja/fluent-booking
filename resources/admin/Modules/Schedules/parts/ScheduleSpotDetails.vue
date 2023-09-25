<template>
    <div class="fcal_schedule_details">
        <div class="fcal_header">
            <router-link :to="{name: 'scheduled_events'}" class="fcal_back_btn">
                <el-icon><Back /></el-icon> Go Back
            </router-link>
            <h1>
                [Event Title]
            </h1>
        </div>

        <div class="fcal_schedule_details_body">
            <div class="fcal_schedule_details_sidebar">
                <div class="fcal_schedule_details_sidebar_inner" v-for="(schedules, scheduleDate) in formattedSchedules" :key="scheduleDate">
                    <h3>{{formattedDate(scheduleDate)}}</h3>

                    <ul>
                        <li
                            class="fcal_spot_line"
                            v-for="spot in schedules"
                            :key="spot[0].id"
                            :id="'spot-'+spot[0].id"
                            :class="$route.params.spot_id==spot[0].id ? 'is-active' : null"
                            @click="$router.push({name: 'scheduled_event_details', params: {spot_id: spot[0].id}})"
                        >
                            <span class="fcal_spot_timing">09:00am - 09:15am</span>
                            <span class="fcal_spot_meeting">1 of 1 guests with you</span>
                            <span class="fcal_spot_event">Free Consultation</span>
                        </li>
                    </ul>
                </div>

            </div>

            <div class="fcal_schedule_details_content">
                <div class="fcal_schedule_event_infos">
                    <div class="fcal_schedule_header_bar">
                        <p>15 minutes meeting with Juwel @ 20 Sep 2023, 09:15pm</p>

                        <el-dropdown trigger="click" popper-class="fcal_select">
                                <span class="el-dropdown-link">
                                     <el-icon><MoreFilled /></el-icon>
                                </span>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item><el-icon><Refresh /></el-icon> Reschedule</el-dropdown-item>
                                    <el-dropdown-item><el-icon><Close /></el-icon> Cancel</el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </div>
                    <div class="fcal_schedule_event_infos_body">
                        <div class="fcal_schedule_details_header">
                            <h1 class="fcal_header_title">
                                Event Information
                            </h1>
                        </div>

                        <div class="fcal_schedule_details_event">

                            <div class="fcal_schedule_details_event_item">
                                <h3>Meeting Host</h3>
                                <p>Marzan Parker</p>
                            </div>
                            <div class="fcal_schedule_details_event_item">
                                <h3>Event Title</h3>
                                <p>Free Consultatio</p>
                            </div>
                            <div class="fcal_schedule_details_event_item">
                                <h3>Event Duration</h3>
                                <p>15 Minutes</p>
                            </div>
                            <div class="fcal_schedule_details_event_item">
                                <h3>Location</h3>
                                <p>Free consultation</p>
                            </div>
                            <div class="fcal_schedule_details_event_item">
                                <h3>Status</h3>
                                <p>Scheduled</p>
                            </div>
                            <div class="fcal_schedule_details_event_item">
                                <h3>Booking URL</h3>
                                <p>https://convertleap.com/parker/15min</p>
                            </div>
                            <div class="fcal_schedule_details_event_item">
                                <h3>Booked From</h3>
                                <p><a href="#">Fluentform</a></p>
                            </div>
                            <div class="fcal_schedule_details_event_item">
                                <h3>Fluent CRM Profile</h3>
                                <p><a href="">View Profile</a></p>
                            </div>

                        </div>


                        <div class="fcal_schedule_details_event_additional fcal_schedule_details_event_item">
                            <h3>Additional Note <el-icon @click="isAdditionalNoteOpen=true"><EditPen /></el-icon></h3>
                            <p>N/A</p>
                            <div v-if="isAdditionalNoteOpen" class="fcal_schedule_additional_form">
                                <el-input
                                    type="textarea"
                                    placeholder="Additional Note"
                                />
                                <div class="action">
                                    <el-button class="fcal_plain_btn" @click="isAdditionalNoteOpen=false">Cancel</el-button>
                                    <el-button class="fcal_primary_btn">Update</el-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="fcal_schedule_event_infos">
                    <div class="fcal_schedule_event_infos_body">
                        <div class="fcal_schedule_details_header">
                            <h1 class="fcal_header_title">
                                Invitees Information
                            </h1>
                        </div>

                        <el-table :data="invitees">
                            <el-table-column label="Name" width="180">
                                <template #default="scope">
                                    {{ scope.row.name }}
                                </template>
                            </el-table-column>
                            <el-table-column label="Email" width="200">
                                <template #default="scope">
                                    {{ scope.row.email }}
                                </template>
                            </el-table-column>
                            <el-table-column label="Time Zone" width="150">
                                <template #default="scope">
                                    {{ scope.row.timezone }}
                                </template>
                            </el-table-column>
                            <el-table-column label="Booked AT" width="150">
                                <template #default="scope">
                                    {{ scope.row.booked_at }}
                                </template>
                            </el-table-column>
                            <el-table-column width="40">
                                <template #default="scope">
                                    <el-dropdown trigger="click" popper-class="fcal_select">
                                        <span class="el-dropdown-link">
                                             <el-icon><MoreFilled /></el-icon>
                                        </span>
                                        <template #dropdown>
                                            <el-dropdown-menu>
                                                <el-dropdown-item><el-icon><Refresh /></el-icon> Reschedule</el-dropdown-item>
                                                <el-dropdown-item><el-icon><Close /></el-icon> Cancel</el-dropdown-item>
                                            </el-dropdown-menu>
                                        </template>
                                    </el-dropdown>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                </div>

            </div>


            <div class="fcal_booking_activities">
                <BookingActivities />
            </div>
        </div>

    </div>
</template>

<script>
import { Back, MoreFilled, Refresh, Close, EditPen } from '@element-plus/icons-vue';
import each from "lodash/each";
import BookingActivities from "./_BookingActivities";
export default {
    name: "ScheduleSpotDetails",
    components: {
        BookingActivities,
        Back,
        MoreFilled,
        Refresh,
        Close,
        EditPen
    },
    data() {
        return {
            loading: false,
            schedules: '',
            schedule: '',
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 20
            },
            filters: {
                period: 'upcoming',
                author: 'all'
            },
            invitees: [
                {
                    name: 'Alex Hamster',
                    email: 'alex@hamster.com',
                    timezone: 'asia/dhaka',
                    booked_at: '10:00a.m'
                },
                {
                    name: 'Hamster',
                    email: 'alex@hamster.com',
                    timezone: 'asia/dhaka',
                    booked_at: '10:00a.m'
                },
                {
                    name: 'Alex Hamster',
                    email: 'alex@hamster.com',
                    timezone: 'asia/dhaka',
                    booked_at: '10:00a.m'
                }
            ],
            isAdditionalNoteOpen: false
        }
    },
    computed: {
        formattedDate() {
            return (date) => {
                if (this.isToday(date)) {
                    return 'Today';
                } else if (this.isYesterday(date)) {
                    return 'Yesterday';
                } else if (this.isTomorrow(date)) {
                    return 'Tomorrow';
                }
                return date;
            }
        },
        formattedSchedules() {
            const items = {};
            each(this.schedules, (schedule) => {
                const startTime = schedule[0].start_time;
                let date = this.toCurrentTimezone(startTime, 'MMMM D, YYYY');
                items[date] = items[date] || [];
                items[date].push(schedule);
            });

            const sortedSchedules = {};
            Object.keys(items)
                .sort((a, b) => new Date(a) - new Date(b))
                .forEach((date) => {
                    sortedSchedules[date] = items[date];
                });

            return sortedSchedules;
        }
    },
    methods: {
        fetchSchedules() {
            this.loading = true;
            this.spot_id = false;
            this.current_spot = null;

            this.$get('schedules', {
                per_page: this.pagination.per_page,
                page: this.pagination.current_page,
                filters: this.filters
            })
                .then(response => {
                    this.schedules = response.schedules.data;
                    // if (this.$route.params.slot_id) {
                        const dt = Object.entries(this.schedules);
                        // this.schedule = ;
                        // this.schedule = this.schedules[this.$route.params.slot_id];
                    // }
                    this.pagination.total = response.schedules.total;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        fetchSchedule() {
            if (!this.$route.params.spot_id) {
                return
            }
            this.$get('schedules/'+this.$route.params.spot_id+'/slot')
                .then(response => {

                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
    mounted() {
        this.fetchSchedules();
        this.fetchSchedule();
    }
}
</script>

<style scoped>

</style>