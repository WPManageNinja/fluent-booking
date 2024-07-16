<template>
    <div v-if="requireUpgrade" class="fb_require_upgrade">
        <h3>FluentBooking Core Plugin is required</h3>
        <p>You are using FluentBooking Pro version which is required the base core plugin. Please install FluentBooking
            Core plugin</p>
        <el-button @click="install()" v-loading="installing" :disabled="installing" type="primary">
            Install FluentBooking Core Plugin
        </el-button>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'UpgradationBlock',
    data() {
        return {
            requireUpgrade: false,
            installing: false
        }
    },
    methods: {
        install() {
            this.installing = true;
            this.$post('settings/install-plugin', {
                plugin_id: 'fluent-booking'
            })
                .then((response) => {
                    this.$notify.success('FluentBooking Core Plugin installed successfully');
                    // Reload the page
                    window.location.reload();
                })
                .catch((errors) => {
                    this.$notify.error('Failed to install FluentBooking Core Plugin');
                })
                .finally(() => {
                    this.installing = false;
                });
        }
    },
    mounted() {
        this.requireUpgrade = this.appVars.require_upgrade;
    }
}
</script>

<style lang="scss" scoped>
.fb_require_upgrade {
    padding: 20px;
    background: #ffeee9;
    border: 1px solid #FF9800;
    border-radius: 5px;
    margin-bottom: 20px;

    h3 {
        font-size: 18px;
        margin: 0;
    }
}
</style>
