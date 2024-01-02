<template>
    <div class="fcal_settings_landing_page">
        <div class="fcal_settings_header">
            <div class="fcal_settings_head">
                <h2>{{ $t('General Host Settings') }}</h2>
                <p class="short-desc">{{ $t('Manage general settings for this calendar') }}</p>
            </div>
            <div class="fcal_settings_actions">
                <a v-if="settings.enabled" :href="share_url" style="text-decoration: none;" target="_blank" rel="noopener noreferrer" class="fcal_plain_btn">
                    <el-icon><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" x2="21" y1="14" y2="3"></line></svg></el-icon> <span>{{ $t('View') }}</span>
                </a>
            </div>
        </div>
        <div v-loading="loading" class="fcal_settings_body">
            <el-form v-model="settings" label-position="top">
                <el-row :gutter="30">
                    <el-col :span="12">
                        <el-form-item :label="$t('Calendar Avatar')">
                            <photo-widget style="width: 100%;" v-model="calendar.author_profile.avatar" />
                            <p class="fcal_input_desc">{{ $t('Recommended Image Size: 600x600. Square Orientation') }}</p>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="$t('Featured Image')">
                            <photo-widget class="fcal_featured_image_upload" style="width: 100%;" v-model="calendar.author_profile.featured_image" />
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
    </div>
</template>

<script type="text/babel">
import {Share} from '@element-plus/icons-vue';
import PhotoWidget from '@/Pieces/PhotoWidget.vue'
export default {
    name: 'LandingPageCalendarSettings',
    props: ['calendar'],
    components: {
        Share,
        PhotoWidget
    },
    data() {
        return {
            loading: false,
            settings: {},
            saving: false,
            share_url: ''
        }
    },
    methods: {
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
