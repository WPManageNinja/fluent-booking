<template>
    <div class="fcal_create_calendar_wrap">
        <div class="fcal_header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item :to="gotoCalendars()">{{ $t('Calendars') }}</el-breadcrumb-item>
                <el-breadcrumb-item :to="gotoCalendarSettings()">{{ calendar.title ?? '...' }}</el-breadcrumb-item>
                <el-breadcrumb-item>{{ slot?.title ?? '...'}}</el-breadcrumb-item>
            </el-breadcrumb>

            <div class="fcal_actions">
                <el-button class="fcal_plain_btn" @click="openShare = true">
                    <el-icon><Share /></el-icon> {{ $t('Share') }}
                </el-button>
                <div class="fcal_event_settings_menu_opener" @click="toggleDrawer">
                    <el-icon><OpenerIcon/></el-icon>
                </div>
            </div>
        </div>

        <div v-if="loading" class="fcal_white_box">
            <el-skeleton animated :rows="6" />
        </div>
        <div v-else class="fcal_create_calendar_body_wrap fcal_slot_settings">
            <el-aside>
                <ul class="fcal_settings_sidebar">
                    <template v-for="(menu, index) in menuItems">
                        <li v-if="isRouteVisible(menu)" :key="index">
                            <router-link :to="menu.route" @click.native="setCurrentMenu(menu)">
                                <el-icon :class="menu.route.name">
                                    <div v-if="menu.svgIcon" class="icon" v-html="menu.svgIcon"></div>
                                    <component v-else-if="menu.elIcon" :is="menu.elIcon"></component>
                                </el-icon>
                                {{ menu.label }}
                            </router-link>
                        </li>
                    </template>
                </ul>
            </el-aside>
            <div v-if="slot.id" class="fcal_settings_content">
                <router-view :calendar_event="slot" :disabled="currentMenu.disable" :event_lists="eventLists"/>
            </div>
        </div>

        <ShareCalendarBlock 
            v-if="openShare" 
            :slot="slot" 
            :openShare="openShare"
            :calendarId="calendar_id"
            :isAuthorCalendars="false"
            @closeShare="openShare = false"
        />
    </div>
    <el-drawer
        v-model="showDrawer"
        :size="320"
        :title="$t('Event Settings')"
        :direction="appVars.is_rtl ? 'ltr' : 'rtl'"
        :append-to-body="true"
        modal-class="fcal_settings_menu_drawer">
        <div class="fcal_settings_menu">
            <template v-for="(menu, index) in menuItems">
                <li v-if="isRouteVisible(menu)" :key="index">
                    <router-link :to="menu.route" @click.native="setCurrentMenu(menu)">
                        <el-icon :class="menu.route.name">
                            <div v-if="menu.svgIcon" class="icon" v-html="menu.svgIcon"></div>
                            <component v-else-if="menu.elIcon" :is="menu.elIcon"></component>
                        </el-icon>
                        {{ menu.label }}
                    </router-link>
                </li>
            </template>
        </div>
    </el-drawer>
</template>

<script>
import EventIcon from '../../../Components/Icons/EventIcon';
import OpenerIcon from '../../../Components/Icons/OpenerIcon';
import QuestionIcon from '../../../Components/Icons/QuestionIcon';
import ScheduleIcon from '../../../Components/Icons/ScheduleIcon';
import NoficationIcon from '../../../Components/Icons/NoficationIcon';
import { Clock, Link, Message, Notification, Share, Money, Connection, Operation } from '@element-plus/icons-vue';
import ShareCalendarBlock from "./../parts/ShareCalendarBlock";

export default {
    name: 'SlotSettings',
    components: {
        OpenerIcon,
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
            currentMenu: {},
            eventLists: [],
            event_id: this.$route.params.event_id,
            calendar_id: this.$route.params.calendar_id,
            showDrawer: false,
            slot: null,
            loading: true,
            saving: false,
            openShare: false
        }
    },
    computed: {
        isRouteVisible() {
            return (menu) => {
                return menu.type == 'route' && menu.visible;
            }
        }
    },
    methods: {
        setCurrentMenu(menu) {
            this.currentMenu = menu;
            if (this.showDrawer) {
                this.toggleDrawer();
            }
        },
        toggleDrawer() {
            this.showDrawer = !this.showDrawer;
        },
        updateCurrentMenu() {
            const currentRouteName = this.$route.name;
            const currentMenu = Object.values(this.menuItems).find(menu => menu.route.name === currentRouteName);
            if (currentMenu) {
                this.currentMenu = currentMenu;
            }
        },
        gotoCalendars() {
            return {
                name: 'calendars'
            }
        },
        gotoCalendarSettings() {
            return {
                name: 'calendar_settings',
                params: { calendar_id: this.calendar_id }
            }
        },
        getSlot() {
            this.loading = true;
            this.$get('calendars/' + this.calendar_id + '/events/' + this.event_id, {
                calendar_id : this.calendar_id,
                with: ['calendar', 'settings_menu', 'calendar_event_lists']
            })
                .then(response => {
                    this.menuItems = response.settings_menu;
                    this.calendar = response.calendar;
                    this.slot = response.calendar_event;
                    this.eventLists = response.calendar_event_lists;
                    this.updateCurrentMenu();
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
