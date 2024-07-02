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
                                    <el-icon>
                                        <SwitchButton/>
                                    </el-icon>
                                    {{ $t('Disable') }}
                                </el-dropdown-item>
                                <el-dropdown-item command="enable" v-else>{{ $t('Enable this event') }}</el-dropdown-item>
                                <el-dropdown-item command="clone">
                                    <el-icon>
                                        <CopyDocument/>
                                    </el-icon>
                                    {{ $t('Clone') }}
                                </el-dropdown-item>
                                <el-dropdown-item command="delete">
                                    <el-icon>
                                        <Delete/>
                                    </el-icon>
                                    {{ $t('Delete') }}
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
            </h3>
            <div v-if="isTeam">
                <div v-if="slot.author_profiles" class="fcal_author_avatars">
                    <div v-for="author in slot.author_profiles" class="fcal_author">
                        <img class="fcal_author_avatar" :src="author.avatar">
                        <div class="fcal_author_tooltip">
                            <span>{{ author.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <p class="fcal_slot_meta">
                <span class="fcal_slot_meta_mins"><el-icon><Clock/></el-icon>{{ getDuration(slot.duration) }}</span>
                <span class="fcal_slot_meta_event">
                    <span class="icons" :class="isTeam ? 'round-robin-icons' : ''">
                        <span class="left-icons">
                            <el-icon><User/></el-icon>
                            <el-icon v-if="isEventCalendar"><User/></el-icon>
                            <el-icon v-if="isTeam"><User/></el-icon>
                            <el-icon v-if="isTeam"><User/></el-icon>
                        </span>
                        <el-icon><Right/></el-icon>
                        <span class="right">
                            <el-icon><User/></el-icon>
                            <el-icon v-if="isGroup || isGroupEvent" class="last-icon"><User/></el-icon>
                        </span>
                    </span> {{ getEventType(eventType) }}
                </span>
                <span v-if="slot.price_total" class="fcal_slot_meta_event">
                    <el-icon><CreditCard/></el-icon>
                    <span>{{currencyFormat(slot.price_total)}}</span>
                </span>
                <span v-else-if="slot.type == 'woo'">
                    <el-icon>
                        <img style="width: 22px; height: 22px;" :src="appVars.asset_url + 'images/woo.svg'"/>
                    </el-icon>
                </span>
            </p>
        </div>
        <div v-if="isLocationDisabled" class="fcal_slot_error">{{ $t('EachSlot/disabled_location_description')}}</div>
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
            @closeShare="closeShareCalendar"
        />
    </div>
</template>

<script type="text/babel">
import {copyToClipBoard} from '@/Bits/data_config.js';
import {
    CopyDocument,
    More,
    Share,
    ArrowDown,
    User,
    Clock,
    Right,
    EditPen,
    Delete,
    SwitchButton,
    CreditCard
} from '@element-plus/icons-vue';
import ShareCalendarBlock from './ShareCalendarBlock';

export default {
    name: 'EachSlot',
    props: ['slot', 'calendarId'],
    $emits: ['slotDeleted'],
    components: {
        CopyDocument,
        More,
        Share,
        ArrowDown,
        User,
        Clock,
        Right,
        EditPen,
        Delete,
        SwitchButton,
        CreditCard,
        ShareCalendarBlock
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
            return this.eventType == 'round_robin' || this.eventType == 'collective';
        },
        isGroup() {
            return this.eventType == 'group';
        },
        isGroupEvent() {
            return this.eventType == 'group_event';
        },
        isEventCalendar() {
            return this.eventType == 'single_event' || this.eventType == 'group_event';
        },
        isLocationDisabled() {
            let isDisabled = false;
            this.slot.location_settings.forEach((location) => {
                if (this.slot.location_fields?.conferencing?.options[location.type]?.disabled) {
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
                'single': 'One-to-One',
                'group': 'Group',
                'round_robin': 'Round Robin',
                'collective': 'Collective',
                'single_event': 'Single Event',
                'group_event': 'Group Event'
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
                this.$confirm(this.$t('Are you sure you want to delete this booking type? All the associate bookings and data will be deleted'),
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
                                this.$emit('slotDeleted');
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
