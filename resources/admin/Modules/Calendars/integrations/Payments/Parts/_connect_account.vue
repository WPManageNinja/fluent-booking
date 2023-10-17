<template>
    <div class="fc-connect-details">
        <h4 class="fc-connect-mode"></h4>
        <div v-if="!connect || connect.error" class="fc-connect-require">
            <h3> {{ 'Connect Your Stripe Account to your website to accept Payments' }}</h3>
            <a :href="connect_config[mode+'_redirect']" class="el-button is-plain el-button--primary el-button--large">
                <img class="w-5.5 mr-2.5" :src="connect_config.image_url"/> Connect with stripe
            </a>
        </div>
        <div v-else class="fc-connect-ok bg-success-100 rounded-xs p-5 dark:border-solid dark:border-green-500/30 dark:bg-gray-700">
            <h3 class="mt-0">Your Stripe Account is connected</h3>
            <h4>{{ connect.display_name }}</h4>
            <h4> {{ connect.email }} - Administrator (Owner)</h4>
            <el-popconfirm width="300"
                confirm-button-text='Confirm'
                cancel-button-text='No, Thanks'
                icon="el-icon-info"
                icon-color="red"
                title="Are you sure to disconnect this account?"
                position="top"
                @confirm="disconnect">
                <template #reference>
                    <el-button size="default" type="danger" slot="reference">Disconnect</el-button>
                </template>
            </el-popconfirm>
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'ConnectAccount',
    props: ['connect', 'connect_config', 'mode', 'method'],
    data() {
        return {
            saving: false
        }
    },
    methods: {
        disconnect() {
            this.saving = true;
            this.$post('integrations/settings/payment-methods/disconnect', {
                method: this.method,
                mode: this.mode
            })
                .then((response) => {
                    this.saving = false;
                    this.$emit('reload_settings', true);
                })
        }
    }
}
</script>
