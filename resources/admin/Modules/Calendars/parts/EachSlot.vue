<template>
    <div :class="'fcal_status_'+slot.status">
        <div class="fcal_slot_body">
            <h3>
                <span class="fcal_status_badge"></span> {{ slot.title }}
                <div class="fcal_slot_config">
                    <el-dropdown @command="handleCommand" trigger="click" popper-class="fcal_select">
                        <el-icon class="fcal_slog_setting_icon"><More /></el-icon>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item command="disable" v-if="slot.status == 'active'">Disable</el-dropdown-item>
                                <el-dropdown-item command="enable" v-else>Enable this event</el-dropdown-item>
                                <el-dropdown-item command="delete" class="danger">Delete</el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
            </h3>
            <p class="fcal_slot_meta">
                <span class="fcal_slot_meta_mins"><el-icon><Clock /></el-icon> {{ slot.duration }} minutes</span>
                <span class="fcal_slog_meta_event">
                    <span class="icons">
                        <el-icon><User /></el-icon>
                        <el-icon><Right /></el-icon>
                        <span class="right">
                            <el-icon><User /></el-icon>
                            <el-icon class="last-icon" v-if="slot.event_type == 'group'"><User /></el-icon>
                        </span>
                    </span> {{ slot.event_type == 'single' || 'One-to-One' ? 'One-to-One' : 'Group' }}
                </span>

            </p>
            <p v-if="slot.public_url" class="fcal_slot_meta">
                <span v-if="slot.status == 'draft'">View Booking Page</span>
                <a :href="slot.public_url" target="_blank" rel="noopener" v-else>View Booking Page</a>
            </p>
        </div>
        <div class="fcal_slot_footer">
<!--            <div class="fcal_slot_actions">-->
<!--                <el-button-->
<!--                    @click="$router.push({ name: 'slot_settings', params: { calendar_id: slot.calendar_id, slot_id: slot.id } })"-->
<!--                    class="fcal_plain_btn">-->
<!--                    <el-icon><EditPen /></el-icon> Edit-->
<!--                </el-button>-->
<!--            </div>-->

            <div v-if="slot.status == 'active'" class="fcal_shortcode">
                <el-button v-if="slot.public_url" @click="copyTo(slot.public_url)" class="fcal_copy_btn">
                    <el-icon>
                        <CopyDocument/>
                    </el-icon>
                    <span v-if="!isCopied">Copy Link</span>
                    <span v-else>Copied!</span>
                </el-button>
                <el-button v-else-if="slot.shortcode" @click="copyTo(slot.shortcode)" class="fcal_copy_btn">
                    <el-icon>
                        <CopyDocument/>
                    </el-icon>
                    <span v-if="!isCopied">Copy Shorcode</span>
                    <span v-else>Copied!</span>
                </el-button>

                <el-button class="fcal_plain_btn" @click="$router.push({ name: 'slot_settings', params: { calendar_id: slot.calendar_id, slot_id: slot.id } })">
                    <el-icon><EditPen /></el-icon> Edit
                </el-button>

            </div>
            <div v-else>
                <el-button v-loading="working" :disabled="working" @click="updateStatus('active')" text>
                    Turn On
                </el-button>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import { copyToClipBoard } from '@/Bits/data_config.js';
import { CopyDocument, More, ArrowDown, User, Clock, Right, EditPen } from '@element-plus/icons-vue';
export default {
    name: 'EachSlot',
    props: ['slot'],
    $emits: ['slotDeleted'],
    components: {
        CopyDocument,
        More,
        ArrowDown,
        User,
        Clock,
        Right,
        EditPen
    },
    data() {
        return {
            working: false,
            isCopied: false
        }
    },
    computed: {
        eventTitle() {
            // return this.appVars.event_types[this.slot.event_type].title;
        }
    },
    methods: {
        copyTo(text) {
            copyToClipBoard(text);
            this.isCopied = true;

            if(this.slot.public_url) {
                this.$handleSuccess('URL has been copied to your clipboard');
            } else {
                this.$handleSuccess('Shortcode has been copied to your clipboard');
            }

            setTimeout(() => {
                this.isCopied = false;
            }, 5000);
        },
        updateStatus(newStatus) {
            this.working = true;
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
                    this.working = false;
                });
        },
        handleCommand(command) {
            if(command == 'enable') {
                this.updateStatus('active');
                return;
            }
            if(command == 'disable') {
                this.updateStatus('draft');
                return;
            }

            if(command == 'delete') {
                this.$confirm('Are you sure you want to delete this booking type? All the associate bookings and data will be deleted', 'Delete Booking Type', {
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    type: 'warning'
                }).then(() => {
                    this.$del('calendars/' + this.slot.calendar_id + '/slots/' + this.slot.id)
                        .then(response => {
                            this.$handleSuccess(response);
                            this.$emit('slotDeleted');
                        })
                        .catch(errors => {
                            this.$handleError(errors);
                        });
                }).catch(() => {

                });
                return;
            }

            console.log(command);
        }
    }
}
</script>
