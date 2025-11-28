<template>
    <div class="fcal_calendar_settings">
        <div class="fcal_settings_header">
            <div class="fcal_settings_head">
                <h2>{{ $t('Remote Calendar Sync Settings') }}</h2>
                <p>{{ $t('set_calendars_to_check_for_conflicts') }}</p>
            </div>
            <div v-if="!isEmpty(feeds)" class="fcal_settings_actions">
                <el-popover placement="bottom-end" width="300" trigger="click">
                    <template #reference>
                        <el-button class="fcal_plain_btn">
                            <el-icon>
                                <Plus/>
                            </el-icon>
                            {{ $t('Add') }}
                        </el-button>
                    </template>
                    <div class="fcal_all_driver_actions">
                        <div v-for="driver in configuredProviders" :key="driver.key" class="fcal_driver_action">
                            <a href="#" class="fcal_remote_cal_link" @click.prevent="initCalDav(driver)" v-if="driver.is_caldav">
                                <img :src="driver.icon"/>
                                <span>{{ driver.btn_text }}</span>
                            </a>
                            <a v-else class="fcal_remote_cal_link" :href="driver.auth_url">
                                <img :src="driver.icon"/>
                                <span>
                                    {{ driver.btn_text }}
                                </span>
                            </a>
                        </div>
                    </div>
                </el-popover>
            </div>
        </div>
        <template v-if="!disabled">
            <el-skeleton :rows="4" animated v-if="loading"/>
            <div v-else class="fcal_calendar_body">
                <template v-if="feeds.length">
                    <div v-if="insertableCalendars.length" class="fcal_hightlight_box fcal_create_event_selector">
                        <el-row align="middle" :gutter="30">
                            <el-col :md="14" :xs="24">
                                <h3>{{ $t('Create events on') }}</h3>
                                <p>{{ $t("Select remote calendar in where to add new events to when you're booked.") }}</p>
                            </el-col>
                            <el-col :md="10" :xs="24">
                                <el-select :disabled="saving" v-loading="saving" @change="updateSettings()" clearable
                                           v-model="settings.remote_calendar_config" value-key="id"
                                           :placeholder="$t('Select a Remote Calendar')"
                                           placement="bottom"
                                           popper-class="fcal_select">
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
                        <div class="fcal_each_calendar_block" v-for="feed in feeds" :key="feed.db_id">
                            <remote-calendar @refetch="getSettings()" :feed="feed" :driver="providers[feed.driver]" :calendar="calendar"/>
                        </div>
                    </div>
                </template>
    
                <div v-else-if="!isEmpty(providers)">
                    <h3>{{ $t('RemoteCalendarsSettings/connect_calendar_desc') }}</h3>
                    <div v-for="driver in providers" :key="driver.key" class="">
                        <div class="fcal_remote_calendar_block fcal_promt_box">
                            <div class="fcal_remote_header">
                                <div class="fcal_driver_brand">
                                    <img :src="driver.icon"/>
                                    <div class="fcal_driver_heading">
                                        <h3>{{ driver.title }}</h3>
                                        <p>{{ driver.subtitle }}</p>
                                    </div>
                                </div>
                                <div class="fcal_driver_action">
                                    <template v-if="driver.is_global_configured">
                                        <el-button @click="initCalDav(driver)" type="primary" v-if="driver.is_caldav">{{ driver.btn_text }}</el-button>
                                        <a v-else :href="driver.auth_url" class="el-button el-button--primary el-button--small">
                                            {{ driver.btn_text }}
                                        </a>
                                    </template>
                                    <a v-else :href="driver.global_config_url"
                                       class="el-button el-button--primary el-button--small">
                                        Configure {{ driver.title }} API
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <ProNotice v-else/>
        <el-dialog :close-on-click-modal="false" v-model="showCalDav" width="50%" :title="'Connect ' + calDavDriver.title">
            <cal-dav-auth v-if="showCalDav" :calendar="calendar" :driver="calDavDriver"/>
        </el-dialog>
    </div>
</template>

<script>
import each from 'lodash/each';
import isEmpty from 'lodash/isEmpty';
import RemoteCalendar from './RemoteCalendar';
import CalDavAuth from './CalDavAuth';
import ProNotice from '@/Components/Common/ProNotice.vue';

export default {
    name: 'RemoteCalendarsSettings',
    props: ['calendar', 'disabled'],
    components: {
    RemoteCalendar,
    CalDavAuth,
    ProNotice
},
    data() {
        return {
            loading: false,
            saving: false,
            providers: {},
            feeds: [],
            settings: {
                remote_calendar_config: ''
            },
            showCalDav: false,
            calDavDriver: {}
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
        },
        configuredProviders() {
            if (isEmpty(this.providers)) {
                return [];
            }

            const validProviders = [];
            each(this.providers, (provider, key) => {
                if (provider.is_global_configured) {
                    validProviders.push(provider)
                }
            });

            return validProviders;
        }
    },
    methods: {
        getSettings() {
            this.loading = true;
            this.$get('calendars/' + this.calendar.id + '/integrations/remote-calendars', {
                calendar_id : this.calendar.id
            })
                .then(response => {
                    this.providers = response.providers;
                    this.feeds = response.feeds;
                    this.settings.remote_calendar_config = response.settings;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        updateSettings() {
            this.saving = true;
            this.$post('calendars/' + this.calendar.id + '/integrations/remote-calendars/sync-settings', {
                calendar_id : this.calendar.id,
                remote_calendar_config: this.settings.remote_calendar_config
            })
                .then(response => {
                    this.$notify.success(response.message);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        isEmpty,
        initCalDav(driver) {
            this.calDavDriver = driver;
            this.showCalDav = true;
        }
    },
    mounted() {
        if (!this.disabled) {
            this.getSettings();
        }
    }
}
</script>
