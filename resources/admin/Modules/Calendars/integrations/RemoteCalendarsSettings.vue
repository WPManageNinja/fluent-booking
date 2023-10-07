<template>
    <div class="fcal_calendar_settings">
        <div class="fcal_settings_header">
            <h2>Remote Calendar Sync Settings</h2>
            <p>Set the calendars to check for conflicts to prevent double bookings and add events to your remote
                calendar.</p>
        </div>
        <el-skeleton :rows="4" animated v-if="loading"/>
        <div v-else class="fcal_calendar_body">
            <div v-if="insertableCalendars.length" class="fcal_hightlight_box fcal_create_event_selector">
                <el-row align="middle" :gutter="30">
                    <el-col :md="14" :xs="24">
                        <h3>Create events on</h3>
                        <p>Select remote calendar in where to add new events to when you're booked.</p>
                    </el-col>
                    <el-col :md="10" :xs="24">
                        <el-select clearable v-model="settings.create_event_calendar" value-key="id" placeholder="Select a Remote Calendar">
                            <el-option
                                v-for="item in insertableCalendars"
                                :key="item.details.id"
                                :label="item.title"
                                :value="item.details"
                            />
                        </el-select>
                    </el-col>
                </el-row>
            </div>

            <div class="fcal_remote_calendars_blocks">
                <div class="fcal_each_calendar_block" v-for="feed in feeds" :key="db_id">
                    <remote-calendar :feed="feed" :driver="providers[feed.driver]" :calendar="calendar" />
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import each from 'lodash/each';
import isEmpty from 'lodash/isEmpty';
import RemoteCalendar from './RemoteCalendar';

export default {
    name: 'RemoteCalendarsSettings',
    props: ['calendar'],
    components: {
        RemoteCalendar
    },
    data() {
        return {
            loading: false,
            providers: {},
            feeds: [],
            settings: {
                create_event_calendar: ''
            }
        }
    },
    computed: {
        insertableCalendars() {
            if (isEmpty(this.feeds)) {
                return [];
            }
            const calendars = [];

            each(this.feeds, (feed) => {
                each(feed.remote_calendars, (item) => {
                    if (item.can_write === 'yes') {
                        calendars.push({
                            title: item.title + ' - ' + feed.driver + ' (' + feed.identifier + ')',
                            id: feed.db_id + '__||__' + item.id,
                            details: {
                                id: feed.db_id + '__||__' + item.id,
                                driver: feed.driver
                            }
                        })
                    }
                });
            });

            return calendars;
        }
    },
    methods: {
        getSettings() {
            this.loading = true;
            this.$get('calendars/' + this.calendar.id + '/integrations/remote-calendars')
                .then(response => {
                    this.providers = response.providers;
                    this.feeds = response.feeds;
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
        this.getSettings();
    }
}
</script>
