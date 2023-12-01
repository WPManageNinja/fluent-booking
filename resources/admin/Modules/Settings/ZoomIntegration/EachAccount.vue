<template>
    <div class="fcal_remote_header">
        <div class="fcal_driver_brand">
            <img :src="connectedAccount.owner.avatar"/>
            <div class="fcal_driver_heading">
                <h3>{{ connectedAccount.owner.name }}</h3>
                <p>{{ $t('Connected Zoom Account Email:') }} <em>{{ connectedAccount.zoom_email }}</em></p>
            </div>
        </div>
        <div class="fcal_driver_action">
            <el-button v-loading="disconnecting" :disabled="disconnecting"
                       @click="disconnect()" type="danger" plain>
                {{ $t('Disconnect') }}
            </el-button>
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'EachZoomAccount',
    props: ['connectedAccount', 'calendar_id'],
    $emits: ['disconnected'],
    data() {
        return {
            disconnecting: false
        }
    },
    methods: {
        disconnect() {
            this.disconnecting = true;

            let url = 'integrations/zoom/disconnect';

            if (this.calendar_id) {
                url = 'calendars/' + this.calendar_id + '/integrations/zoom-connection/disconnect';
            }

            this.$post(url, {
                calendar_id : this.calendar_id,
                connected_id: this.connectedAccount.id
            })
                .then(response => {
                    this.$notify.success(response.message);
                    this.$emit('disconnected');
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.disconnecting = false;
                });
        }
    }
}
</script>
