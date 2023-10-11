<script setup>
import {getCurrentInstance, defineProps, onMounted, ref, defineEmits, nextTick } from "vue";
import ConnectAccount from "../Parts/_connect_account.vue";
import Tabs from './Tabs.vue'
import {InfoFilled} from "@element-plus/icons-vue";

const selfRef = getCurrentInstance().ctx;
const connect_config = ref({});
const live_account = ref(null);
const test_account = ref(null);
const fetching_connect = ref(false);
const errors = ref({});

const props =
defineProps({
  fields: Object,
  settings: Object,
  route_name: String,
});

const emit = defineEmits(['onSettingsChange']);

const getConnectConfig = () => {
      fetching_connect.value = true;
      selfRef.$get('integrations/settings/payment-methods/connect/info', {
        method: props.route_name
      })
          .then(response => {
            if (response.data) {
              connect_config.value = response.data.connect_config;
              emit('onSettingsChange', response.data.settings);
              live_account.value = response.data.live_account;
              test_account.value = response.data.test_account;
              if (live_account.value.error) {
                errors.value = live_account.value;
              }
              if (test_account.value.error) {
                errors.value = test_account.value;
              }
            }
          }).catch(error => {
            errors.value = error;
          })
          .finally(() => {
            fetching_connect.value = false;
          })
    }

    onMounted(() => {
      nextTick(() => {
        getConnectConfig();
      })
    })

</script>

<template>

  <div>
    <div class="fc-payment-row" v-for="(field, index) in fields" :key="index">
      <div class="fc-payment-col" v-if="field.type === 'enable'">
        <el-switch
            v-model="settings[index]"
            active-value="yes"
            inactive-value="no"
            active-text="Active"
            inactive-text="Inactive"
        />
        <p class="fc-payment-label" v-if="field.tooltip">
          <el-tooltip placement="top-start">
            <template #content>
              <p v-html="field.tooltip"></p>
            </template>
            <el-icon><InfoFilled /></el-icon>
          </el-tooltip>
        </p>
      </div><!-- .fc-payment-col -->

      <div class="fc-payment-col" v-if="field.type === 'radio'">
        <div class="flex items-center">
          <p>
            {{ field.label }}
            <el-tooltip v-if="field.tooltip" placement="top-start">
              <template #content>
                <p v-html="field.tooltip">
                </p>
              </template>
              <el-icon><InfoFilled /></el-icon>
            </el-tooltip>
          </p>
          <el-radio-group v-model="settings[index]">
            <el-radio v-for="(opt, ind) in field.options" :label="ind" :key="ind">{{ opt }}
            </el-radio>
          </el-radio-group>
        </div>
      </div><!-- .fc-payment-col -->

      <div class="fc-payment-col" v-if="field.type === 'provider'">
        <template v-if="field.value == 'connect'">
          <template v-if="settings.payment_mode == 'test'">
            <span v-if="fetching_connect">Connecting...</span>
            <ConnectAccount 
                @reload_settings="getConnectConfig()"
                :method="route_name"
                :connect_config="connect_config"
                mode="test"
                :connect="test_account"
                />
          </template>

          <template v-else-if="settings.payment_mode == 'live'">
            <ConnectAccount
                :method="route_name"
                @reload_settings="getConnectConfig()"
                :connect_config="connect_config"
                mode="live"
                :connect="live_account"/>
          </template>
          <p class="text-red-700">{{ errors.error }}</p>
          <!-- <ErrorView field="connect" :errors="errors" /> -->
        </template>
      </div><!-- .fc-payment-col -->

      <div class="fc-payment-col" v-if="field.type === 'input' || field.type === 'password'">
        <p>{{ field.label }}</p>
        <el-input :type="field.type" :placeholder="field.placeholder" v-model="settings[index]"></el-input>
      </div><!-- .fc-payment-col -->

      <div class="fc-payment-col" v-if="field.type === 'email'">
        <p class="">{{ field.label }}</p>
        <el-input type="email" :placeholder="field.placeholder" v-model="settings[index]">
        </el-input>
      </div><!-- .fc-payment-col -->

      <div class="fc-payment-col" v-if="field.type === 'text'">
        <p class="fc-payment-label">{{ field.label }}</p>
        <el-input type="text" :placeholder="field.placeholder" v-model="settings[index]">
        </el-input>
      </div><!-- .fc-payment-col -->

      <div class="fc-payment-col" v-if="field.type === 'checkbox_group'">
        <p class="fc-payment-label">
          {{ field.title }}
          <el-tooltip v-if="field.tooltip" placement="top-start">
            <template #content>
              <p>
                {{ field.tooltip }}
              </p>
            </template>
            <el-icon><InfoFilled /></el-icon>
          </el-tooltip>
        </p>
        <p>{{ field.desc }}</p>
        <el-checkbox-group v-model="settings[index]">
          <el-checkbox v-for="(opt, ind) in field.options" :label="ind" :key="ind">{{ opt }}
          </el-checkbox>
        </el-checkbox-group>
      </div><!-- .fc-payment-col -->

      <div class="fc-payment-col" v-if="field.type === 'verify_button'">
        <p v-if="verifiedStatus" style="color:green;">Authenticated: {{ verifiedMessage }}</p>
        <el-button style="margin: 0;" v-loading="verifying" element-loading-text="verifying..."
                   element-loading-spinner="el-icon-loading"
                   @click="verifyKeys(field.req_type, field.method)"
                   class="fct_new_ui_button">
          {{ field.label }}
        </el-button>
      </div><!-- .fc-payment-col -->

      <div class="fc-payment-col" v-if="field.type === 'html_attr'">
        <div v-html="field.value"></div>
      </div><!-- .fc-payment-col -->

      <div class="fc-payment-col" v-if="field.type === 'tabs'">
        <Tabs :fields="fields" :settings="settings" :index="index" :tabs="fields[index]"/>
      </div><!-- .fc-payment-col -->

    </div>
  </div>
</template>
