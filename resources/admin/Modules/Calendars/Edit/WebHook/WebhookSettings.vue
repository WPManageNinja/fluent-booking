<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_webhook_settings">
            <div class="fcal_create_calendar_form">
                <div class="fcal_create_calendar_form_header">
                    <h2>
                        <el-icon><Link/></el-icon>
                        {{ $t('Webhook Feeds') }}
                    </h2>

                    <div v-if="!disabled" class="fcal_header_action">
                        <el-button
                            v-if="editing_feed"
                            @click="backToHome()"
                            class="fcal_plain_btn">
                            <el-icon><Back/></el-icon>
                            {{ $t('Back') }}
                        </el-button>
                        <template v-else>
                            <el-button class="fcal_primary_btn2" @click="add">
                                <el-icon><Plus/></el-icon>
                                {{ $t('Add New Webhook') }}
                            </el-button>
                            <el-dropdown trigger="click" popper-class="fcal_select">
                                <span class="el-dropdown-link">
                                    <el-icon><MoreFilled/></el-icon>
                                </span>
                                <template #dropdown>
                                    <el-dropdown-menu>
                                        <el-dropdown-item
                                            @click="isCloneOpen = true">
                                            {{ $t('Clone from') }}
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </template>
                            </el-dropdown>
                        </template>
                    </div>
                </div>
            </div>

            <template v-if="!disabled">
                <div class="fcal_settings_body" v-if="!editing_feed">
                    <el-skeleton v-if="loading" animated :rows="6">
                    </el-skeleton>
                    <template v-else>
                        <div v-if="feeds.length" class="fcal_integration_items">
                            <div class="fcal_integration_item" v-for="feed in feeds" :key="feed.id">
                                <div class="fcal_card_wrap">
                                    <div class="fcal_card_item_details">
                                        <h3>{{ feed.settings.name }}</h3>
                                        <p class="request_url">{{ feed.settings.request_url }}</p>
                                        <ul class="event_triggers" v-if="feed.settings.event_triggers">
                                            <li v-for="(event, i) in feed.settings.event_triggers" :key="i"><i class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 stroke-[3px]" data-testid="start-icon"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg></i> {{ getEventName(event) }}</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="fcal_card_actions">
                                    <el-switch
                                        active-color="#306ae0"
                                        @change="handleActive(feed)"
                                        v-model="feed.settings.enabled"
                                    ></el-switch>
                                    <el-button
                                        class="fcal_plain_btn"
                                        @click="edit(feed)"
                                    >
                                        <el-icon>
                                            <Edit/>
                                        </el-icon>
                                    </el-button>
                                    <el-popconfirm
                                        :title="$t('Are you sure to delete this webhook?')"
                                        popper-class="fcal_confirm_dialog"
                                        :confirm-button-text="$t('Yes')"
                                        :cancel-button-text="$t('No')"
                                        confirm-button-type="danger"
                                        @confirm="deleteWebhook(feed.id)"
                                    >
                                        <template #reference>
                                            <el-button type="danger" class="fcal_danger_btn">
                                                <el-icon>
                                                    <Delete/>
                                                </el-icon>
                                            </el-button>
                                        </template>
                                    </el-popconfirm>
                                </div>
                            </div>
                        </div>
                        <p v-else class="fcal_empty_inline_text">
                            {{ $t("You don't have any feeds configured. Let's go") }}
                            <el-link :underline="true" @click="add">{{ $t('create one!') }}</el-link>
                        </p>
                    </template>
                </div>
                <div class="fcal_settings_body" v-else>
                    <Editor
                        :smart_codes="smart_codes"
                        :editing_feed="editing_feed"
                        :calendar_event="calendar_event"
                        :event_triggers="event_triggers"
                        @backToWebhook="backToWebhook"
                    />
                </div>
            </template>
            <ProNotice v-else/>
        </div>
        <CloneDrawer
            v-if="isCloneOpen"
            :isOpen="isCloneOpen"
            :eventId="calendar_event.id"
            :eventLists="event_lists"
            :saving="saving"
            :title="$t('Clone Webhook Settings')"
            :label="$t('Select Calendar Event')"
            :placeholder="$t('Select Event')"
            :helpText="$t('CalendarEvent/select_webhook_settings')"
            :buttonLabel="$t('Clone Settings')"
            @clone="cloneWebhook"
            @update:isOpen="isCloneOpen = $event"
        />
    </div>
</template>

<script>
import { Link, Back, MoreFilled } from '@element-plus/icons-vue';
import Editor from "./Editor";
import ProNotice from '@/Components/Common/ProNotice.vue';
import CloneDrawer from '@/Components/Common/CloneDrawer.vue';

export default {
    name: "WebhookSettings",
    props: ['calendar_event', 'disabled', 'event_lists'],
    components: {
        Editor,
        Link,
        Back,
        MoreFilled,
        ProNotice,
        CloneDrawer
    },
    data() {
        return {
            loading: false,
            saving: false,
            feeds: [],
            event_triggers: [],
            editing_feed: null,

            isCloneOpen: false,
            selected_id: null,
            selectedIndex: null,
            webhook: {
                name: '',
                calendar_id: '',
                event_id: '',
            },
            calendars: [],
            slots: [],
            show_edit: false,
            editing_item: null,
            smart_codes: {
                texts: {},
                html: {}
            }
        }
    },
    computed: {
        tableData() {
            return this.webhooks;
        }
    },
    methods: {
        backToWebhook() {
            this.show_edit = false;
            this.editing_feed = null;
            this.getFeeds();
        },
        getEventName(name) {
            const eventNames = {
                'after_booking_scheduled': 'Booking Confirmed',
                'booking_schedule_completed': 'Booking Completed',
                'booking_schedule_cancelled': 'Booking Cancelled',
                'after_booking_rescheduled': 'Booking Rescheduled',
                'booking_schedule_rejected': 'Booking Rejected'
            };
            return this.$t(eventNames[name]);
        },
        backToHome() {
            this.editing_feed = null;
            this.getFeeds();
        },
        edit(feed) {
            this.editing_feed = feed;
            this.show_edit = true;
        },
        getFeeds() {
            this.loading = true;
            this.$get(`calendars/${this.calendar_event.calendar_id}/events/${this.calendar_event.id}/webhooks`, {
                calendar_id: this.calendar_event.calendar_id,
                with: ['smart_codes']
            })
                .then(response => {
                    this.event_triggers = response.event_triggers;
                    this.feeds = response.feeds;
                    this.smart_codes = response.smart_codes;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        add() {
            this.editing_feed = {
                id: 0,
                settings: {
                    name: '',
                    request_url: '',
                    enabled: false,
                    custom_header_keys: [false],
                    custom_header_values: [false],
                    event_triggers: ['after_booking_scheduled'],
                    fields: [
                        {
                            key: '',
                            value: ''
                        }
                    ],
                    request_body: 'all_data',
                    request_format: 'JSON',
                    request_headers: [{
                        key: '',
                        value: ''
                    }],
                    request_method: 'POST',
                    with_header: 'nop'
                }
            }
        },
        handleActive(row) {
            let data = {
                webhook: {
                    id: row.id,
                    settings: row.settings
                },
                calendar_id: this.calendar_event.calendar_id
            };
            this.$post(`calendars/${this.calendar_event.calendar_id}/events/${this.calendar_event.id}/webhooks`, data)
                .then(response => {
                    this.$handleSuccess(response.message);
                })
                .catch(error => {
                    this.$handleError(error);
                });
        },
        deleteWebhook(id) {
            this.loading = true;
            this.$del(`calendars/${this.calendar_event.calendar_id}/events/${this.calendar_event.id}/webhooks/${id}`, {
                calendar_id: this.calendar_event.calendar_id
            })
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.getFeeds();
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        cloneWebhook(eventId) {
            this.loading = true;
            this.$post(`calendars/${this.calendar_event.calendar_id}/events/${this.calendar_event.id}/webhooks/clone`,{
                    calendar_id: this.calendar_event.calendar_id,
                    from_event_id: eventId
                })
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.getFeeds();
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
    beforeMount() {
        if (!this.disabled) {
            this.getFeeds();
        }
    }
}
</script>

