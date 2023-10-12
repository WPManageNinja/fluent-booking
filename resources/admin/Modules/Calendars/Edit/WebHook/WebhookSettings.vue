<template>

    <div class="fcal_webhook_settings">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header">
                <h2>
                    <el-icon>
                        <Link/>
                    </el-icon>
                    Webhook Feeds
                </h2>

                <el-button
                    v-if="editing_feed"
                    @click="backToHome()"
                    class="fcal_primary_btn2"
                >
                    <el-icon>
                        <Back/>
                    </el-icon>
                    Back
                </el-button>
                <el-button v-else class="fcal_primary_btn2" @click="add">
                    <el-icon>
                        <Plus/>
                    </el-icon>
                    Add New Webhook
                </el-button>
            </div>
        </div>

        <div class="fcal_settings_body" v-if="!editing_feed">
            <el-skeleton v-if="loading" animated :rows="6">
            </el-skeleton>
            <template v-else>
                <el-table :data="feeds" stripe>
                    <template #empty>
                        You don't have any feeds configured. Let's go
                        <el-link :underline="true" @click="add">create one!</el-link>
                    </template>


                    <el-table-column width="70">
                        <template #default="scope">
                            <el-switch
                                active-color="#13ce66"
                                @change="handleActive(scope.row)"
                                v-model="scope.row.settings.enabled"
                            ></el-switch>
                        </template>
                    </el-table-column>

                    <el-table-column
                        width="200"
                        label="Name">
                        <template #default="scope">
                            {{ scope.row.settings.name }}
                        </template>
                    </el-table-column>

                    <el-table-column
                        :label="('WebHook URL')">
                        <template #default="scope">
                            {{ scope.row.settings.request_url }}
                        </template>
                    </el-table-column>
                    <el-table-column width="160" label="Actions" class-name="action-buttons">
                        <template #default="scope">

                            <el-button
                                class="fcal_primary_btn"
                                @click="edit(scope.row)"
                            >
                                <el-icon>
                                    <Edit/>
                                </el-icon>
                            </el-button>
                            <el-popconfirm
                                title="Are you sure to delete this webhook?"
                                popper-class="fcal_confirm_dialog"
                                confirm-button-type="danger"
                                @confirm="deleteWebhook(scope.row.id)"
                            >
                                <template #reference>
                                    <el-button type="danger" class="fcal_danger_btn">
                                        <el-icon>
                                            <Delete/>
                                        </el-icon>
                                    </el-button>
                                </template>
                            </el-popconfirm>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
        </div>

        <div class="fcal_settings_body" v-else>
            <Editor
                :editing_feed="editing_feed"
                :calendar_event="calendar_event"
                :request_headers="request_headers"
                :event_triggers="event_triggers"
                @backToWebhook="backToWebhook"
            />
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
            request_headers: [],
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
        }
    },
    methods: {
        getFeeds() {
            this.loading = true;
            this.$get(`calendars/${this.calendar_event.calendar_id}/slots/${this.calendar_event.id}/webhooks`)
                .then(response => {
                    this.request_headers = response.request_headers;
                    this.event_triggers = response.event_triggers;
                    this.feeds = response.feeds;
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
