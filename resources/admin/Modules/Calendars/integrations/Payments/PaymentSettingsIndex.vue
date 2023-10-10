<template>
    <div class="fcal_settings_container">
      <div v-if="!fetching" class="fcal_settings_body_inner fcal_settings_general">
        <h1> Hello from stripe</h1>
        <Renderer
            @onSettingsChange="updateSettings"
            :route_name="route_name"
            :fields="fields"
            :settings="settings"/>

        <div class="setting-save-action">
          <el-button size="large" :loading="saving" @click="saveSettings()" type="primary">
            <el-icon class="el-icon--left"><Setting /></el-icon>
            Save Settings
          </el-button>
        </div>
      </div>
      <el-skeleton v-else animated>
        <template #template>
          <el-skeleton-item />
          <el-skeleton-item style="width: 70%" />
          <el-skeleton-item style="width: 50%" />
          <el-skeleton-item style="width: 50%" />
          <el-skeleton-item style="width: 50%" />
        </template>
      </el-skeleton>
    </div>
</template>
<script>
import Renderer from "../Payments/PaymentComponet/Renderer.vue";
export default {
  name: 'PaymentSettingsIndex',
  components: {
    Renderer
  },
  data() {
    return {
      fields: {},
      settings: {},
      saving: false,
      fetching: false,
      is_key_defined: false,
      labelPosition: 'top',
      webhook_url: '',
      pages: [],
      route_name: '',
      ipn_url: 'Blank',
      verifiedMessage: false,
      verifiedStatus: false,
      verifying: false
    }
  },
  watch: {
    $route(to, from) {
      this.getRoute();
      this.getSettings();
    }
  },
  methods: {
    updateSettings(settings) {
      this.settings = settings;
    },
    getSettings() {
      this.fetching = true;
      console.log(this.route_name);
      this.$get('integrations/settings/payment-methods', {
        method: this.route_name
      })
          .then((response) => {
            this.fetching = false;
            this.fields = response.fields;
            this.settings = response.settings;
          })
    },
    saveSettings() {
      this.saving = true;
      this.$post('integrations/settings/payment-methods', {
        settings: this.settings,
        method: this.route_name
      })
          .then(response => {
            this.saving = false;
            this.$notify.success('Settings updated!');
          })
    },
    verifyKeys(req, method) {
      this.verifying = true;
    },
    getRoute() {
      this.route_name = this.$route.params.settings_key ? this.$route.params.settings_key : 'stripe';
    },
  },
  mounted() {
    this.getRoute();
    this.getSettings();
    if (window.outerWidth < 500) {
      this.labelPosition = "top";
    }
  }
}

</script>
