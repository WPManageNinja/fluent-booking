<script setup>
import { getCurrentInstance, defineProps, onMounted, ref, defineEmits, nextTick } from "vue";
import ConnectAccount from "../Parts/_connect_account.vue";
import Tabs from './Tabs.vue';
import { copyToClipBoard } from '@/Bits/data_config.js';

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

const copyText = (text) => {
    copyToClipBoard(text);
}

onMounted(() => {
    nextTick(() => {
        getConnectConfig();
    })
})

</script>

<template>
    <div class="fcal_settings_content_wrap">
        <el-form label-position="top">
            <div class="fc-payment-row" v-for="(field, index) in fields" :key="index">
                <div class="fc-payment-col" v-if="field.type === 'enable'">
                    <el-switch
                        v-model="settings[index]"
                        active-value="yes"
                        inactive-value="no"
                        :active-text="$t('Active')"
                        :inactive-text="$t('Inactive')"
                    />
                    <p class="fc-payment-label" v-if="field.tooltip">
                        <el-tooltip placement="top-start">
                            <template #content>
                                <p v-html="field.tooltip"></p>
                            </template>
                            <el-icon>
                                <InfoFilled/>
                            </el-icon>
                        </el-tooltip>
                    </p>
                </div><!-- .fc-payment-col -->
    
                <div class="fc-payment-col" v-else-if="field.type === 'inline_checkbox'">
                    <p class="fc-payment-label">
                        <el-checkbox true-label="yes" false-label="no" v-model="settings[index]">
                            {{ field.label }}
                        </el-checkbox>
                    </p>
                </div><!-- .fc-payment-col -->

                <div class="fc-payment-col" v-else-if="field.type === 'inline_switch'">
                    <p class="fc-payment-label">
                        <el-form-item>
                            <el-switch :inactive-text="field.label" active-value="yes" inactive-value="no" v-model="settings[index]"/>
                            <p v-if="field.inline_help" v-html="field.inline_help"></p>
                        </el-form-item>
                    </p>
                </div><!-- .fc-payment-col -->
    
                <div class="fc-payment-col" v-if="field.type === 'radio'">
                    <div class="flex items-center">
                        <el-form-item :label="field.label">
                            <el-radio-group v-model="settings[index]">
                                <el-radio v-for="(opt, ind) in field.options" :label="ind" :key="ind">{{ opt }}
                                </el-radio>
                            </el-radio-group>
                        </el-form-item>
                    </div>
                </div><!-- .fc-payment-col -->
    
                <div class="fc-payment-col" v-else-if="field.type === 'provider'">
                    <template v-if="field.value == 'connect'">
                        <template v-if="settings.payment_mode == 'test'">
                            <span v-if="fetching_connect">{{ $t('Connecting') }}...</span>
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
    
                <div class="fc-payment-col" v-else-if="field.type === 'input' || field.type === 'password'">
                    <el-form-item :label="field.label">
                        <el-input :type="field.type" :placeholder="field.placeholder" v-model="settings[index]"></el-input>
                    </el-form-item>
                </div><!-- .fc-payment-col -->
    
                <div class="fc-payment-col" v-else-if="field.type === 'email'">
                    <el-form-item :label="field.label">
                        <el-input type="email" :placeholder="field.placeholder" v-model="settings[index]"></el-input>
                    </el-form-item>
    
                </div><!-- .fc-payment-col -->
    
                <div class="fc-payment-col" v-else-if="field.type === 'text'">
                    <el-form-item :label="field.label">
                        <el-input
                            type="text"
                            :placeholder="field.placeholder"
                            :disabled="field.readonly"
                            v-model="settings[index]">
                            <template v-if="field.copy_btn" #append>
                                <el-button type="default" @click="copyText(settings[index])">
                                    <el-icon><CopyDocument /></el-icon> {{ $t('Copy') }}
                                </el-button>
                            </template>
                        </el-input>
                        <p v-if="field.inline_help" v-html="field.inline_help"></p>
                    </el-form-item>
                </div><!-- .fc-payment-col -->
    
                <div class="fc-payment-col" v-else-if="field.type === 'checkbox_group'">
                    <p class="fc-payment-label">
                        {{ field.title }}
                        <el-tooltip v-if="field.tooltip" placement="top-start">
                            <template #content>
                                <p>{{ field.tooltip }}</p>
                            </template>
                            <el-icon>
                                <InfoFilled/>
                            </el-icon>
                        </el-tooltip>
                    </p>
                    <p>{{ field.desc }}</p>
                    <el-checkbox-group v-model="settings[index]">
                        <el-checkbox v-for="(opt, ind) in field.options" :label="ind" :key="ind">{{ opt }}</el-checkbox>
                    </el-checkbox-group>
                </div><!-- .fc-payment-col -->
    
              <div class="fc-payment-col" v-else-if="field.type === 'select'">
                <div class="flex items-center">
                  <el-form-item :label="field.label">
                    <el-select filterable style="max-width:400px;" v-model="settings[index]" class="m-2" popper-class="fcal_select" :placeholder="$t('Select')" size="large">
                      <el-option
                          v-for="item in field.options"
                          :key="item.value"
                          :label="item.label"
                          :value="item.value"
                      />
                    </el-select>
                  </el-form-item>
                </div>
              </div><!-- .fc-payment-col -->
    
                <div class="fc-payment-col" v-else-if="field.type === 'verify_button'">
                    <p v-if="verifiedStatus" style="color:green;">{{ $t('Authenticated') }}: {{ verifiedMessage }}</p>
                    <el-button style="margin: 0;" v-loading="verifying" element-loading-text="{{ $t('verifying') }}..."
                               element-loading-spinner="el-icon-loading"
                               @click="verifyKeys(field.req_type, field.method)"
                               class="fct_new_ui_button">
                        {{ field.label }}
                    </el-button>
                </div><!-- .fc-payment-col -->
    
                <div class="fc-payment-col" v-else-if="field.type === 'html_attr'">
                    <div v-html="field.value"></div>
                </div><!-- .fc-payment-col -->
    
                <div class="fc-payment-col" v-else-if="field.type === 'tabs'">
                    <Tabs :fields="fields" :settings="settings" :index="index" :tabs="fields[index]"/>
                </div><!-- .fc-payment-col -->
            </div>
        </el-form>
    </div>
</template>
