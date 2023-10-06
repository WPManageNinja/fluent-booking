<template>
    <div class="fcal_webhook_settings">
        <div class="fcal_settings_header">
            <h1>
                Webhook Settings
            </h1>
            <div class="fcal_settings_header_action">
                <el-button class="fcal_primary_btn2" @click="isDrawerOpen = true">
                    <el-icon><Plus /></el-icon> Add New Webhook
                </el-button>

            </div>
        </div>

        <div v-loading="loading" class="fcal_settings_body">
            <el-table :data="tableData" style="width: 100%">
                <el-table-column type="expand">
                    <template #default="props">
                        <div m="4">
                            <p m="t-0 b-2">State: {{ props.row.state }}</p>
                            <p m="t-0 b-2">City: {{ props.row.city }}</p>
                            <p m="t-0 b-2">Address: {{ props.row.address }}</p>
                            <p m="t-0 b-2">Zip: {{ props.row.zip }}</p>
                            <h3>Family</h3>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="Date" prop="date" />
                <el-table-column label="Name" prop="name" />
            </el-table>
        </div>

        <el-drawer
            v-model="isDrawerOpen"
            modal-class="fcal_drawer"
            title="Create New Webhook"
            direction="rtl">
            <template #default>
                <el-form :data="webhook" label-position="top">
                    <el-form-item label="Name">
                        <el-input
                            v-model="webhook.name"
                        />
                    </el-form-item>
<!--                    <el-form-item label="Calendar">-->
<!--                        <el-select-->
<!--                            v-model="webhook.calendar_id"-->
<!--                            placeholder="Select"-->
<!--                            popper-class="fcal_select"-->
<!--                            @change="getSlots"-->
<!--                            >-->
<!--                            <el-option-->
<!--                                v-for="calendar in calendars"-->
<!--                                :key="calendar.id"-->
<!--                                :label="calendar.title"-->
<!--                                :value="calendar.id"-->
<!--                            />-->
<!--                        </el-select>-->
<!--                    </el-form-item>-->
<!--                    <el-form-item label="Slot">-->
<!--                        <el-select-->
<!--                            v-model="webhook.slot_id"-->
<!--                            placeholder="Select"-->
<!--                            popper-class="fcal_select"-->
<!--                            >-->
<!--                            <el-option-->
<!--                                v-for="slot in slots"-->
<!--                                :key="slot.id"-->
<!--                                :label="slot.title"-->
<!--                                :value="slot.id"-->
<!--                            />-->
<!--                        </el-select>-->
<!--                    </el-form-item>-->
                </el-form>
            </template>
            <template #footer>
                <div style="flex: auto">
                    <el-button @click="isDrawerOpen = false">Cancel</el-button>
                    <el-button type="primary" @click="store">Create</el-button>
                </div>
            </template>
        </el-drawer>
    </div>
</template>

<script>
import { Plus } from '@element-plus/icons-vue';

export default {
    name: "WebhookSettings",
    components: {
        Plus
    },
    data() {
        return {
            loading: false,
            tableData: [
                {
                    date: '2016-05-03',
                    name: 'Tom',
                    state: 'California',
                    city: 'San Francisco',
                    address: '3650 21st St, San Francisco',
                    zip: 'CA 94114'
                },
                {
                    date: '2016-05-02',
                    name: 'Tom',
                    state: 'California',
                    city: 'San Francisco',
                    address: '3650 21st St, San Francisco',
                    zip: 'CA 94114'
                }
            ],
            isDrawerOpen: false,
            webhook: {
                name: '',
                calendar_id: 1,
                slot_id: 1,
            },
            calendars: [],
            slots: []
        }
    },
    methods: {
        store() {
            this.loading = true;
            this.$post('webhooks', this.webhook)
                .then(response => {
                    console.log('webhook response -> ', response);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });

        },
        // getCalendars() {
        //     this.loading = true;
        //     this.$get('calendars')
        //         .then(response => {
        //             this.calendars = response.calendars.data;
        //             this.slots = response.calendars.data.slots;
        //         })
        //         .catch(errors => {
        //             this.$handleError(errors);
        //         })
        //         .finally(() => {
        //             this.loading = false;
        //         });
        // },
        // getSlots() {
        //     this.loading = true;
        //     this.$get('calendars/'+this.webhook.calendar_id)
        //         .then(response => {
        //             this.slots = response.calendar.slots
        //         })
        //         .catch(errors => {
        //             this.$handleError(errors);
        //         })
        //         .finally(() => {
        //             this.loading = false;
        //         });
        // },
    },
    mounted() {

        // this.getCalendars();
    }
}
</script>

<style scoped>

</style>