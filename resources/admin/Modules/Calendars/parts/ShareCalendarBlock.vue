<template>
    <el-dialog
        v-model="showShare"
        :append-to-body="true"
        class="fcal_dialog fcal_share_event_dialog"
        >
         <template #header>
            <h3>{{ eventTitle }}</h3>
    
            <p class="fcal_slot_meta">
                <span class="fcal_slot_meta_mins">
                    <el-icon><Clock /></el-icon> {{ slot.duration }} minutes
                </span>
                <span class="fcal_slog_meta_event">
                    {{ eventType }}
                </span>
            </p>
         </template>
        <el-tabs
            v-model="activeTab"
            tab-position="top"
            class="fcal_tabs"
        >
            <el-tab-pane name="copy-shortcode">
                <template #label>
                    Shortcode
                </template>
                <div v-if="activeTab == 'copy-shortcode'" class="fcal_create_calendar_body" style="text-align: center;">
                    <p>Copy and use the shortcode Page/Post of your website</p>
                    <el-button class="fcal_plain_btn fcal_copy_btn" @click="copyTo(slot?.id)">
                        <el-icon><CopyDocument /></el-icon> [fluent_booking id="{{ slot?.id }}"]
                    </el-button>
                </div>
            </el-tab-pane>
            <el-tab-pane name="landing-page">
                <template #label>
                    Landing Page
                </template>
                <div v-if="activeTab == 'landing-page'" class="fcal_create_calendar_body">
                    <div v-if="slot.public_url">
                        <el-input
                            v-model="slot.public_url"
                            :disabled="true">
                            <template #append>
                                <a target="_blank" :href="slot.public_url">
                                    <el-button type="default">
                                        <el-icon><Link /></el-icon> View
                                    </el-button>
                                </a>                                
                                <el-button type="default" @click="copyLandingPageUrl(slot.public_url)">
                                    <el-icon><CopyDocument /></el-icon> Copy
                                </el-button>
                            </template>
                        </el-input>
                    </div>
                    <div v-else>
                        <p>To get the landing page url, please enable from 
                            <span @click="gotoCalendarSettings"><el-link>here</el-link></span>
                        </p>
                        <img :src="appVars.asset_url+'images/calendar-settings.png'">
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane name="block">
                <template #label>
                    Add to Block
                </template>
                <div v-if="activeTab == 'block'" class="fcal_create_calendar_body">
                    <div class="fcal_create_calendar_form_footer">
                        <p>Add Calendar to Gutenberg Block</p>
                        <img :src="appVars.asset_url+'images/gutenberg.png'">
                    </div>
                </div>
            </el-tab-pane>
        </el-tabs>
        <template #footer>
            <div class="dialog-footer">
                <el-button class="fcal_plain_btn" @click="showShare = false">
                    Close
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

 <script>
import { Clock, Link, CopyDocument } from '@element-plus/icons-vue';
import { copyToClipBoard } from '@/Bits/data_config.js';

export default {
    name: 'ShareCalendarBlock',
    props: ['slot', 'openShare', 'calendarId'],
    emits: ['closeShare'],
    components: {
        Clock,
        Link,
        CopyDocument
    },    
    data() {
        return {
            showShare: this.openShare,
            activeTab: 'copy-shortcode',
        }
    },
    watch: {
        showShare() {
            this.$emit('closeShare');
        }
    },
    computed: {
        eventTitle() {
            return this.slot.title;
        },
        eventType() {
            return this.slot.event_type == 'group' ? 'Group' : 'One-to-One';
        }
    },
    methods: {
        gotoCalendarSettings() {
            this.$router.push({
                name: 'calendar_settings', 
                params: {id: this.calendarId}
            })
        },
        copyTo(text) {
            const CopyText = '[fluent_booking id="'+text+'"]';
            copyToClipBoard(CopyText);
            this.$handleSuccess('Shortcode has been copied to your clipboard');
        },
        copyLandingPageUrl(text) {
            copyToClipBoard(text);
            this.$handleSuccess('Copied to clipboard');
        }
    }
}
</script>
