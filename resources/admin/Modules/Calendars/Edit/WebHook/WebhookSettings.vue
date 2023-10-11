<template>

    <div class="fcal_webhook_settings">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header">
                <h2><el-icon><Link /></el-icon> Webhook Feeds </h2>

                <el-button
                    v-if="show_edit"
                    @click="backToHome()"
                    class="fcal_primary_btn2"
                >
                    <el-icon><Back /></el-icon> Back
                </el-button>
                <el-button v-else class="fcal_primary_btn2" @click="add">
                    <el-icon><Plus /></el-icon> Add New Webhook
                </el-button>
            </div>
        </div>

        <div class="fcal_settings_body" v-if="!show_edit">
            <el-skeleton :loading="loading" animated :rows="6">
                <el-table :data="tableData" stripe>
                    <template #empty>
                        You don't have any feeds configured. Let's go
                        <el-link :underline="true" @click="add">create one!</el-link>
                    </template>


                    <el-table-column width="70">
                        <template #default="scope">
                            <el-switch
                                active-color="#13ce66"
                                @click="handleActive(scope.row)"
                                v-model="scope.row.enabled"
                            ></el-switch>
                        </template>
                    </el-table-column>

                    <el-table-column
                        prop="value.name"
                        label="Name">
                    </el-table-column>

                    <el-table-column
                        prop="value.request_url"
                        :label="('WebHook URL')">
                    </el-table-column>

                    <el-table-column width="160" label="Actions" class-name="action-buttons">
                        <template #default="scope">

                            <el-button
                                class="fcal_primary_btn"
                                @click="edit(scope.$index)"
                            >
                                <el-icon><Edit /></el-icon>
                            </el-button>
                            <el-popconfirm
                                title="Are you sure to delete this webhook?"
                                popper-class="fcal_confirm_dialog"
                                confirm-button-type="danger"
                                @confirm="deleteWebhook(scope.row.id)"
                            >
                                <template #reference>
                                    <el-button type="danger" class="fcal_danger_btn">
                                        <el-icon><Delete /></el-icon>
                                    </el-button>
                                </template>
                            </el-popconfirm>
                        </template>
                    </el-table-column>
                </el-table>
            </el-skeleton>
        </div>

        <div class="fcal_settings_body">
            <Editor
                v-if="show_edit"
                :edit_item="editing_item"
                :request_headers="request_headers"
                :event_triggers="event_triggers"
                :event_id="event_id"
                :selected_id="selected_id"
                :setSelectedId="setSelectedId"
                :selected_index="selectedIndex"
                @backToWebhook="backToWebhook"
            />

        </div>
    </div>
</template>

<script>
import { Plus, Link, Edit, Delete, Back } from '@element-plus/icons-vue';
import Editor from "./Editor";

export default {
    name: "WebhookSettings",
    props:['event_id', 'calendar_id'],
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
            webhooks: [],
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
            request_headers: [],
            event_triggers: [],
        }
    },
    methods: {
        setSelectedId(id) {
            this.selected_id = id;
        },
        backToHome() {
            this.getFeeds(true);
            this.selected_id = null;
            this.selectedIndex = 0;
            this.show_edit = false;
        },
        add() {
            this.selectedIndex = this.webhooks.length;
            this.selected_id = null;
            this.editing_item = false;
            this.show_edit = true;
        },
        edit(index) {
            let webhook = this.webhooks[index];
            this.selectedIndex = 0;
            this.selected_id = webhook.id;
            this.editing_item =  webhook.value;
            this.show_edit = true;
        },
        handleActive(row) {
            console.log(row);
            row.value.enabled = row.enabled;
            let data = {
                id: row.id,
                webhook: row.value
            };

            this.$put('webhooks',data)
                .then(response => {
                    this.$handleSuccess(response.message);
                })
                .catch(error => {
                    this.$handleError(error);
                });
        },
        deleteWebhook(id) {
            this.loading = true;
            this.$del('webhooks/'+id)
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
        getFeeds(onlyFeeds = null) {
            const slotID = this.event_id;
            this.$get('webhooks',{
                event_id: slotID
            })
                .then(response => {
                    this.request_headers = response.request_headers;
                    this.event_triggers = response.event_triggers;
                    this.webhooks = response.webhooks;

                    // this.request_headers.push({
                    //     'label': 'Add Custom Header',
                    //     'value': '__webhook_custom_header__'
                    // });
                })
                .catch(e => console.log(e))
                .finally(r => this.loading = false);
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
