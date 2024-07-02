<template>
    <div class="fcal_create_calendar_wrap">
        <div class="fcal_header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item :to="{ name: 'calendars' }">{{ $t('Booking Types') }}</el-breadcrumb-item>
                <el-breadcrumb-item>{{ calendar.title ?? '...' }}</el-breadcrumb-item>
                <el-breadcrumb-item>{{ slot?.title ?? '...'}}</el-breadcrumb-item>
            </el-breadcrumb>

            <div class="fcal_actions">
                <el-button class="fcal_plain_btn" @click="openShare = true">
                    <el-icon><Share /></el-icon> {{ $t('Share') }}
                </el-button>
            </div>
        </div>

        <div v-if="loading" class="fcal_white_box">
            <el-skeleton animated :rows="6" />
        </div>
        <div v-else class="fcal_create_calendar_body_wrap">
            <el-aside>
                <ul class="fcal_settings_sidebar">
                    <li v-for="(menu, index) in menuItems" :key="index">
                        <router-link v-if="isRouteVisible(menu)" :to="menu.route">
                            <el-icon :class="menu.route.name">
                                <div v-if="menu.svgIcon" class="icon" v-html="menu.svgIcon"></div>
                                <component v-else-if="menu.elIcon" :is="menu.elIcon"></component>
                            </el-icon>
                            {{ menu.label }}
                        </router-link>
                    </li>
                </ul>
            </el-aside>
            <div v-if="slot.id" class="fcal_settings_content">
                <router-view :calendar_event="slot" />
            </div>
        </div>

        <ShareCalendarBlock 
            v-if="openShare" 
            :slot="slot" 
            :openShare="openShare"
            :calendarId="calendar_id"
            @closeShare="openShare = false"
        />
    </div>
</template>

<script>
import EventIcon from '../../../Components/Icons/EventIcon';
import QuestionIcon from '../../../Components/Icons/QuestionIcon';
import ScheduleIcon from '../../../Components/Icons/ScheduleIcon';
import NoficationIcon from '../../../Components/Icons/NoficationIcon';
import { Clock, Link, Message, Notification, Share, Money, Connection, Operation } from '@element-plus/icons-vue';
import ShareCalendarBlock from "./../parts/ShareCalendarBlock";

export default {
    name: 'SlotSettings',
    components: {
        EventIcon,
        ScheduleIcon,
        NoficationIcon,
        QuestionIcon,
        ShareCalendarBlock,
        Clock,
        Link,
        Share,
        Money,
        Message,
        Connection,
        Notification,
        Operation
    },
    data() {
        return {
            calendar: {},
            menuItems: {},
            event_id: this.$route.params.event_id,
            calendar_id: this.$route.params.calendar_id,
            slot: null,
            loading: true,
            saving: false,
            openShare: false
        }
    },
    computed: {
        isRouteVisible() {
            return (menu) => {
                if (menu.type != 'route') {
                    return false;
                }
                const hiddenRoutes = {
                    simple: 'assignment',
                    event: 'limit_settings'
                };
                if (menu.route.name === hiddenRoutes[this.calendar.type]) {
                    return false;
                }
                return true;
            }
        }
    },
    methods: {
        getSlot() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_id + '/events/' + this.event_id, {
                calendar_id : this.calendar_id,
                with: ['calendar', 'settings_menu']
            })
                .then(response => {
                    this.menuItems = response.settings_menu;
                    this.calendar = response.calendar;
                    this.slot = response.calendar_event;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    mounted() {
        this.$changeTitle(this.$t('Event Settings'));
        this.getSlot();
    }
}
</script>
