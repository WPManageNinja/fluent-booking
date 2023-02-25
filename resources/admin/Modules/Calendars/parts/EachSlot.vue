<template>
    <div class="fcal_slot_card fcal_cal_slot">
        <div class="fcal_slot_body">
            <h3>{{ slot.title }}</h3>
            <p class="fcal_slot_meta">{{ slot.duration }} mins, One-on-One</p>
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
                <el-button v-loading="working" :disabled="working" @click="activateSlot(slot)" text>Turn
                    On
                </el-button>
            </div>
            <div class="fcal_slot_actions">
                <el-button
                    @click="$router.push({ name: 'slot_settings', params: { calendar_id: slot.calendar_id, slot_id: slot.id } })"
                    type="default">Settings
                </el-button>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import {copyToClipBoard} from '@/Bits/data_config.js';
import {CopyDocument} from '@element-plus/icons-vue';
export default {
    name: 'EachSlot',
    props: ['slot'],
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
        copyTo(text) {
            copyToClipBoard(text);
            this.isCopied = true;
            this.$notify.success('URL has been copied to your clipboard');

            setTimeout(() => {
                this.isCopied = false;
            }, 5000);
        },
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
        }
    }
}
</script>
