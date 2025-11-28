<template>
    <div>
        <div class="fcal_calda_header">
            <h3>{{ driver.caldav_settings.heading }}</h3>
            <p v-html="driver.caldav_settings.description"></p>

            <div class="fcal_secure_form">
                <el-form v-model="settings" label-position="top">
                    <el-form-item :label="driver.caldav_settings.username_label">
                        <el-input v-model="settings.username">
                            <template #prepend>
                                <el-icon>
                                    <Lock/>
                                </el-icon>
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item :label="driver.caldav_settings.password_label">
                        <el-input v-model="settings.password">
                            <template #prepend>
                                <el-icon>
                                    <Lock/>
                                </el-icon>
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button @click="saveSettings()" type="success" :disabled="saving" v-loading="saving">
                            {{ driver.caldav_settings.button_text }}
                        </el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'CalDavAuth',
    props: ['calendar', 'driver'],
    data() {
        return {
            settings: {
                username: '',
                password: ''
            },
            saving: false
        }
    },
    methods: {
        saveSettings() {
            if(!this.settings.username || !this.settings.password) {
                this.$notify.error(this.$t('Please enter username and password'));
                return;
            }

            this.saving = true;

            this.$post('calendars/' + this.calendar.id + '/integrations/remote-calendars/cal-dav-auth', {
                calendar_id: this.calendar.id,
                driver_key: this.driver.key,
                settings: this.settings
            })
                .then(response => {
                    this.$notify.success(response.message);
                    this.$emit('refetch');
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        }
    }
}
</script>
