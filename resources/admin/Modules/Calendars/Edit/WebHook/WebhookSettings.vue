<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_webhook_settings">
            <div class="fcal_create_calendar_form">
                <div class="fcal_create_calendar_form_header">
                    <h2>
                        <el-icon>
                            <Link/>
                        </el-icon>
                        {{ $t('Webhook Feeds') }}
                    </h2>

                    <el-button
                        v-if="editing_feed"
                        @click="backToHome()"
                        class="fcal_primary_btn2"
                    >
                        <el-icon>
                            <Back/>
                        </el-icon>
                        {{ $t('Back') }}
                    </el-button>
                    <el-button v-else class="fcal_primary_btn2" @click="add">
                        <el-icon>
                            <Plus/>
                        </el-icon>
                        {{ $t('Add New Webhook') }}
                    </el-button>
                </div>
            </div>

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
                    <p v-else>
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
        </div>
    </div>
</template>

<script>
import {Plus, Link, Edit, Delete, Back} from '@element-plus/icons-vue';
import Editor from "./Editor";

export default {
    name: "WebhookSettings",
    props: ['calendar_event'],
    components: {
        Editor,
        Plus,
        Link,
        Edit,
        Delete,
        Back
    },
    data() {
        return {
            loading: false,
            feeds: [],
            event_triggers: [],
            editing_feed: null,

            isDrawerOpen: false,
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
    methods: {
        getFeeds() {
            this.loading = true;
            this.$get(`calendars/${this.calendar_event.calendar_id}/slots/${this.calendar_event.id}/webhooks`, {
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
        backToHome() {
            this.editing_feed = null;
            this.getFeeds();
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
        edit(feed) {
            this.editing_feed = feed;
            this.show_edit = true;
        },
        handleActive(row) {
            let data = {
                webhook: {
                    id: row.id,
                    settings: row.settings
                }
            };

            this.$post(`calendars/${this.calendar_event.calendar_id}/slots/${this.calendar_event.id}/webhooks`, data)
                .then(response => {
                    this.$handleSuccess(response.message);
                })
                .catch(error => {
                    this.$handleError(error);
                });
        },
        deleteWebhook(id) {
            this.loading = true;
            this.$del(`calendars/${this.calendar_event.calendar_id}/slots/${this.calendar_event.id}/webhooks/${id}`)
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
        backToWebhook() {
            this.show_edit = false;
            this.editing_feed = null;
            this.getFeeds();
        },
        getEventName(name) {
            if (name == 'after_booking_scheduled') {
                return this.$t('Booking Confirmed');
            }
            if (name == 'booking_schedule_completed') {
                return this.$t('Booking Completed');
            }
            if (name == 'booking_schedule_cancelled') {
                return this.$t('Booking Cancelled');
            }
        }
    },
    computed: {
        tableData() {
            return this.webhooks;
        }
    },
    beforeMount() {
        this.getFeeds();
    },
    mounted() {

    }
}
</script>

<style scoped>

</style>
