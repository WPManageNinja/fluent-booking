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
                            <el-dropdown-item command="delete"><el-icon><Delete /></el-icon>
                                {{ $t('Delete') }}
                            </el-dropdown-item>
                            <el-dropdown-item v-if="calendar.public_url" command="copy"><el-icon><Link /></el-icon>
                                {{ $t('Copy link') }}
                            </el-dropdown-item>
                            <el-dropdown-item command="clone"><el-icon><CopyDocument /></el-icon>
                                {{ $t('Clone from') }}
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>
        </div>
        <div class="fcal_cal_slots">
            <div v-if="calendar.slots.length" class="fcal_cal_slot" v-for="(slot, slotIndex) in calendar.slots" :key="slot.id">
                <each-slot @slotDeleted="slotDeleted(slotIndex)" :slot="slot" :calendarId="calendar.id"/>
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
            modal-class="fcal_drawer">
            <div class="fcal_create_new_booking_type_drawer">
                <template v-if="calendar.type == 'team'">
                    <el-button @click="createSlot('round_robin')">
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
                </template>
                <template v-else>
                    <el-button @click="createSlot('single')">
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
                    <el-button @click="createSlot('group')">
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
            modal-class="fcal_drawer">
            <div class="fcal_clone_event_drawer">
                <el-form-item :label="$t('Select Calendar Event')">
                    <el-select
                        v-model="cloneEventId"
                        :placeholder="$t('Select Schedule')"
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
    </div>
</template>

<script>
import EachSlot from "./EachSlot";
import { Setting, User, Right, MoreFilled, Delete, CopyDocument, Link } from '@element-plus/icons-vue';
import CalendarSettings from "./CalendarSettings";
import SaveButton from "../../../Components/Buttons/SaveButton.vue";
import { copyToClipBoard } from '@/Bits/data_config.js';

export default {
    name: 'CalendarEventBlock',
    props: ['calendar', 'eventLists'],
    components: {
        EachSlot,
        Setting,
        User,
        Right,
        CalendarSettings,
        MoreFilled,
        Delete,
        CopyDocument,
        Link,
        SaveButton
    },
    data() {
        return {
            saving: false,
            cloneEventId: '',
            calendarEvents: [],
            showSettings: false,
            isNewBookingOpen: false,
            isCloneOpen: false
        }
    },
    computed: {
        getSettingLabel() {
            return this.calendar.type == 'team' ? this.$t('Team Settings') : this.$t('Host Settings');
        },
        filteredEventList() {
            return this.calendarEvents.filter(calendarEvent => calendarEvent.id != this.calendar.id);
        }
    },
    methods: {
        slotDeleted(slotIndex) {
            this.calendar.slots.splice(slotIndex, 1);
        },
        goToCalendarSetting() {
            this.$router.push({
                name: 'calendar_settings',
                params: { calendar_id: this.calendar.id }
            })
        },
        createSlot(eventType) {
            this.$router.push({
                name: 'create_slot_event',
                params: {calendar_id: this.calendar.id, event_type: eventType}
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

            if (command == 'delete') {
                this.$confirm(this.$t('Are you sure you want to delete this calendar? All the associate bookings and data will be deleted'), this.$t('Delete Calendar'), {
                    confirmButtonText: this.$t('Delete'),
                    cancelButtonText: this.$t('Cancel'),
                    type: 'warning'
                }).then(() => {
                    this.$del('calendars/' + this.calendar.id, {
                        calendar_id : this.calendar.id,
                    })
                        .then(response => {
                            this.$handleSuccess(response);
                            this.$emit('fetchCalendar')
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
        }
    },
    mounted() {
        this.calendarEvents = this.calendar.type == 'team' ? this.eventLists.teams : this.eventLists.events;
    }
}
</script>
