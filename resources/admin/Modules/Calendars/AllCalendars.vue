<template>
    <div class="fcal_section fcal_section_narrow">
        <div class="fcal_section_header">
            <div class="fcal_title">
                <h3>{{ $t('Calendars') }}</h3>
            </div>
            <div class="fcal_actions">
                <el-input class="fcal_search_input"
                          v-model="search"
                          clearable
                          @clear="getCalendars"
                          @keyup.enter.native="getCalendars"
                          :placeholder="$t('Search Events')">
                    <template #append>
                        <el-button @click="getCalendars"><el-icon><Search /></el-icon></el-button>
                    </template>
                </el-input>
                <el-dropdown v-if="hasSupport('multi_users') && hasAccess('invite_team_members')" trigger="click" popper-class="fcal_select">
                    <span class="el-dropdown-link">
                        <el-button class="fcal_primary_btn">
                            <span>+</span> {{ $t('New') }}
                        </el-button>
                    </span>
                    <template #dropdown>
                        <el-dropdown-menu>
                            <el-dropdown-item
                                @click="isNewBookingOpen = true">
                                {{ $t('Add Host') }}
                            </el-dropdown-item>
                            <el-dropdown-item 
                                @click="isNewTeamOpen = true">
                                {{ $t('Add Team') }}
                            </el-dropdown-item>
                            <el-dropdown-item 
                                @click="isSingleEventOpen = true">
                                {{ $t('One-off Event') }}
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>
        </div>
        <div class="fcal_section_body">

            <SkeletonLoader v-if="loading"/>

            <div v-else class="fcal_calendars_wrap">
                <template v-if="calendars.length">
                    <div v-for="calendar in calendars" :key="calendar.id" class="fcal_each_cal">
                        <calendar-event-block @fetchCalendar="getCalendars" :calendar="calendar" :eventLists="event_lists"/>
                    </div>
                </template>
                <el-empty v-else class="fcal_empty" :description="$t('No Calendars found')"/>
            </div>

            <div class="fcal_right fcal_tm20">
                <pagination popper-class="fcal_select" :pagination="pagination" @fetch="getCalendars"/>
            </div>
        </div>

        <el-drawer
            v-model="isNewBookingOpen"
            :title="$t('Add New Calendar Host')"
            :zIndex="999"
            label-position="top"
            modal-class="fcal_drawer">
            <div class="fcal_create_new_booking_type_drawer">
                <el-form-item :label="$t('Select Host')">
                    <HostSelector v-model="user_id"/>
                    <p>{{ $t('AllCalendars/create_host_desc') }}</p>
                </el-form-item>
                <el-button
                    @click="createCalendarEvent('single')"
                    :disabled="!user_id">
                    <div class="icons-wrap">
                        <el-icon><User/></el-icon>
                        <el-icon><Right/></el-icon>
                        <div class="icons">
                            <el-icon><User/></el-icon>
                        </div>
                    </div>
                    <div class="content">
                        <h3>{{ $t('One-to-One') }}</h3>
                        <h4><strong>{{ $t('One host') }}</strong> <span>{{ $t('with') }}</span> <strong>{{ $t('One invitee') }}</strong></h4>
                        <p>{{ $t('Good for: coffee chats, 1:1 interviews, etc.') }}</p>
                        <el-icon class="icon-right">
                            <Right/>
                        </el-icon>
                    </div>
                </el-button>
                <el-button
                    @click="createCalendarEvent('group')"
                    :disabled="!user_id">
                    <div class="icons-wrap">
                        <el-icon><User/></el-icon>
                        <el-icon><Right/></el-icon>
                        <div class="icons">
                            <el-icon><User/></el-icon>
                            <el-icon><User/></el-icon>
                        </div>
                    </div>
                    <div class="content">
                        <h3>{{ $t('Group') }}</h3>
                        <h4><strong>{{ $t('One host') }}</strong> <span>{{ $t('with') }}</span> <strong>{{ $t('Group of invitees') }}</strong></h4>
                        <p>{{ $t('Good for: webinars, online classes, etc.') }}</p>
                        <el-icon class="icon-right">
                            <Right/>
                        </el-icon>
                    </div>
                </el-button>
            </div>
        </el-drawer>

        <el-drawer
            v-model="isNewTeamOpen"
            :title="$t('Add New Team')"
            :zIndex="999"
            label-position="top"
            modal-class="fcal_drawer">
            <div v-if="appVars.has_pro" class="fcal_create_new_booking_type_drawer">
                <el-form-item :label="$t('Team Name') + ' *'">
                    <el-input
                        v-model="team_name"
                        type="text"
                        :placeholder="$t('Enter Name of this team')"
                    />
                </el-form-item>
                <el-form-item :label="$t('Select Team Members') + ' *'">
                    <TeamMemberSelector v-model="team_members"/>
                    <p>{{ $t('Please select the members you want to assign to this team') }}</p>
                </el-form-item>
                <el-button
                    @click="createTeamEvent('round_robin')"
                    :disabled="!team_name || !team_members.length">
                    <div class="icons-wrap">
                        <el-icon><User/></el-icon>
                        <el-icon><User/></el-icon>
                        <el-icon><Right/></el-icon>
                        <el-icon><User/></el-icon>
                    </div>
                    <div class="content">
                        <h3>{{ $t('Round Robin') }}</h3>
                        <h4><strong>{{ $t('One rotating host') }}</strong> <span>{{ $t('with') }}</span> <strong>{{ $t('One invitee') }}</strong></h4>
                        <p>{{ $t('Good for: distributing incoming sales leads.') }}</p>
                        <el-icon class="icon-right">
                            <Right/>
                        </el-icon>
                    </div>
                </el-button>
            </div>
            <div v-else>
                <p class="fcal_need_pro">{{ $t('Team') + ' ' + $t('NeedProVersion')}}</p>
                <a target="_blank" :href="appVars.upgrade_url" class="el-button fcal_primary_btn">
                    {{$t('Upgrade to Pro')}}
                </a>
            </div>
        </el-drawer>

        <el-drawer
            v-model="isSingleEventOpen"
            :title="$t('Add One-off Event')"
            :zIndex="999"
            label-position="top"
            modal-class="fcal_drawer">
            <div v-if="appVars.has_pro" class="fcal_create_new_booking_type_drawer">
                <el-form-item :label="$t('Event Calendar Name') + ' *'">
                    <el-input
                        v-model="event_name"
                        type="text"
                        :placeholder="$t('Enter Name of this event calendar')"
                    />
                </el-form-item>
                <el-form-item :label="$t('Select Event Hosts') + ' *'">
                    <TeamMemberSelector v-model="event_members" :modelPlaceholder="$t('Select Event Hosts')"/>
                    <p>{{ $t('Please select the hosts you want to assign to this event') }}</p>
                </el-form-item>
                <el-button
                    @click="createEventCalendar('single_event')"
                    :disabled="!event_name || !event_members.length">
                    <div class="icons-wrap">
                        <el-icon><User/></el-icon>
                        <el-icon><User/></el-icon>
                        <el-icon><Right/></el-icon>
                        <el-icon><User/></el-icon>
                    </div>
                    <div class="content">
                        <h3>{{ $t('Single Event') }}</h3>
                        <h4><strong>{{ $t('Invite someone') }}</strong> <span>{{ $t('to pick a time to meet with') }}</span> <strong>{{ $t('hosts') }}</strong></h4>
                        <p>{{ $t('Good for: higher priority meetings.') }}</p>
                        <el-icon class="icon-right">
                            <Right/>
                        </el-icon>
                    </div>
                </el-button>
                <el-button
                    @click="createEventCalendar('group_event')"
                    :disabled="!event_name || !event_members.length">
                    <div class="icons-wrap">
                        <div class="icons">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="-mt-px mr-1 inline h-3 w-3"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <el-icon><Right/></el-icon>
                        <div class="icons">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="-mt-px mr-1 inline h-3 w-3"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                    </div>
                    <div class="content">
                        <h3>{{ $t('Group Event') }}</h3>
                        <h4><strong>{{ $t('Reserve spots') }}</strong> <span>{{ $t('for a scheduled event with') }}</span> <strong>{{ $t('hosts') }}</strong></h4>
                        <p>{{ $t('Good for: reservation or ticketing system') }}</p>
                        <el-icon class="icon-right">
                            <Right/>
                        </el-icon>
                    </div>
                </el-button>
            </div>
            <div v-else>
                <p class="fcal_need_pro">{{ $t('Single Event') + ' ' + $t('NeedProVersion')}}</p>
                <a target="_blank" :href="appVars.upgrade_url" class="el-button fcal_primary_btn">
                    {{$t('Upgrade to Pro')}}
                </a>
            </div>
        </el-drawer>
    </div>
</template>

<script>
import Pagination from "../../Pieces/Pagination";
import CalendarEventBlock from "./parts/CalendarEventBlock";
import { User, Right, Search } from '@element-plus/icons-vue';
import HostSelector from "../../Pieces/HostSelector";
import TeamMemberSelector from "../../Pieces/TeamMemberSelector";
import SkeletonLoader from "../../Pieces/SkeletonLoader";

export default {
    name: 'AllCalendars',
    components: {
        SkeletonLoader,
        HostSelector,
        TeamMemberSelector,
        User,
        Right,
        Search,
        Pagination,
        CalendarEventBlock
    },
    data() {
        return {
            calendars: [],
            loading: false,
            pagination: {
                total: 0,
                per_page: 10,
                current_page: 1
            },
            isNewBookingOpen: false,
            isNewTeamOpen: false,
            isSingleEventOpen: false,
            user_id: '',
            team_name: '',
            team_members: [],
            event_name: '',
            event_members: [],
            event_lists: [],
            search: ''
        }
    },
    methods: {
        getCalendars() {
            this.loading = true;
            this.$get('calendars', {
                per_page: this.pagination.per_page,
                page: this.pagination.current_page,
                search: this.search,
                with: ['calendar_event_lists']
            })
                .then(response => {
                    this.calendars = response.calendars.data;
                    this.pagination.total = response.calendars.total;
                    this.event_lists = response.calendar_event_lists;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        createCalendarEvent(eventType) {
            this.$router.push({
                name: 'create_calendar',
                params: {host_id: this.user_id, event_type: eventType}
            })
        },
        createTeamEvent(eventType) {
            this.$router.push({
                name: 'create_calendar',
                params: {host_id: this.appVars.me.id, event_type: eventType},
                query: {team_name: this.team_name, team_members: this.team_members}
            })
        },
        createEventCalendar(eventType) {
            this.$router.push({
                name: 'create_calendar',
                params: {host_id: this.appVars.me.id, event_type: eventType},
                query: {event_name: this.event_name, event_members: this.event_members}
            })
        }
    },
    mounted() {
        this.$changeTitle('Calendars');
        this.getCalendars();
    }
}
</script>
