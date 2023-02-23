<template>
    <div class="fcal_event_block">
        <div class="fcal_cal_header">
            <div class="fcal_cal_title">
                <img :src="calendar.author_profile.avatar" />
                <div class="fcal_cal_info">
                    <h3>{{calendar.author_profile.name}}</h3>
                </div>
            </div>
            <div class="fcal_cal_actions">
                <el-button @click="$router.push({ name: 'create_slot_event', params: { calendar_id: calendar.id } })" type="primary">+ New Booking Type</el-button>
            </div>
        </div>
        <div class="fcal_cal_slots">
            <div v-for="slot in calendar.slots" :key="slot.id" class="fcal_cal_slot">
                <div class="fcal_slot_card">
                    <div class="fcal_slot_body">
                        <h3>{{slot.title}}</h3>
                        <p class="fcal_slot_meta">{{slot.duration}} mins, One-on-One</p>
                    </div>
                    <div class="fcal_slot_footer">
                        <div v-if="slot.status == 'active'" class="fcal_shortcode">
                            <el-button @click="copyTo(slot.public_url)" text>
                                <el-icon><CopyDocument /></el-icon>
                                <span>Copy Link</span>
                            </el-button>
                        </div>
                        <div v-else>
                            <el-button v-loading="working" :disabled="working" @click="activateSlot(slot)" text>Turn On</el-button>
                        </div>
                        <div class="fcal_slot_actions">
                            <el-button @click="$router.push({ name: 'slot_settings', params: { calendar_id: slot.calendar_id, slot_id: slot.id } })" type="default">Settings</el-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import {copyToClipBoard} from '@/Bits/data_config.js';
import {CopyDocument} from '@element-plus/icons-vue';

export default {
    name: 'CalendarEventBlock',
    props: ['calendar'],
    components: {
        CopyDocument
    },
    data() {
        return {
            working: false,
            isCopied: false
        }
    },
    methods: {
        activateSlot(slot) {
            this.working = true;
            this.$put('calendars/' + this.calendar.id + '/slots/' + slot.id, {
                status: 'active'
            })
                .then(response => {
                    slot.status = 'active';
                    this.$notify.success('Slot has been activated successfully');
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.working = false;
                });
        },
        copyTo(text) {
            copyToClipBoard(text);
            this.isCopied = true;
            this.$notify.success('URL has been copied to your clipboard');

            setTimeout(() => {
                this.isCopied = false;
            }, 8000);
        }
    }
}
</script>
