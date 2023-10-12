<template>
  <div class="fcal_webhook_settings fcal_payment_settings">
    <div class="fcal_create_calendar_form">
      <div class="fcal_create_calendar_form_header">
        <h2><el-icon><Money/></el-icon> Payment Settings</h2>
        <el-button
            @click="update()"
            class="fcal_primary_btn2"
        >
          Update Settings
        </el-button>
      </div>
    </div>
    <div class="fcal_settings_body" style="min-height: calc(100vh - 320px);">
      <el-form :model="paymentSettings" label-position="left">
        <el-form-item class="fcal_payment_flex_row">
          <span class="header_left">Enable booking payment</span>
          <el-switch
              class="header_right"
              active-value="yes"
              active-color="#2653C7"
              @click="handleActive()"
              v-model="paymentSettings.enabled"
          ></el-switch>
        </el-form-item>
        <template v-if="paymentSettings.enabled === 'yes'">
          <el-form-item class="fcal_payment_flex_row">
            <span class="header_left">Booking Payments</span>
            <div>
              <el-row style="margin-bottom: 12px;" :gutter="20" v-for="(item, index) in paymentSettings.items">
                <el-col :span="16">
                  <el-input v-model="item.title"></el-input>
                </el-col>
                <el-col :span="6">
                  <el-input min="0" type="number" v-model="item.value"></el-input>
                </el-col>
                <el-col :span="2">
              <span v-if="index > 0"
                    @click="()=>{
                paymentSettings.items.splice(index, 1);
              }" style="cursor: pointer; font-weight: bold;">
                   <el-icon><Delete/></el-icon>
              </span>
                </el-col>
              </el-row>
              <el-link @click="addItem" style="cursor: pointer;">
                  Add more item <el-icon> <Plus/></el-icon>
              </el-link>
            </div>
          </el-form-item>
          <el-form-item class="fcal_payment_flex_row">
            <span class="header_left">Currency</span>
            <div class="header_right">
              <el-select
                  filterable
                  v-model="paymentSettings.currency"
                  placeholder="Select"
                  popper-class="fcal_select"
              >
                <el-option
                    v-for="item in currencies"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                />
              </el-select>
            </div>
          </el-form-item>
        </template>

       </el-form>
    </div>
</div>
</template>
<script>
import {Back, Link, Plus, Money, Delete} from "@element-plus/icons-vue";
import Popover from "../../../../Components/Popover.vue";

export default {
  name: "PaymentSettings.vue",
  components: {Popover, Link, Plus, Back, Money, Delete},
  data() {
    return {
      props: ['event_id', 'calendar_id'],
      paymentSettings: {
        enabled: 'yes',
        items: [
          {
            title: 'Booking Fee',
            value: '10',
          },
        ],
        currency: 'USD'
      },
      currencies: [],
      calendarId: '',
      eventId: '',
    };
  },
  methods : {
    getCurrencies(){
      this.$get('integrations/settings/payment-methods/currencies')
          .then((response) => {
        this.currencies = response.data;
      }).then(() => {
        this.loading = false;
      }).catch((error) => {
        console.log(error);
      });
    },
    getSettings() {
      this.$get(`calendars/${this.calendarId}/slots/${this.eventId}/payment-settings`, {})
          .then((response) => {
            if (response.data) {
              this.paymentSettings = response.data;
            }
          }).then(() => {
        this.loading = false;
      }).catch((error) => {
        console.log(error);
      });
    },
    update(){
      this.$post(`calendars/${this.calendarId}/slots/${this.eventId}/payment-settings`,
          {
            settings: this.paymentSettings,
          })
          .then((response) => {
            this.$handleSuccess(response.message);
        }).catch((error) => {
          console.log(error);
        });
    },
    addItem() {
      this.paymentSettings.items.push({
        title: 'Payment Item',
        value: 10,
      });
    },
  },
  mounted() {
    this.calendarId = this.calendar_id ?? this.$route.params.calendar_id;
    this.eventId = this.event_id ?? this.$route.params.event_id;
    this.getCurrencies();
    this.getSettings();
  },
}
</script>

<style lang="scss">
.fcal_payment_flex_row .el-form-item__content {
  display: flex;
  justify-content: space-between !important;
  .header_left {
    font-size: 16px;
    font-weight: 500;
    line-height: 24px;
    color: #1B2533;
  }
}


</style>