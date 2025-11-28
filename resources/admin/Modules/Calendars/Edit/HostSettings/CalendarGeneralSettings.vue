<template>
    <div class="fcal_settings_landing_page">
        <div class="fcal_settings_header">
            <div class="fcal_settings_head">
                <h2>{{ $t('General Host Settings') }}</h2>
                <p class="short-desc">{{ $t('Manage general settings for this calendar') }}</p>
            </div>
            <div class="fcal_settings_actions">
                <el-button v-if="settings.enabled" @click="openShare = true" class="fcal_plain_btn">
                    <el-icon><Share /></el-icon> {{ $t('Share') }}
                </el-button>
            </div>
        </div>
        <div v-loading="loading" class="fcal_settings_body">

            <div style="padding: 20px;background: #ffdcd1; margin: 10px 0 20px;" v-if="calendar.author_profile && !calendar.author_profile.ID">
                <h3 style="margin: 0;">Connected Host user is missing</h3>
                <p>FluentBooking could not retrive the connected host user in WP Users Database Table. Most probably the user was deleted. You may delete this calendar from all calendars screen.</p>
            </div>

            <el-form v-model="settings" label-position="top">
                <el-row :gutter="30">
                    <el-col :span="12">
                        <el-form-item :label="$t('Calendar Avatar')">
                            <photo-widget style="width: 100%;" v-model="calendar.author_profile.avatar">
                                <template #after>
                                    <el-button size="small" class="fcal_remove_btn"
                                        v-if="calendar.author_profile.avatar"
                                        :disabled="saving"
                                        @click="removeAvatar">
                                        <el-icon><CloseBold /></el-icon>
                                    </el-button>
                                </template>
                            </photo-widget>
                            <p class="fcal_input_desc">{{ $t('Recommended Image Size: 600x600. Square Orientation') }}</p>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="$t('Featured Image')">
                            <photo-widget class="fcal_featured_image_upload" style="width: 100%;" v-model="calendar.author_profile.featured_image">
                                <template #after>
                                    <el-button size="small" class="fcal_remove_btn"
                                        v-if="calendar.author_profile.featured_image"
                                        :disabled="saving"
                                        @click="removeFeaturedImage">
                                        <el-icon><CloseBold /></el-icon>
                                    </el-button>
                                </template>
                            </photo-widget>
                            <p class="fcal_input_desc">{{ $t('Will be shown on landing page social share meta or profile block') }}</p>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-form-item :label="$t('Host Name / Calendar Title')">
                    <el-input
                        v-model="calendar.title"
                        type="text"
                        :placeholder="$t('Enter Name of this calendar')"
                    />
                    <p class="fcal_input_desc" v-if="calendar.type == 'simple'">{{ $t('Should be same as the host name') }}</p>
                </el-form-item>
                <el-form-item v-if="calendar.type == 'simple'" :label="$t('Host Phone (with country code)')">
                    <el-input
                        v-model="calendar.author_profile.phone"
                        type="text"
                        :placeholder="$t('Enter the Host Phone Number')"
                    />
                    <p class="fcal_input_desc">{{ $t('This number will be used for sending sms notification') }}</p>
                </el-form-item>
                <el-form-item :label="$t('Host Timezone')" class="fcal_global_timezone">
                    <TimeZoneSelector v-model="calendar.author_timezone"/>
                </el-form-item>
                <el-form-item :label="$t('About')">
                    <el-input
                        v-model="calendar.description"
                        type="textarea"
                        :rows="3"
                        :placeholder="$t('Enter description for this person / calendar')"
                    />
                    <p class="fcal_input_desc">{{ $t('Will be shown on your calendar landing page / team block UI') }}</p>
                </el-form-item>
                <el-form-item>
                    <el-checkbox true-label="yes" false-label="no" v-model="settings.enabled">{{ $t('Enable Landing Page Features for this calendar') }}</el-checkbox>
                </el-form-item>
                <template v-if="settings.enabled == 'yes'">
                    <el-form-item :label="$t('Which Booking Forms to Show?')">
                        <el-radio-group v-model="settings.show_type">
                            <el-radio label="all">{{ $t('All Active Booking Forms') }}</el-radio>
                            <el-radio label="selected_only">{{ $t('Only Selected Active Booking Types') }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item v-if="settings.show_type == 'selected_only'"
                                  :label="$t('Please select which Booking Forms to show in the page?')">
                        <el-checkbox-group class="fcal_radio_lined" v-model="settings.enabled_slots">
                            <el-checkbox v-for="slot in calendar.slots" :key="slot.id" :label="slot.id">{{
                                    slot.title
                                }}
                            </el-checkbox>
                        </el-checkbox-group>
                    </el-form-item>
                </template>
                <el-form-item>
                    <el-button
                        @click="saveSettings()"
                        :disabled="saving"
                        v-loading="saving"
                        type="primary">
                        {{ $t('Save Settings') }}
                    </el-button>
                </el-form-item>
            </el-form>
        </div>
        <ShareCalendarBlock 
            v-if="openShare" 
            :slot="getShareSlot()"
            :openShare="openShare"
            :calendarId="calendar.id"
            :isAuthorCalendars="true"
            @closeShare="openShare = false"
        />
    </div>
</template>

<script>
import { CloseBold, Share } from '@element-plus/icons-vue';
import PhotoWidget from '@/Pieces/PhotoWidget.vue';
import TimeZoneSelector from '../../parts/TimeZoneSelector.vue';
import ShareCalendarBlock from '../../parts/ShareCalendarBlock.vue';

export default {
    name: 'LandingPageCalendarSettings',
    props: ['calendar'],
    components: {
        Share,
        PhotoWidget,
        TimeZoneSelector,
        CloseBold,
        ShareCalendarBlock
    },
    data() {
        return {
            loading: false,
            settings: {},
            saving: false,
            share_url: '',
            openShare: false
        }
    },
    methods: {
        getShareSlot() {
            return {
                ...this.calendar,
                public_url: this.share_url
            };
        },
        removeAvatar() {
            this.calendar.author_profile.avatar = null;
            this.saveSettings();
        },
        removeFeaturedImage() {
            this.calendar.author_profile.featured_image = null;
            this.saveSettings();
        },
        fetchSettings() {
            this.loading = true;
            this.$get('calendars/' + this.calendar.id + '/sharing-settings', {
                    calendar_id : this.calendar.id
                })
                .then(response => {
                    this.settings = response.settings;
                    this.share_url = response.share_url;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        saveSettings() {
            this.saving = true;
            this.$post('calendars/' + this.calendar.id + '/sharing-settings', {
                calendar_id : this.calendar.id,
                landing_page_settings: this.settings,
                calendar_data: {
                    description: this.calendar.description,
                    title: this.calendar.title,
                    phone: this.calendar.author_profile.phone,
                    timezone: this.calendar.author_timezone,
                    calendar_avatar: this.calendar.author_profile.avatar,
                    featured_image: this.calendar.author_profile.featured_image
                }
            })
                .then(response => {
                    this.$notify.success(response.message);
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
        this.fetchSettings();
    }
}
</script>
