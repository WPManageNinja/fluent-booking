<template>
    <el-form label-position="top">
        <el-form-item label="Description">
            <el-input :rows="4" placeholder="Calendar Description" type="textarea"
                      v-model="calendar.description"></el-input>
        </el-form-item>

        <template v-if="hasSupport('is_hosted')">
            <el-row :gutter="20">
                <el-col :md="12" :sm="24">
                    <el-form-item label="Landing page URL">
                        <el-input placeholder="Landing page URL" v-model="calendar.slug">
                            <template #prepend>
                                <span>{{appVars.site_url}}</span>
                            </template>
                        </el-input>
                    </el-form-item>
                </el-col>
                <el-col :md="12" :sm="24">
                    <el-form-item label="Landing Page Visibility">
                        <el-radio-group v-model="calendar.visibility">
                            <el-radio label="public">Publicly Accessible</el-radio>
                            <el-radio label="private">Disable Landing Page Feature</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </el-col>
            </el-row>

            <el-row :gutter="20">
                <el-col :md="12" :sm="24">
                    <el-form-item label="Your First Name">
                        <el-input placeholder="Your First Name" v-model="calendar.author_profile.first_name"></el-input>
                    </el-form-item>
                </el-col>
                <el-col :md="12" :sm="24">
                    <el-form-item label="Your Last Name">
                        <el-input placeholder="Your Last Name" v-model="calendar.author_profile.last_name"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
        </template>

        <el-form-item>
            <el-button v-loading="saving" :disabled="saving" type="primary" @click="saveSettings()">Update Info</el-button>
        </el-form-item>
    </el-form>
</template>

<script type="text/babel">
export default {
    name: 'CalendarSettings',
    $emits: ['calendarUpdated'],
    props: ['calendar'],
    data() {
        return {
            saving: false
        }
    },
    methods: {
        saveSettings() {
            this.saving = true;
            this.$post('calendars/'+this.calendar.id, {
                description: this.calendar.description,
                author_profile: this.calendar.author_profile,
                slug: this.calendar.slug,
                visibility: this.calendar.visibility
            }).then(res => {
                if(this.hasSupport('is_hosted')) {
                    this.calendar.author_profile = res.calendar.author_profile;
                    this.calendar.public_url = res.calendar.public_url;
                }
                this.$notify.success(res.message);
                this.$emit('calendarUpdated', res);
            })
                .catch(err => {
                    this.$handleError(err);
                })
                .finally(() => {
                   this.saving = false;
                });
        }
    }
}
</script>
