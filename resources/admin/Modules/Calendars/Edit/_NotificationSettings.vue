<template>
    <div v-if="!loading">
        <table style="margin: 20px 0px;" class="fcal_table fcal_stripe fcal_horizontal">
            <thead>
                <tr>
                    <th>Notification Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(notification, index) in notifications" :key="index">
                    <td>{{notification.title}}</td>
                    <td>
                        <el-switch v-model="notification.enabled"></el-switch>
                        <span style="margin-left: 10px;">
                            <span v-if="notification.enabled">Enabled</span>
                            <span style="color: red;" v-else>Disabled</span>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        <el-button @click="save()" type="success" :disabled="saving" v-loading="saving">Update Notification Settings</el-button>
    </div>
    <div class="fcal_section_body" v-else>
        <el-skeleton :rows="1" animated />
        <el-skeleton :rows="5" animated />
    </div>
</template>

<script type="text/babel">
export default {
    name: 'NotificationSettings',
    props: ['slot'],
    data() {
        return {
            notifications: {},
            loading: false,
            saving: false
        }
    },
    methods: {
        fetch() {
            this.loading = true;
            this.$get('calendars/' + this.slot.calendar_id + '/slots/' + this.slot.id + '/notifications')
                .then(response => {
                    this.notifications = response.notifications;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        save() {
            this.saving = true;
            this.$post('calendars/' + this.slot.calendar_id + '/slots/' + this.slot.id + '/notifications', {
                notifications: this.notifications
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
        this.fetch();
    }
}
</script>
