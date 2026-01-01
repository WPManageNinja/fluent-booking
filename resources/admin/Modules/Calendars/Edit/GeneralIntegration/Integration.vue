<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_calendar_settings fcal_integration_settings">
            <div class="fcal_settings_header" v-if="show_edit">
                <div class="fcal_settings_head">
                    <h2>
                        <el-breadcrumb separator="/">
                            <el-breadcrumb-item @click="showAll()">{{ $t('Integrations') }}</el-breadcrumb-item>
                            <el-breadcrumb-item>{{ $t('Edit') }}</el-breadcrumb-item>
                        </el-breadcrumb>
                    </h2>
                </div>
                <div class="fcal_actions">
                    <el-button
                        class="fcal_plain_btn"
                        @click="showAll()">
                        <el-icon><Back/></el-icon>
                        {{ $t('Back') }}
                    </el-button>
                </div>
            </div>

            <div v-else class="fcal_settings_header">
                <div class="fcal_settings_head">
                    <h2>{{ $t('Integrations') }}</h2>
                    <p>{{ $t('integrations_description') }}</p>
                </div>
                <div v-if="!isEmpty(available_integrations)" class="fcal_actions">
                    <el-dropdown @command="addNewIntegration" :hide-on-click="false" trigger="click" popper-class="fcal_select">
                        <el-button type="info">
                            {{ $t('Add New Integration') }}
                            <el-icon><ArrowDown/></el-icon>
                        </el-button>
                        <template #dropdown>
                            <el-dropdown-menu class="ff-dropdown-menu" slot="dropdown" style="max-height: 400px; overflow: auto">
                                <el-dropdown-item v-for="(integration,integration_name) in filteredList" :key="integration_name" :command="integration_name">
                                    {{ integration.title }}
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                    <el-dropdown trigger="click" popper-class="fcal_select">
                        <span class="el-dropdown-link fcal_more">
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
                                    active-color="#306ae0"
                                    @change="handleActive(integration)"
                                    v-model="integration.enabled">
                                </el-switch>
                                <el-button
                                    class="fcal_plain_btn"
                                    @click="edit(integration)"
                                >
                                    <el-icon>
                                        <Edit/>
                                    </el-icon>
                                </el-button>
                                <el-popconfirm
                                    :title="$t('Are you sure to delete this?')"
                                    popper-class="fcal_confirm_dialog"
                                    confirm-button-type="danger"
                                    :confirm-button-text="$t('Yes')"
                                    :cancel-button-text="$t('No')"
                                    @confirm="removeFeed(integration.id)"
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
                    <template v-else-if="isEmpty(available_integrations)">
                        <p style="font-size: 16px;">{{ $t('Currently FluentBooking has integration with FluentCRM. After install') }} <a target="_blank" rel="nofollow" href="https://fluentcrm.com">FluentCRM</a>{{ $t('Integration/FluentCRM_not_active_desc') }}</p>
                    </template>
                    <div v-else class="getting_started_message" style="padding-top: 16px; padding-bottom: 10px;">
                        <p class="fcal_help_text">{{ $t('Integration/empty_integrations_title') }}</p>
                    </div>
                </template>
                <IntegrationEditor
                    v-else
                    :editingIntegration="editingIntegration"
                    :calendar_event="calendar_event"
                    :inputs="fields"
                    :has_pro="appVars.has_pro"
                    :smart_codes="smart_codes"
                    @back="hideEditor"
                />
            </div>
        </div>
        <CloneDrawer
            v-if="isCloneOpen"
            :isOpen="isCloneOpen"
            :eventId="event_id"
            :eventLists="event_lists"
            :saving="saving"
            :title="$t('Clone Integration Settings')"
            :label="$t('Select Calendar Event')"
            :placeholder="$t('Select Event')"
            :helpText="$t('CalendarEvent/select_integration_settings')"
            :buttonLabel="$t('Clone Settings')"
            @clone="cloneIntegrations"
            @update:isOpen="isCloneOpen = $event"
        />
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

import { ArrowDown, Back, MoreFilled } from '@element-plus/icons-vue';
import QuestionIcon from "@/Components/Icons/QuestionIcon.vue";
import EventIcon from "@/Components/Icons/EventIcon.vue";
import CloneDrawer from '@/Components/Common/CloneDrawer.vue';

export default {
    name: 'Integrations',
    props: ['calendar_id', 'event_id', 'calendar_event', 'event_lists'],
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
        CloneDrawer,
        MoreFilled
    },
    data() {
        return {
            search: '',
            loading: true,
            saving: false,
            integrations: [],
            errors: new Errors,
            available_integrations: {},
            all_module_config_url: '',
            show_edit: false,
            isCloneOpen: false,
            integration_id: 0,
            integration_name: null,
            fields: [],
            editingIntegration: {
                integration_id: '',
                integration_name: ''
            },
            smart_codes: {
                texts: {},
                html: {}
            }
        };
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
        hideEditor() {
            this.show_edit = false;
            this.getFeeds();
        },
        getEventName(name) {
            const eventNames = {
                'after_booking_scheduled' : this.$t('Booking Confirmed'),
                'booking_schedule_completed' : this.$t('Booking Completed'),
                'booking_schedule_cancelled' : this.$t('Booking Cancelled'),
                'after_booking_rescheduled' : this.$t('Booking Rescheduled'),
                'booking_schedule_rejected' : this.$t('Booking Rejected'),
            }
            return eventNames[name] || name;
        },
        handleActive(row) {
            this.errors.clear();
            this.saving = true;
            this.$post('calendars/' + this.calendar_id + '/events/' + this.event_id + '/integrations/' + row.id, {
                    calendar_id : this.calendar_id,
                    status: row.enabled,
                })
                .then(response => {
                    this.$handleSuccess(response.message);
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.saving = false
                });
        },
        removeFeed(feed_id) {
            this.deleting = true;
            this.$del('calendars/' + this.calendar_id + '/events/' + this.event_id + '/integrations/' + feed_id, {
                    calendar_id : this.calendar_id,
                    integration_id: feed_id,
                })
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
        },
        getFeeds() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_id + '/events/' + this.event_id + '/integrations', {
                calendar_id : this.calendar_id
            })
                .then(response => {
                    this.integrations = response.feeds;
                    this.available_integrations = response.available_integrations;
                    this.all_module_config_url = response.all_module_config_url;
                    this.smart_codes = response.smart_codes
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
            this.$get('calendars/' + this.calendar_id + '/events/' + this.event_id + '/booking-fields', {
                calendar_id : this.calendar_id
            })
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
        cloneIntegrations(eventId) {
            this.saving = true;
            this.$post('calendars/' + this.calendar_id + '/events/' + this.event_id + '/integrations/clone', {
                    calendar_id : this.calendar_id,
                    from_event_id: eventId,
                })
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.getFeeds();
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.saving = false;
                });
        }
    },
    beforeMount() {
        this.getFeeds();
        this.fetchFields();
    }
};
</script>
