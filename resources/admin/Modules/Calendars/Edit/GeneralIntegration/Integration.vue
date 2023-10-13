<template>
    <div class="ff_form_integrations">
        <card>
            <card-head>
                <card-head-group class="justify-between">
                    <h5 class="title">{{ $t('Integrations') }}</h5>

                    <el-button
                        v-if="show_edit"
                        class="fcal_primary_btn2"
                        @click="show_edit = false"
                    >
                        <el-icon><Back /></el-icon> Back
                    </el-button>

                    <div v-else class="action-buttons">
                        <el-dropdown @command="add" :hide-on-click="false" trigger="click">
                            <el-button type="info">
                                {{ $t('Add New Integration') }}

                                <el-icon><ArrowDown /></el-icon>
                            </el-button>
                            <template #dropdown>
                                <el-dropdown-menu class="ff-dropdown-menu" slot="dropdown" style="max-height: 400px; overflow: auto">
                                    <el-dropdown-item>
                                        <el-input @click.prevent autofocus v-model="search" :placeholder="$t('Search Integration')"></el-input>
                                    </el-dropdown-item>
                                    <el-dropdown-item v-for="(integration,integration_name) in filteredList" :key="integration_name" :command="integration_name">{{integration.title}}</el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </div>
                </card-head-group>
            </card-head>
            <card-body v-if="!show_edit">
                <!-- <div v-if="has_pro && isEmpty(available_integrations) && !loading" class="text-center">
                    <p style="font-size: 16px; margin-bottom: 20px; max-width: 760px; margin-left: auto; margin-right: auto;">
                        {{ $t(this.integrationsResource.instruction) }}
                    </p>
                     <a class="el-button el-button--primary el-dropdown-selfdefine" :href="all_module_config_url">
                        {{ $t('Configure Modules') }}
                    </a>
                </div> -->

                <!-- Feeds Table: 1 -->
                <div class="ff-table-container">
                    <el-skeleton :loading="loading" animated :rows="6">
                        <el-table v-if="!isEmpty(available_integrations)" :data="integrations">
                            <template #empty>
                                <div class="getting_started_message" style="padding-top: 16px; padding-bottom: 10px;">
                                    <p>{{ $t('You haven\'t added any integration feed yet. Add new integration to connect your favourite tools with your calendar') }}</p>
                                </div>
                            </template>

                            <el-table-column width="180" :label="$t('Status')">
                                <template #default="scope">
                                    <span class="mr-3" v-if="scope.row.enabled">{{$t('Enabled')}}</span>
                                    <span class="mr-3" v-else style="color:#fa3b3c;">{{ $t('Disabled') }}</span>
                                    <el-switch 
                                        active-color="#00b27f" 
                                        @change="handleActive(scope.row)" 
                                        v-model="scope.row.enabled">
                                    </el-switch>
                                </template>
                            </el-table-column>

                            <el-table-column width="180" :label="$t('Integration')">
                                <template #default="scope">
                                    <img v-if="scope.row.provider_logo" class="general_integration_logo" :src="scope.row.provider_logo" :alt="scope.row.provider" />
                                    <span class="general_integration_name" v-else>{{scope.row.provider}}</span>
                                </template>
                            </el-table-column>


                            <el-table-column :label="$t('Title')">
                                <template #default="scope">
                                    {{scope.row.name}}
                                </template>
                            </el-table-column>

                            <el-table-column width="130" :label="$t('Actions')" class-name="action-buttons">
                                <template #default="scope">
                                    <el-button
                                        class="fcal_primary_btn"
                                        @click="edit(scope.row)"
                                    >
                                        <el-icon><Edit /></el-icon>
                                    </el-button>
                                    <el-popconfirm
                                        title="Are you sure to delete this?"
                                        popper-class="fcal_confirm_dialog"
                                        confirm-button-type="danger"
                                        @confirm="remove(scope.row.id, scope)"
                                    >
                                        <template #reference>
                                            <el-button type="danger" class="fcal_danger_btn">
                                                <el-icon><Delete /></el-icon>
                                            </el-button>
                                        </template>
                                    </el-popconfirm>

                                    <!-- <btn-group size="sm">
                                        <btn-group-item>
                                            <el-button
                                                class="el-button--soft el-button--icon"
                                                @click="edit(scope.row)"
                                                type="success"
                                                icon="ff-icon-setting"
                                                size="small">
                                            </el-button>
                                        </btn-group-item>
                                        <btn-group-item>
                                            <remove @on-confirm="remove(scope.row.id, scope)">
                                                <el-button
                                                    class="el-button--soft el-button--icon"
                                                    size="small"
                                                    type="danger"
                                                    icon="ff-icon-trash"
                                                />
                                            </remove>
                                        </btn-group-item>
                                    </btn-group> -->
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-skeleton>
                </div><!-- .ff-table-container -->

                <p v-if="has_pro && !integrations.length" class="text-center">
                    <a :href="all_module_config_url">{{$t('Check Global Integration Settings')}}</a>
                    <a style="margin-left: 20px" target="_blank" rel="noopener" href="https://wpmanageninja.com/docs/fluent-form/integrations-available-in-wp-fluent-form/">
                        {{ $t('View Documentations') }}
                    </a>
                </p>

                <!-- <div v-if="!has_pro" class="upgrade_to_pro text-center mt-4" style="max-width: 750px; margin: auto;">
                    <p style="font-size: 16px;" class="mb-4">
                        {{ $t(this.integrationsResource.instruction) }}
                    </p>

                    <btn-group>
                        <btn-group-item>
                            <a class="el-button el-button--primary el-dropdown-selfdefine" :href="upgrade_url">
                                {{ $t('Upgrade to PRO') }}
                            </a>
                        </btn-group-item>
                        <btn-group-item>
                            <a class="el-button el-button--default" :href="integrationsResource.list_url">
                                {{ $t('See All Integrations') }}
                            </a>
                        </btn-group-item>
                    </btn-group>
                    
                    <img class="mt-6" :src="integrationsResource.asset_url" alt="integrations asset" />
                </div> -->

            </card-body>
            
            <div class="fcal_settings_body">
                <IntegrationEditor
                    v-if="show_edit"
                    :calendar_id="calendar_id"
                    :event_id="event_id"
                    :integration_id="integration_id"
                    :integration_name="integration_name"
                    :inputs="fields"
                    :has_pro="has_pro"
                    @back="hideEditor"
                />
            </div>
        </card>
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
    
    import {ArrowDown, Back, Edit, Delete } from '@element-plus/icons-vue';

    export default {
        name: 'Integrations',
        props: ['calendar_id', 'event_id', 'has_pro', 'editorShortcodes'],
        components: {
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
            // integrationsResource: window.FluentCalendarApp.integrationsResource,
            };
        },
        methods: {
            add(integration_name) {
                let integration = this.available_integrations[integration_name];

                // if (!integration.is_active) {
                //     // Handle Inactive state
                //     this.$confirm(integration.configure_message, integration.configure_title, {
                //         confirmButtonText: integration.configure_button_text,
                //         cancelButtonText: 'Cancel',
                //         type: 'warning',
                //     })
                //         .then(() => {
                //             window.location.href = integration.global_configure_url;
                //             return;
                //         })
                //         .catch(() => {});
                //     return;
                // }

                
                this.integration_id = 0;
                this.integration_name = integration_name;
                this.show_edit = true;
            },
            edit(integration) {
                this.integration_id = integration.id;
                this.integration_name = integration.provider;
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
            remove(feed_id, scope) {
                const url = 'calendars/' + this.calendar_id + '/slots/' + this.event_id + '/integrations/' + feed_id;

                let $index = scope.$index;
                let data = {
                    integration_id: feed_id,
                };
                this.deleting = true;
                this.$del(url, data)
                    .then(response => {
                        this.$handleSuccess(response.message);
                        this.integrations.splice($index, 1);
                    })
                    .catch(error => {
                        this.$handleError(error);
                    })
                    .finally(() => {});
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
