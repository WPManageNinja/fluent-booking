<template>
    <div class="fcal_calendar_settings">
        <div class="fcal_settings_header" v-if="show_edit">
            <div class="fcal_settings_head">
                <h2>
                    <el-breadcrumb separator="/">
                        <el-breadcrumb-item @click="showAll()">Integrations</el-breadcrumb-item>
                        <el-breadcrumb-item>Edit</el-breadcrumb-item>
                    </el-breadcrumb>
                </h2>
            </div>
            <div class="fcal_actions">
                <el-button
                    class="fcal_primary_btn2"
                    @click="showAll()"
                >
                    <el-icon>
                        <Back/>
                    </el-icon>
                    Back
                </el-button>
            </div>
        </div>

        <div v-else class="fcal_settings_header">
            <div class="fcal_settings_head">
                <h2>Integrations</h2>
                <p>Connect your favourite tools with your booking scheduled, completed or cancelled actions</p>
            </div>
            <div class="fcal_actions">
                <el-dropdown @command="addNewIntegration" :hide-on-click="false" trigger="click">
                    <el-button type="info">
                        {{ $t('Add New Integration') }}

                        <el-icon>
                            <ArrowDown/>
                        </el-icon>
                    </el-button>
                    <template #dropdown>
                        <el-dropdown-menu class="ff-dropdown-menu" slot="dropdown"
                                          style="max-height: 400px; overflow: auto">
                            <el-dropdown-item v-for="(integration,integration_name) in filteredList"
                                              :key="integration_name" :command="integration_name">
                                {{ integration.title }}
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>
        </div>

        <el-skeleton v-if="loading" :animated="true" :rows="5"/>
        <div v-else class="fcal_settings_body">
            <template v-if="!show_edit">
                <el-table v-if="!isEmpty(available_integrations)" :data="integrations">
                    <template #empty>
                        <div class="getting_started_message" style="padding-top: 16px; padding-bottom: 10px;">
                            <p>{{
                                    $t('You haven\'t added any integration feed yet. Add new integration to connect your favourite tools with your calendar')
                                }}</p>
                        </div>
                    </template>

                    <el-table-column width="180" :label="$t('Status')">
                        <template #default="scope">
                            <span class="mr-3" v-if="scope.row.enabled">{{ $t('Enabled') }}</span>
                            <span class="mr-3" v-else style="color:#fa3b3c;">{{ $t('Disabled') }}</span>
                            <el-switch
                                style="margin-left: 10px;"
                                active-color="#00b27f"
                                @change="handleActive(scope.row)"
                                v-model="scope.row.enabled">
                            </el-switch>
                        </template>
                    </el-table-column>

                    <el-table-column width="180" :label="$t('Integration')">
                        <template #default="scope">
                            <img style="max-height: 30px;" v-if="scope.row.provider_logo"
                                 class="general_integration_logo"
                                 :src="scope.row.provider_logo" :alt="scope.row.provider"/>
                            <span class="general_integration_name" v-else>{{ scope.row.provider }}</span>
                        </template>
                    </el-table-column>


                    <el-table-column :label="$t('Title')">
                        <template #default="scope">
                            {{ scope.row.name }}
                        </template>
                    </el-table-column>

                    <el-table-column width="160" :label="$t('Actions')" class-name="action-buttons">
                        <template #default="scope">
                            <el-button
                                size="small"
                                type="success"
                                @click="edit(scope.row)"
                            >
                                <el-icon>
                                    <Edit/>
                                </el-icon>
                            </el-button>
                            <el-popconfirm
                                title="Are you sure to delete this?"
                                popper-class="fcal_confirm_dialog"
                                confirm-button-type="danger"
                                @confirm="removeFeed(scope.row.id)"
                            >
                                <template #reference>
                                    <el-button type="danger" size="small" class="fcal_danger_btn">
                                        <el-icon>
                                            <Delete/>
                                        </el-icon>
                                    </el-button>
                                </template>
                            </el-popconfirm>
                        </template>
                    </el-table-column>
                </el-table>
                <h3 style="text-align: center; margin-top: 40px;" v-else>Your integrations will be available here. If
                    you use FluentCRM then you can manage that from here.</h3>
            </template>

            <IntegrationEditor
                v-else
                :editingIntegration="editingIntegration"
                :calendar_event="calendar_event"
                :inputs="fields"
                :has_pro="has_pro"
                @back="hideEditor"
            />
        </div>
    </div>
</template>

<script>
import isEmpty from 'lodash/isEmpty';
import remove from '@/Components/Common/ConfirmRemove.vue';
import Card from '@/Components/Common/Card/Card.vue';
import CardHead from '@/Components/Common/Card/CardHead.vue';
import CardBody from '@/Components/Common/Card/CardBody.vue';
import CardHeadGroup from '@/Components/Common/Card/CardHeadGroup.vue';
import BtnGroup from '@/Components/Common/BtnGroup/BtnGroup.vue';
import BtnGroupItem from '@/Components/Common/BtnGroup/BtnGroupItem.vue';
import IntegrationEditor from './IntegrationEditor.vue';

import {ArrowDown, Back, Edit, Delete} from '@element-plus/icons-vue';
import QuestionIcon from "@/Components/Icons/QuestionIcon.vue";
import EventIcon from "@/Components/Icons/EventIcon.vue";

export default {
    name: 'Integrations',
    props: ['calendar_id', 'event_id', 'calendar_event', 'has_pro', 'editorShortcodes'],
    components: {
        EventIcon,
        QuestionIcon,
        remove,
        Card,
        CardHead,
        CardBody,
        CardHeadGroup,
        BtnGroup,
        BtnGroupItem,
        ArrowDown,
        Back,
        IntegrationEditor,
        Edit,
        Delete
    },
    data() {
        return {
            search: '',
            loading: true,
            integrations: [],
            errors: new Errors,
            available_integrations: {},
            all_module_config_url: '',
            show_edit: false,
            integration_id: 0,
            integration_name: null,
            fields: [],
            editingIntegration: {
                integration_id: '',
                integration_name: ''
            },
        };
    },
    methods: {
        addNewIntegration(integration_name) {
            this.editingIntegration = {
                integration_id: 0,
                integration_name: integration_name
            }
            this.show_edit = true;
        },
        showAll() {
            this.show_edit = false;
            this.editingIntegration = {};
            this.getFeeds();
        },
        edit(integration) {
            this.editingIntegration = {
                integration_id: integration.id,
                integration_name: integration.provider
            }
            this.show_edit = true;
        },
        handleActive(row) {
            let data = {
                status: row.enabled,
            };

            this.errors.clear();

            this.saving = true;

            const url = 'calendars/' + this.calendar_id + '/slots/' + this.event_id + '/integrations/' + row.id;

            this.$post(url, data)
                .then(response => {
                    if (response.created) {
                        // this.$router.push({
                        //     name: 'allIntegrations',
                        // });
                    }
                    // this.$handleSuccess(response);
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => (this.saving = false));
        },
        removeFeed(feed_id) {
            this.$confirm('Are you sure to delete this Feed?')
                .then(_ => {
                    const url = 'calendars/' + this.calendar_id + '/slots/' + this.event_id + '/integrations/' + feed_id;
                    let data = {
                        integration_id: feed_id,
                    };
                    this.deleting = true;
                    this.$del(url, data)
                        .then(response => {
                            this.$handleSuccess(response.message);
                            this.getFeeds();
                        })
                        .catch(error => {
                            this.$handleError(error);
                        })
                        .finally(() => {
                            this.deleting = false;
                        });
                });
        },
        getFeeds() {
            this.loading = true;

            const url = 'calendars/' + this.calendar_id + '/slots/' + this.event_id + '/integrations';
            this.$get(url)
                .then(response => {
                    this.integrations = response.feeds;
                    this.available_integrations = response.available_integrations;
                    this.all_module_config_url = response.all_module_config_url;
                    // this.$success(response.message);
                })
                .catch(error => {
                    this.errors.record(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        isEmpty,
        fetchFields() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_id + '/slots/' + this.event_id + '/booking-fields')
                .then(response => {
                    this.fields = response.fields;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        hideEditor() {
            this.show_edit = false;
            this.getFeeds();
        }
    },
    computed: {
        filteredList() {
            let filteredList = {};
            Object.keys(this.available_integrations).map(key => {
                if (key.toLowerCase().includes(this.search.toLowerCase())) {
                    filteredList[key] = this.available_integrations[key];
                }
            });
            return filteredList;
        },
    },
    beforeMount() {
        this.getFeeds();
        this.fetchFields();
    }
};
</script>
<script setup>
import {Share} from "@element-plus/icons-vue";
</script>
