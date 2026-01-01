<template>
    <div :class="'fcal_cal_slot_wrapper ' + 'fcal_status_'+slot.status">
        <div class="fcal_slot_body">
            <h3>
                <span class="fcal_status_badge" :style="{background: slot.color_schema}"></span> <span class="fcal_slot_title" @click="editSlot">{{ slot.title }}</span>
                <div class="fcal_slot_config">
                    <el-dropdown @command="handleCommand" trigger="click" popper-class="fcal_select">
                        <el-icon class="fcal_slog_setting_icon">
                            <More/>
                        </el-icon>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item command="disable" v-if="slot.status == 'active'">
                                    <el-icon><SwitchButton/></el-icon> {{ $t('Disable') }}
                                </el-dropdown-item>
                                <el-dropdown-item command="enable" v-else>
                                    <el-icon><SwitchButton/></el-icon> {{ $t('Enable') }}
                                </el-dropdown-item>
                                <el-dropdown-item command="clone">
                                    <el-icon><CopyDocument/></el-icon> {{ $t('Clone') }}
                                </el-dropdown-item>
                                <el-dropdown-item command="delete">
                                    <el-icon><Delete/></el-icon> {{ $t('Delete') }}
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
            </h3>
            <div v-if="isMultiHostEvent">
                <div v-if="slot.author_profiles" class="fcal_author_avatars">
                    <div v-for="author in slot.author_profiles" class="fcal_author">
                        <img class="fcal_author_avatar" :src="author.avatar">
                        <div class="fcal_author_tooltip">
                            <span>{{ author.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fcal_slot_meta">
                <span class="fcal_slot_meta_mins"><el-icon><Clock/></el-icon>{{ getDuration(slot.duration) }}</span>
                <span class="fcal_slot_meta_event">
                    <span class="icons" :class="eventType">
                        <span class="left-icons">
                            <template v-if="!isGroupEvent">
                                <el-icon><User/></el-icon>
                                <el-icon v-if="isTeam || isEventCalendar"><User/></el-icon>
                                <el-icon v-if="isRoundRobin"><User/></el-icon>
                            </template>
                            <div v-else class="icons">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="-mt-px mr-1 inline h-3 w-3"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            </div>
                        </span>
                        <el-icon><Right/></el-icon>
                        <span class="right">
                            <template v-if="!isGroupEvent">
                                <el-icon><User/></el-icon>
                                <el-icon v-if="isGroup" class="last-icon"><User/></el-icon>
                            </template>
                            <div v-else class="icons">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="-mt-px mr-1 inline h-3 w-3"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            </div>
                        </span>
                    </span> {{ getEventType(eventType) }}
                </span>
                <span v-if="slot.price_total" class="fcal_slot_meta_event">
                    <el-icon><CreditCard/></el-icon>
                    <span>{{ $currencyFormat(slot.price_total) }}</span>
                </span>
                <span v-else-if="slot.type == 'woo'">
                    <el-icon>
                        <img style="width: 22px; height: 22px;" :src="appVars.asset_url + 'images/woo.svg'"/>
                    </el-icon>
                </span>
            </div>
            <div v-if="isRecurringEvent && getMaxCount" class="fcal_slot_meta">
                <span class="fcal_slot_meta_event">
                    <el-icon><RepeatIcon/></el-icon>
                    {{ $t('Repeats up to') }} {{ getMaxCount }} {{ $t('times') }}
                </span>
            </div>
        </div>
        <div v-if="isLocationDisabled" class="fcal_slot_error">{{ $t('EachSlot/disabled_location_description')}}</div>
        <div v-if="slot.generic_error" v-html="slot.generic_error"></div>
        <div class="fcal_slot_footer">
            <div v-if="slot.status == 'active'" class="fcal_shortcode">
                <el-button class="fcal_plain_btn" @click="viewShareCalendar(slot)">
                    <el-icon>
                        <Share/>
                    </el-icon>
                    {{ $t('Share') }}
                </el-button>

                <el-button class="fcal_plain_btn" @click="editSlot">
                    <el-icon>
                        <EditPen/>
                    </el-icon>
                    {{ $t('Edit') }}
                </el-button>

            </div>
            <div v-else>
                <el-button
                    v-loading="updating"
                    :disabled="updating"
                    @click="updateStatus('active')"
                    class="fcal_primary_btn fcal_turn_on_btn">
                    {{ $t('Turn On') }}
                </el-button>
            </div>
        </div>
        <ShareCalendarBlock
            v-if="shareSlot"
            :slot="shareSlot"
            :openShare="openShare"
            :calendarId="calendarId"
            :isAuthorCalendars="false"
            @closeShare="closeShareCalendar"
        />
    </div>
</template>

<script>
import ShareCalendarBlock from './ShareCalendarBlock';
import { copyToClipBoard } from '@/Bits/data_config.js';
import RepeatIcon from '@/Components/Icons/RepeatIcon.vue';
import { Share, ArrowDown, User, Clock, Right, SwitchButton, CreditCard } from '@element-plus/icons-vue';

export default {
    name: 'EachSlot',
    props: ['slot', 'calendarId'],
    $emits: ['slotDeleted'],
    components: {
        Share,
        ArrowDown,
        User,
        Clock,
        Right,
        SwitchButton,
        CreditCard,
        ShareCalendarBlock,
        RepeatIcon
    },
    data() {
        return {
            updating: false,
            isCopied: false,
            openShare: false,
            shareSlot: null,
            eventType: this.slot.event_type,
            durationLookup: []
        }
    },
    computed: {
        isTeam() {
            return ['round_robin', 'collective'].includes(this.eventType);
        },
        isRoundRobin() {
            return this.eventType == 'round_robin';
        },
        isGroup() {
            return this.eventType == 'group';
        },
        isGroupEvent() {
            return this.eventType == 'group_event';
        },
        isEventCalendar() {
            return ['single_event', 'group_event'].includes(this.eventType);
        },
        isRecurringEvent() {
            return this.slot.settings?.recurring_config?.enabled;
        },
        isMultiHostEvent() {
            return this.isTeam || this.isEventCalendar;
        },
        isLocationDisabled() {
            let isDisabled = false;
            this.slot.location_settings.forEach((location) => {
                const locationField = this.slot.location_fields?.conferencing?.options[location.type];
                if (locationField?.disabled || locationField?.error) {
                    return isDisabled = true;
                }
            })
            return isDisabled;
        },
        getDuration() {
            return (duration) => {
                return this.durationLookup[duration] || duration + ' ' + this.$t('Minutes');
            }
        },
        getMaxCount() {
            return this.slot.settings?.recurring_config?.max_count || 0;
        }
    },
    methods: {
        editSlot() {
            this.$router.push({
                name: 'event_details',
                params: {calendar_id: this.slot.calendar_id, event_id: this.slot.id}
            })
        },
        viewShareCalendar(slot) {
            this.openShare = true;
            this.shareSlot = slot;
        },
        closeShareCalendar() {
            this.openShare = false;
            this.shareSlot = null;
        },
        getEventType(eventType) {
            const typeMap = {
                'single': this.$t('One-to-One'),
                'group': this.$t('Group'),
                'round_robin': this.$t('Round Robin'),
                'collective': this.$t('Collective'),
                'single_event': this.$t('Single Event'),
                'group_event': this.$t('Group Event')
            };
            return typeMap[eventType];
        },
        copyTo(text) {
            copyToClipBoard(text);
            this.isCopied = true;

            if (this.slot.public_url) {
                this.$handleSuccess(this.$t('URL has been copied to your clipboard'));
            } else {
                this.$handleSuccess(this.$t('Shortcode has been copied to your clipboard'));
            }

            setTimeout(() => {
                this.isCopied = false;
            }, 5000);
        },
        updateStatus(newStatus) {
            this.updating = true;
            this.$put('calendars/' + this.slot.calendar_id + '/events/' + this.slot.id, {
                calendar_id: this.slot.calendar_id,
                status: newStatus
            })
                .then(response => {
                    this.slot.status = newStatus;
                    this.$handleSuccess(response);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                });
        },
        cloneEvent() {
            this.updating = true;
            this.$post('calendars/' + this.slot.calendar_id + '/clone-event/' + this.slot.id, {
                calendar_id: this.slot.calendar_id
            })
                .then(response => {
                    this.$handleSuccess(response);
                    this.goToEvent(response.slot);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                });
        },
        goToEvent(slot) {
            this.$router.push({ 
                name: 'event_details', 
                params: {calendar_id: slot.calendar_id, event_id: slot.id},
            })
        },
        handleCommand(command) {
            if (command == 'enable') {
                this.updateStatus('active');
                return;
            }
            if (command == 'disable') {
                this.updateStatus('draft');
                return;
            }
            if (command == 'delete') {
                this.$confirm(this.$t('EachSlot/delete_confirmation'),
                    this.$t('Delete Booking Type'), {
                        confirmButtonText: this.$t('Delete'),
                        cancelButtonText: this.$t('Cancel'),
                        type: 'warning'
                    })
                    .then(() => {
                        this.$del('calendars/' + this.slot.calendar_id + '/events/' + this.slot.id, {
                            calendar_id: this.slot.calendar_id
                        })
                            .then(response => {
                                this.$handleSuccess(response);
                                this.$emit('slotDeleted', this.slot.id);
                            })
                            .catch(errors => {
                                this.$handleError(errors);
                            });
                    })
                return;
            }
            if (command == 'clone') {
                this.cloneEvent();
            }
        }
    },
    mounted() {
        if (this.slot.settings?.multi_duration?.enabled) {
            this.durationLookup = this.appVars.multi_duration_lookup;
        } else {
            this.durationLookup = this.appVars.duration_lookup;
        }
    },
}
</script>
