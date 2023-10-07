<template>
    <div class="fcal_calendar_settings">
        <div class="fcal_settings_header">
            <h2>Remote Calendar Sync Settings</h2>
            <p>Set the calendars to check for conflicts to prevent double bookings and add events to your remote
                calendar.</p>
        </div>
        <el-skeleton :rows="4" animated v-if="loading"/>
        <div v-else class="fcal_calendar_body">
            <div class="fcal_hightlight_box fcal_create_event_selector">
                <el-row :gutter="30">
                    <el-col :md="16" :xs="24">
                        <h3>Create events on</h3>
                        <p>Select remote calendar in where to add new events to when you're booked.</p>
                    </el-col>
                    <el-col :md="8" :xs="24">
                        <pre>{{ insertableCalendars }}</pre>
                    </el-col>
                </el-row>
            </div>
            <div>

                <pre>{{ providers }}</pre>
                <pre>{{ insertableCalendars }}</pre>
                <pre>{{ feeds }}</pre>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import each from 'lodash/each';
import isEmpty from 'lodash/isEmpty';

export default {
    name: 'RemoteCalendarsSettings',
    props: ['calendar'],
    data() {
        return {
            loading: false,
            providers: {},
            feeds: []
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
                            id: item.id,
                            driver: feed.driver,
                            calendar_identifier: feed.identifier,
                            title: item.title
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
