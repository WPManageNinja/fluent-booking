<template>
    <div class="fcal_calendar_settings fcal_integration_settings">
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
            <div v-if="!isEmpty(available_integrations)" class="fcal_actions">
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
                <div v-if="integrations.length" class="fcal_integration_items">
                    <div class="fcal_integration_item" v-for="integration in integrations" :key="integration.id">
                        <div class="fcal_card_wrap">
                            <div class="fcal_integration_icon">
                                <img v-if="integration.provider_logo"
                                     class="general_integration_logo"
                                     :src="integration.provider_logo" :alt="integration.provider"/>
                            </div>
                            <div class="fcal_card_item_details">
                                <h3>{{ integration.name }}</h3>
                                <ul class="event_triggers" v-if="integration.feed.event_trigger">

                                    <li v-for="(event, i) in integration.feed.event_trigger" :key="i"><i class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 stroke-[3px]"
                                             data-testid="start-icon">
                                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                        </svg>
                                    </i> {{ getEventName(event) }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="fcal_card_actions">
                            <el-switch
                                active-color="#00b27f"
                                @change="handleActive(integration)"
                                v-model="integration.enabled">
                            </el-switch>
                            <el-button
                                size="small"
                                type="success"
                                @click="edit(integration)"
                            >
                                <el-icon>
                                    <Edit/>
                                </el-icon>
                            </el-button>
                            <el-popconfirm
                                title="Are you sure to delete this?"
                                popper-class="fcal_confirm_dialog"
                                confirm-button-type="danger"
                                @confirm="removeFeed(integration.id)"
                            >
                                <template #reference>
                                    <el-button type="danger" size="small" class="fcal_danger_btn">
                                        <el-icon>
                                            <Delete/>
                                        </el-icon>
                                    </el-button>
                                </template>
                            </el-popconfirm>
                        </div>
                    </div>
                </div>
                <template v-else-if="isEmpty(available_integrations)">
                    <p style="font-size: 16px;">Currently FluentBooking has integration with FluentCRM. After install <a target="_blank" rel="nofollow" href="https://fluentcrm.com">FluentCRM</a>, you can configure the integration feed here. More integration will be available soon. For now, you may use webhook feed.</p>
                </template>
                <div v-else class="getting_started_message" style="padding-top: 16px; padding-bottom: 10px;">
                    <p style="font-size: 16px;">You haven't added any integration feed yet. Add new integration to connect your favourite tools
                        with your calendar</p>
                </div>
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
        },
        getEventName(name) {
            if (name == 'after_booking_scheduled') {
                return 'Booking Confirmed';
            }
            if (name == 'booking_schedule_completed') {
                return 'Booking Complated';
            }
            if (name == 'booking_schedule_cancelled') {
                return 'Booking Cancelled';
            }
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
