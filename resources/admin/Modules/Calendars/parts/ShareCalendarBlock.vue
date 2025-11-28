<template>
    <el-dialog
        v-model="showShare"
        :append-to-body="true"
        class="fcal_dialog fcal_share_event_dialog"
        >
         <template #header>
            <h3>{{ eventTitle }}</h3>
    
            <p v-if="!isAuthorCalendars" class="fcal_slot_meta">
                <span class="fcal_slot_meta_mins">
                    <el-icon><Clock /></el-icon> {{ getDuration(slot.duration) }}
                </span>
                <span class="fcal_slot_meta_event">
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
                    {{ $t('Shortcode') }}
                </template>
                <div v-if="activeTab == 'copy-shortcode'" class="fcal_create_calendar_body" style="text-align: center;">
                    <p>{{ $t('ShareCalendarBlock/copy_and_use_shortcode_desc') }}</p>
                    <el-button class="fcal_plain_btn fcal_copy_btn" @click="copyShortcode()">
                        <el-icon><CopyDocument /></el-icon> {{ getShortcode() }}
                    </el-button>
                </div>
            </el-tab-pane>
            <el-tab-pane name="landing-page">
                <template #label>
                    {{ $t('Landing Page') }}
                </template>
                <div v-if="activeTab == 'landing-page'" class="fcal_create_calendar_body">
                    <div v-if="slot.public_url">
                        <el-input
                            v-model="slot.public_url"
                            :disabled="true">
                            <template #append>
                                <a target="_blank" :href="slot.public_url">
                                    <el-button type="default">
                                        <el-icon><Link /></el-icon>
                                        {{ $t('View') }}
                                    </el-button>
                                </a>                                
                                <el-button type="default" @click="copyLandingPageUrl(slot.public_url)">
                                    <el-icon><CopyDocument /></el-icon> {{ $t('Copy') }}
                                </el-button>
                            </template>
                        </el-input>
                    </div>
                    <div v-else>
                        <p>{{ $t('To get the landing page url, please enable from') }}
                            <span @click="gotoCalendarSettings"><el-link>{{ $t('here') }}</el-link></span>
                        </p>
                        <img :src="appVars.asset_url+'images/calendar-settings.png'">
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane name="block">
                <template #label>
                    {{ $t('Add to Block') }}
                </template>
                <div v-if="activeTab == 'block'" class="fcal_create_calendar_body">
                    <div class="fcal_create_calendar_form_footer">
                        <p>{{ $t('Add Calendar to Gutenberg Block') }}</p>
                        <img v-if="isAuthorCalendars" :src="appVars.asset_url+'images/calendar-block.webp'">
                        <img v-else :src="appVars.asset_url+'images/gutenberg.png'">
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane v-if="!isAuthorCalendars" name="generate-link">
                <template #label>
                    {{ $t('Generate Link') }}
                </template>
                <GenerateLinkTab
                    v-if="activeTab == 'generate-link'"
                    :slot="slot"
                />
            </el-tab-pane>
            <el-tab-pane name="embed">
                <template #label>
                    {{ $t('Embed') }}
                </template>
                <div class="fcal_create_calendar_body">
                    <el-form v-if="activeTab == 'embed'" label-position="top">
                        <el-form-item :label="$t('Embed via HTML Code')">
                            <span class="fcal_form_input_desc">{{ $t('Place this code in your HTML where you want to embed the calendar.') }}</span>
                            <el-input
                                :autosize="{ minRows: 3, maxRows: 5 }"
                                type="textarea"
                                class="fcal_disabled"
                                :disabled="true"
                                :value="gethtmlCode()">
                            </el-input>
                            <p class="fcal_input_hint">
                                {{ $t('Note: please make sure if your wp hosting server supports iframe.') }}
                            </p>
                        </el-form-item>
                    </el-form>
                </div>
            </el-tab-pane>
        </el-tabs>
        <template #footer>
            <div class="dialog-footer">
                <el-button class="fcal_plain_btn" @click="showShare = false">
                    {{ $t('Close') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

 <script>
import { Clock, Link } from '@element-plus/icons-vue';
import { copyToClipBoard } from '@/Bits/data_config.js';
import GenerateLinkTab from './_GenerateLinkTab.vue';

export default {
    name: 'ShareCalendarBlock',
    props: ['slot', 'openShare', 'calendarId', 'isAuthorCalendars'],
    emits: ['closeShare'],
    components: {
        Clock,
        Link,
        GenerateLinkTab
    },
    data() {
        return {
            showShare: this.openShare,
            activeTab: 'copy-shortcode',
            durationLookup: []
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
            const eventTypes = {
                'single': this.$t('One-to-One'),
                'group': this.$t('Group'),
                'round_robin': this.$t('Round Robin'),
                'collective': this.$t('Collective'),
                'single_event': this.$t('Single Event'),
                'group_event': this.$t('Group Event'),
            }
            return eventTypes[this.slot.event_type];
        },
        getDuration() {
            return (duration) => {
                return this.durationLookup[duration] || duration + ' ' + this.$t('Minutes');
            }
        }
    },
    methods: {
        gotoCalendarSettings() {
            if (this.isAuthorCalendars) {
                this.showShare = false;
                return;
            }
            this.$router.push({
                name: 'calendar_settings',
                params: { calendar_id: this.calendarId}
            })
        },
        copyShortcode() {
            const CopyText = this.getShortcode();
            copyToClipBoard(CopyText);
            this.$handleSuccess(this.$t('Shortcode has been copied to your clipboard'));
        },
        copyLandingPageUrl(text) {
            copyToClipBoard(text);
            this.$handleSuccess(this.$t('Copied to clipboard'));
        },
        gethtmlCode() {
            const url = this.slot?.public_url + '&embedded=1';
            return this.appVars?.iframe_html?.replace('##landing_page_url##', url) || '';
        },
        copyHtmlCode() {
            copyToClipBoard(this.gethtmlCode());
            this.$handleSuccess(this.$t('Copied to clipboard'));
        },
        getShortcode() {
            if (this.isAuthorCalendars) {
                return '[fluent_booking_calendar calendar_id="'+this.calendarId+'"]';
            }
            return '[fluent_booking id="'+this.slot?.id+'"]';
        }
    },
    mounted() {
        if (!this.isAuthorCalendars) {
            if (this.slot.settings?.multi_duration?.enabled) {
                this.slot.duration = this.slot.settings.multi_duration.default_duration;
                this.durationLookup = this.appVars.multi_duration_lookup;
            } else {
                this.slot.duration = this.slot.duration == 'custom' ? this.slot.custom_duration : this.slot.duration;
                this.durationLookup = this.appVars.duration_lookup;
            }
        }
    }
}
</script>
