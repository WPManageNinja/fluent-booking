<template>
    <div class="fcal_event_block">
        <div class="fcal_cal_header">
            <div class="fcal_cal_title">
                <img :src="calendar.author_profile.avatar"/>
                <div class="fcal_cal_info">
                    <h3>{{ calendar.title }}</h3>
                    <p v-if="calendar.public_url && calendar.visibility == 'public'" class="fcal_profile_link">
                        <a target="_blank" rel="noopener" :href="calendar.public_url">{{calendar.public_url}}</a>
                    </p>
                    <p class="fcal_profile_link" v-else-if="!calendar.public_url"><span style="cursor: pointer;" @click="goToCalendarSetting">
                        {{ $t('Enable Landing Page') }}
                    </span></p>
                    <div v-if="calendar.generic_error" v-html="calendar.generic_error"></div>
                </div>
            </div>
            <div class="fcal_cal_actions">
                <el-button class="fcal_plain_btn" @click="goToCalendarSetting">
                    <el-icon><Setting /></el-icon> {{ getSettingLabel }}
                </el-button>

                <el-button
                    @click="isNewBookingOpen = true"
                    class="fcal_primary_btn2">
                    <span>+</span> {{ $t('New Event Type') }}
                </el-button>

                <el-dropdown @command="handleCommand" popper-class="fcal_select" trigger="click">
                    <span class="el-dropdown-link">
                        <el-icon><MoreFilled /></el-icon>
                    </span>
                    <template #dropdown>
                        <el-dropdown-menu>
                            <el-dropdown-item v-if="calendar.public_url" command="copy"><el-icon><Link /></el-icon>
                                {{ $t('Copy link') }}
                            </el-dropdown-item>
                            <el-dropdown-item command="clone"><el-icon><CopyDocument /></el-icon>
                                {{ $t('Clone from') }}
                            </el-dropdown-item>
                            <el-dropdown-item command="reorder"><el-icon><Rank /></el-icon>
                                {{ $t('Reorder Events') }}
                            </el-dropdown-item>
                            <el-dropdown-item command="export"><el-icon><Download /></el-icon>
                                {{ $t('Export') }}
                            </el-dropdown-item>
                            <el-dropdown-item command="delete"><el-icon><Delete /></el-icon>
                                {{ $t('Delete') }}
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>
        </div>
        <div class="fcal_cal_slots">
            <div v-if="calendar.slots.length" class="fcal_cal_slot" v-for="(slot, slotIndex) in orderedCalendarEvents" :key="slot.id">
                <each-slot @slotDeleted="slotDeleted(slot.id)" :slot="slot" :calendarId="calendar.id"/>
            </div>
            <div v-else class="fcal_cal_empty_slots">
                {{ $t('No Event Found!') }}
            </div>
        </div>
        <el-dialog v-model="showSettings" :title="$t('Calendar Settings')">
            <calendar-settings @calendarUpdated="() => {showSettings = false}" :calendar="calendar" />
        </el-dialog>

        <el-drawer
            v-model="isNewBookingOpen"
            :title="$t('Create New Event Type')"
            :zIndex="999"
            :direction="appVars.is_rtl ? 'ltr' : 'rtl'"
            modal-class="fcal_drawer">
            <div class="fcal_create_new_booking_type_drawer">
                <template v-if="calendar.type == 'team'">
                    <el-form-item :label="$t('Select Team Members') + ' *'">
                        <TeamMemberSelector v-model="teamMembers"/>
                        <p>{{ $t('Please select the members you want to assign to this team') }}</p>
                    </el-form-item>
                    <el-button @click="createTeamEvent('round_robin')"
                        :disabled="!teamMembers.length">
                        <div class="icons-wrap">
                            <el-icon><User /></el-icon>
                            <el-icon><User /></el-icon>
                            <el-icon><Right /></el-icon>
                            <div class="icons">
                                <el-icon><User /></el-icon>
                            </div>
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
                    <el-button @click="createTeamEvent('collective')"
                        :disabled="!teamMembers.length">
                        <div class="icons-wrap">
                            <el-icon><User /></el-icon>
                            <el-icon><User /></el-icon>
                            <el-icon><Right /></el-icon>
                            <div class="icons">
                                <el-icon><User /></el-icon>
                            </div>
                        </div>
                        <div class="content">
                            <h3>{{ $t('Collective') }}</h3>
                            <h4><strong>{{ $t('Multiple hosts') }}</strong> <span>{{ $t('with') }}</span> <strong>{{ $t('One invitee') }}</strong></h4>
                            <p>{{ $t('Good for: Panel interviews, group sales calls, etc.') }}</p>
                            <el-icon class="icon-right">
                                <Right/>
                            </el-icon>
                        </div>
                    </el-button>
                </template>
                <template v-else-if="calendar.type == 'event'">
                    <el-form-item :label="$t('Select Event Hosts') + ' *'">
                        <TeamMemberSelector v-model="eventMembers" :modelPlaceholder="$t('Select Event Hosts')"/>
                        <p>{{ $t('Please select the hosts you want to assign to this event') }}</p>
                    </el-form-item>
                    <el-button @click="createEventCalendar('single_event')"
                        :disabled="!eventMembers.length">
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
                    <el-button @click="createEventCalendar('group_event')"
                        :disabled="!eventMembers.length">
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
                </template>
                <template v-else>
                    <el-button @click="createCalendarEvent('single')">
                        <div class="icons-wrap">
                            <el-icon><User /></el-icon>
                            <el-icon><Right /></el-icon>
                            <div class="icons">
                                <el-icon><User /></el-icon>
                            </div>
                        </div>
                        <div class="content">
                            <h3>{{ $t('One-to-One') }}</h3>
                            <h4><strong>{{ $t('One host') }}</strong> <span>{{ $t('with') }}</span> <strong>{{ $t('One invitee') }}</strong></h4>
                            <p>{{ $t('Good for: coffee chats, 1:1 interviews, etc.') }}</p>
                            <el-icon class="icon-right"><Right /></el-icon>
                        </div>
                    </el-button>
                    <el-button @click="maybeCreateGroupSlot">
                        <div class="icons-wrap">
                            <el-icon><User /></el-icon>
                            <el-icon><Right /></el-icon>
                            <div class="icons">
                                <el-icon><User /></el-icon>
                                <el-icon><User /></el-icon>
                            </div>
                        </div>
                        <div class="content">
                            <h3>{{ $t('Group') }}</h3>
                            <h4><strong>{{ $t('One host') }}</strong> <span>{{ $t('with') }}</span> <strong>{{ $t('Group of invitees') }}</strong></h4>
                            <p>{{ $t('Good for: webinars, online classes, etc.') }}</p>
                            <el-icon class="icon-right"><Right /></el-icon>
                        </div>
                    </el-button>
                </template>
            </div>
        </el-drawer>

        <el-drawer
            v-model="isCloneOpen"
            :title="$t('Clone Calendar Event')"
            :zIndex="999"
            label-position="top"
            :direction="appVars.is_rtl ? 'ltr' : 'rtl'"
            modal-class="fcal_drawer">
            <div class="fcal_clone_event_drawer">
                <el-form-item :label="$t('Select Calendar Event')">
                    <el-select
                        v-model="cloneEventId"
                        :placeholder="$t('Select Event')"
                        popper-class="fcal_select"
                        placement="bottom"
                        :no-match-text="$t('No Data match')"
                        :no-data-text="$t('No Data')">
                        <el-option-group
                            v-for="cal in filteredEventList"
                                :key="cal.id"
                                :label="cal.title">
                                <el-option
                                    v-for="event in cal.options"
                                    :key="event.id"
                                    :label="event.title"
                                    :value="event.id">
                                </el-option>
                        </el-option-group>
                    </el-select>
                    <p>{{ $t('CalendarEvent/select_event_description') }}</p>
                </el-form-item>
                <SaveButton :saving="saving" :disabled="!cloneEventId" :label="$t('Clone Event')" @click="cloneEvent"/>
            </div>
        </el-drawer>

        <ReorderDialog
            v-if="reorderModal"
            :openModal="reorderModal"
            :calendar="calendar"
            @saveOrder="$emit('fetchCalendar')"
            @closeModal="reorderModal = false"
        />
        <ProNoticeDialog 
            v-if="noticeModal" 
            :openModal="noticeModal"
            :title="noticeTitle"
            @update:openModal="noticeModal = $event"
        />
    </div>
</template>

<script>
import EachSlot from "./EachSlot";
import { Setting, User, Right, MoreFilled, Rank, Download, Link } from '@element-plus/icons-vue';
import CalendarSettings from "./CalendarSettings";
import SaveButton from "../../../Components/Buttons/SaveButton.vue";
import { copyToClipBoard } from '@/Bits/data_config.js';
import TeamMemberSelector from "@/Pieces/TeamMemberSelector.vue";
import ProNoticeDialog from "@/Components/Common/ProNoticeDialog.vue";
import ReorderDialog from "./_ReorderDialog";

export default {
    name: 'CalendarEventBlock',
    props: ['calendar', 'eventLists'],
    components: {
        EachSlot,
        Setting,
        User,
        Right,
        CalendarSettings,
        TeamMemberSelector,
        MoreFilled,
        Rank,
        Download,
        Link,
        SaveButton,
        ReorderDialog,
        ProNoticeDialog
    },
    data() {
        return {
            saving: false,
            cloneEventId: '',
            calendarEvents: [],
            showSettings: false,
            isNewBookingOpen: false,
            isCloneOpen: false,
            reorderModal: false,
            noticeModal: false,
            noticeTitle: '',
            teamMembers: [],
            eventMembers: []
        }
    },
    computed: {
        getSettingLabel() {
            return this.calendar.type == 'team' ? this.$t('Team Settings') : this.$t('Host Settings');
        },
        filteredEventList() {
            return this.calendarEvents.filter(calendarEvent => calendarEvent.id != this.calendar.id);
        },
        orderedCalendarEvents() {
            const eventOrder = this.calendar.event_order ?? [];
            return this.calendar.slots.slice().sort((a, b) => {
                return eventOrder.indexOf(a.id) - eventOrder.indexOf(b.id);
            });
        }
    },
    methods: {
        slotDeleted(slotId) {
            const slotIndex = this.calendar.slots.findIndex(slot => slot.id === slotId);
            if (slotIndex != -1) {
                this.calendar.slots.splice(slotIndex, 1);
            }
        },
        goToCalendarSetting() {
            this.$router.push({
                name: 'calendar_settings',
                params: { calendar_id: this.calendar.id }
            })
        },
        createCalendarEvent(eventType) {
            this.$router.push({
                name: 'create_slot_event',
                params: {calendar_id: this.calendar.id, event_type: eventType}
            })
        },
        createTeamEvent(eventType) {
            if (!this.appVars.has_pro) {
                this.noticeModal = true;
                this.noticeTitle = eventType == 'collective' ? this.$t('Collective') : this.$t('Round Robin');
                return;
            }
            this.$router.push({
                name: 'create_slot_event',
                params: {calendar_id: this.calendar.id, event_type: eventType},
                query: {team_members: this.teamMembers}

            })
        },
        createEventCalendar(eventType) {
            if (!this.appVars.has_pro) {
                this.noticeModal = true;
                this.noticeTitle = this.$t('Single Event');
                return;
            }
            this.$router.push({
                name: 'create_slot_event',
                params: {calendar_id: this.calendar.id, event_type: eventType},
                query: {team_members: this.eventMembers}
            })
        },
        goToEvent(slot) {
            this.$router.push({ 
                name: 'event_details',
                params: {calendar_id: slot.calendar_id, event_id: slot.id},
            })
        },
        getCalendarId(eventId) {
            const event = this.calendarEvents.find(event => event.options.some(option => option.id == eventId));
            return event.id;
        },
        maybeCreateGroupSlot() {
            if (this.appVars.has_pro) {
                this.createCalendarEvent('group');
            } else {
                this.noticeModal = true;
                this.noticeTitle = this.$t('Group Event');
            }
        },
        handleCommand(command) {
            if (command == 'clone') {
                this.isCloneOpen = true;
                return;
            }

            if (command == 'copy') {
                copyToClipBoard(this.calendar.public_url);
                this.$handleSuccess(this.$t('Link Copied'));
                return;
            }

            if (command == 'reorder') {
                this.reorderModal = true;
                return;
            }

            if (command == 'export') {
                this.exportCalendar();
                return;
            }

            if (command == 'delete') {
                this.$confirm(this.$t('CalendarEventBlock/delete_confirmation_desc'), this.$t('Delete Calendar'), {
                    confirmButtonText: this.$t('Delete'),
                    cancelButtonText: this.$t('Cancel'),
                    type: 'warning'
                }).then(() => {
                    this.$del('calendars/' + this.calendar.id, {
                        calendar_id : this.calendar.id,
                    })
                        .then(response => {
                            this.$handleSuccess(response);
                            this.$emit('fetchCalendar');
                        })
                        .catch(errors => {
                            this.$handleError(errors);
                        });
                })
            }
        },
        cloneEvent() {
            this.saving = true;
            const cloneEventCalId = this.getCalendarId(this.cloneEventId);
            this.$post('calendars/' + cloneEventCalId + '/clone-event/' + this.cloneEventId, {
                calendar_id: this.cloneEventCalId,
                new_calendar_id: this.calendar.id
            })
                .then(response => {
                    this.isCloneOpen = false;
                    this.$handleSuccess(response.message);
                    this.goToEvent(response.slot);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        exportCalendar() {
            location.href = window.ajaxurl + '?' + jQuery.param({
                action: 'fluent_booking_export_calendar',
                calendar_id: this.calendar.id
            });
        },
    },
    mounted() {
        if (this.calendar.type == 'team') {
            this.calendarEvents = this.eventLists.teams;
        } else if (this.calendar.type == 'event'){
            this.calendarEvents = this.eventLists.events;
        } else {
            this.calendarEvents = this.eventLists.hosts;
        }
    }
}
</script>
