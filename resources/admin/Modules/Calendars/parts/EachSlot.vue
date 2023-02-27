<template>
    <div :class="'fcal_status_'+slot.status" class="fcal_slot_card fcal_cal_slot">
        <div class="fcal_slot_body">
            <h3 style="margin: 0">{{ slot.title }}</h3>
            <p style="margin-top: 0" class="fcal_slot_meta">{{ slot.duration }} mins, One-on-One</p>
            <p v-if="slot.public_url" class="fcal_slot_meta">
                <span v-if="slot.status == 'draft'">View Booking Page</span>
                <a :href="slot.public_url" target="_blank" rel="noopener" else>View Booking Page</a>
            </p>
            <div class="fcal_slot_config">
                <el-dropdown @command="handleCommand" trigger="click">
                    <el-button size="small" text>
                        <el-icon><Tools /></el-icon>
                    </el-button>
                    <template #dropdown>
                        <el-dropdown-menu>
                            <el-dropdown-item command="edit">Edit Booking Type Details</el-dropdown-item>
                            <el-dropdown-item command="disable" v-if="slot.status == 'active'">Disable</el-dropdown-item>
                            <el-dropdown-item command="enable" v-else>Enable this event</el-dropdown-item>
                            <el-dropdown-item command="delete" divided>Delete</el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>
        </div>
        <div class="fcal_slot_footer">
            <div v-if="slot.status == 'active'" class="fcal_shortcode">
                <el-button @click="copyTo(slot.public_url)" text>
                    <el-icon>
                        <CopyDocument/>
                    </el-icon>
                    <span v-if="!isCopied">Copy Link</span>
                    <span v-else>Copied</span>
                </el-button>
            </div>
            <div v-else>
                <el-button v-loading="working" :disabled="working" @click="updateStatus('active')" text>
                    Turn On
                </el-button>
            </div>
            <div class="fcal_slot_actions">
                <el-button
                    @click="$router.push({ name: 'slot_settings', params: { calendar_id: slot.calendar_id, slot_id: slot.id } })"
                    type="default">edit
                </el-button>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import {copyToClipBoard} from '@/Bits/data_config.js';
import {CopyDocument, Tools, ArrowDown} from '@element-plus/icons-vue';
export default {
    name: 'EachSlot',
    props: ['slot'],
    $emits: ['slotDeleted'],
    components: {
        CopyDocument,
        Tools,
        ArrowDown
    },
    data() {
        return {
            working: false,
            isCopied: false
        }
    },
    methods: {
        copyTo(text) {
            copyToClipBoard(text);
            this.isCopied = true;
            this.$notify.success('URL has been copied to your clipboard');

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
                    this.$notify.success(response.message);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.working = false;
                });
        },
        handleCommand(command) {
            if(command == 'edit') {
                this.$router.push({ name: 'slot_settings', params: { calendar_id: this.slot.calendar_id, slot_id: this.slot.id } });
                return;
            }

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
                            this.$notify.success(response.message);
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
