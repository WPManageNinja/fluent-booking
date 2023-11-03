<template>
    <div :class="'fcal_status_'+slot.status">
        <div class="fcal_slot_body">
            <h3>
                <span class="fcal_status_badge" :style="{background: slot.color_schema}"></span> {{ slot.title }}
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
            <p class="fcal_slot_meta">
                <span class="fcal_slot_meta_mins"><el-icon><Clock/></el-icon> {{ slot.duration }} {{ $t('minutes') }}</span>
                <span class="fcal_slog_meta_event">
                    <span class="icons">
                        <el-icon><User/></el-icon>
                        <el-icon><Right/></el-icon>
                        <span class="right">
                            <el-icon><User/></el-icon>
                            <el-icon class="last-icon" v-if="slot.event_type == 'group'"><User/></el-icon>
                        </span>
                    </span> {{ eventType }}
                </span>
                <span v-if="slot.price_total" class="fcal_slog_meta_event">
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
            shareSlot: null
        }
    },
    computed: {
        eventType() {
            return this.slot.event_type == 'group' ? this.$t('Group') : this.$t('One-to-One');
        }
    },
    methods: {
        editSlot() {
            this.$router.push({
                name: 'slot_settings',
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
            this.$put('calendars/' + this.slot.calendar_id + '/slots/' + this.slot.id, {
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
            this.$post('calendars/' + this.slot.calendar_id + '/clone-slot/' + this.slot.id)
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
                name: 'slot_settings', 
                params: {calendar_id: slot.calendar_id, event_id: slot.id},
                query: {step: 'basic-info' }
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
                        this.$del('calendars/' + this.slot.calendar_id + '/slots/' + this.slot.id)
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
    }
}
</script>
