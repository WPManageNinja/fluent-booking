<template>
    <div class="fcal_settings_content_wrap">
        <div class="fcal_settings_section">
            <el-breadcrumb :separator-icon="ArrowRight" class="fcal_settings_section_body">
                <el-breadcrumb-item :to="{ name: 'payment_coupons' }">
                    {{ $t("Coupons") }}
                </el-breadcrumb-item>
                <el-breadcrumb-item>
                    {{ this.coupon?.id ? $t("Edit Coupon") : $t("Add Coupon") }}
                </el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-form label-position="top" require-asterisk-position="right">
            <div class="fcal_settings_section">
                <div class="fcal_settings_section_header">
                    {{ $t('Basic Information') }}
                </div>
                <div v-if="!loading" class="fcal_settings_section_body">
                    <el-form-item :label="$t('Title')" required>
                        <el-input v-model="coupon.title" :placeholder="$t('Enter Title')" />
                    </el-form-item>
    
                    <el-row :gutter="20">
                        <el-col :sm="12">
                            <el-form-item required>
                                <div class="fcal_inline_label">
                                    <div class="label_text required">{{ $t('Coupon Code') }}</div>
                                    <span @click="generateRandomCode()">
                                        {{ $t('Generate Random Code') }}
                                    </span>
                                </div>
                                <el-input
                                    v-model="coupon.coupon_code"
                                    class="fcal_input_with_copy"
                                    :placeholder="$t('Enter Coupon Code')">
                                    <template #append>
                                        <el-icon @click="copyContent(coupon.coupon_code)"><CopyDocument /></el-icon>
                                    </template>
                                </el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :sm="12">
                            <el-form-item :label="getDiscountLabel" required>
                                <el-input
                                    v-model="coupon.discount"
                                    type="number"
                                    :min="1"
                                    :max="999999"
                                    class="fcal_input_with_select"
                                    @input="(value) => validateDiscount(value)"
                                    placeholder="0.00">
                                    <template #append>
                                        <el-select v-model="coupon.discount_type">
                                            <el-option :label="$t('Percentage (%)')" value="percentage"/>
                                            <el-option :label="$t('Fixed')" value="fixed"/>
                                        </el-select>
                                    </template>
                                </el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </div>
                <el-skeleton v-else class="fcal_settings_section_body" :rows="2" animated/>
            </div>
            <div class="fcal_settings_section">
                <div class="fcal_settings_section_header">
                    {{ $t('Discount Conditions') }}
                </div>
                <div v-if="!loading" class="fcal_settings_section_body">
                    <el-row :gutter="20" style="margin-bottom: 16px;">
                        <el-col :sm="12">
                            <with-label :field="{
                                label: $t('Minimum Purchase Amount'),
                                label_class: 'fcal_form_label',
                                help: $t('Minimum purchase amount should be greater than the discount amount')
                            }">
                                <el-input
                                    type="number"
                                    v-model="coupon.min_purchase_amount"
                                    :min="0" :max="999999"
                                    placeholder="0"
                                    @input="(value) => validateInput('min_purchase_amount', value)"/>
                            </with-label>
                        </el-col>
                        <el-col :sm="12">
                            <with-label :field="{
                                label: $t('Maximum Discount Amount'),
                                label_class: 'fcal_form_label',
                                help: $t('CouponModule/maximum_discount_amount_help')
                            }">
                                <el-input
                                    type="number"
                                    v-model="coupon.max_discount_amount"
                                    :min="0" :max="999999"
                                    placeholder="0"
                                    @input="(value) => validateInput('max_discount_amount', value)"/>
                            </with-label>
                        </el-col>
                    </el-row>
                    <el-row :gutter="20" style="margin-bottom: 16px;">
                        <el-col :sm="12">
                            <with-label :field="{
                                label: $t('Total Limit'),
                                label_class: 'fcal_form_label',
                                help: $t('CouponModule/total_limit_help')
                            }">
                                <el-input
                                    type="number"
                                    v-model="coupon.total_limit"
                                    :min="0" :max="999999"
                                    placeholder="0"
                                    @input="(value) => validateInput('total_limit', value)"/>
                            </with-label>
                        </el-col>
                        <el-col :sm="12">
                            <with-label :field="{
                                label: $t('Per User Limit'),
                                label_class: 'fcal_form_label',
                                help: $t('CouponModule/per_user_limit_help')
                            }">
                                <el-input
                                    type="number"
                                    v-model="coupon.per_user_limit"
                                    :min="0" :max="999999"
                                    placeholder="0"
                                    @input="(value) => validateInput('per_user_limit', value)"/>
                            </with-label>
                        </el-col>
                    </el-row>
                    <with-label :field="{
                        label: $t('Applicable Events'),
                        label_class: 'fcal_form_label',
                        help: $t('Select the events that this coupon can be applied to. Empty for all events')
                    }">
                        <el-select
                            v-model="coupon.allowed_event_ids"
                            filterable
                            clearable
                            multiple
                            popper-class="fcal_select"
                            :no-match-text="$t('No Data match')"
                            :no-data-text="$t('No Data')"
                            :placeholder="$t('Select Event')">
                            <el-option-group
                                v-for="calendar in eventLists"
                                    :key="calendar.title"
                                    :label="calendar.title">
                                    <el-option
                                        v-for="event in calendar.options"
                                        :key="event.id"
                                        :label="event.title"
                                        :value="event.id">
                                    </el-option>
                            </el-option-group>
                        </el-select>
                    </with-label>
                </div>
                <el-skeleton v-else class="fcal_settings_section_body" :rows="3" animated/>
            </div>
            <div class="fcal_settings_section">
                <div class="fcal_settings_section_header">
                    {{ $t('Expiration') }}
                </div>
                <div v-if="!loading" class="fcal_settings_section_body">
                    <el-row :gutter="20">
                        <el-col :sm="12">
                            <el-form-item :label="$t('Start Date & Time')">
                                <el-date-picker
                                    v-model="coupon.start_date"
                                    type="datetime"
                                    format="YYYY-MM-DD HH:mm"
                                    value-format="YYYY-MM-DD HH:mm"
                                    :placeholder="$t('Start Date & Time')"
                                    :disabled-date="disabledStartDate">
                                </el-date-picker>
                            </el-form-item>
                        </el-col>
                        <el-col :sm="12">
                            <el-form-item :label="$t('End Date & Time')">
                                <el-date-picker
                                    v-model="coupon.end_date"
                                    type="datetime"
                                    format="YYYY-MM-DD HH:mm"
                                    value-format="YYYY-MM-DD HH:mm"
                                    :placeholder="$t('End Date & Time')"
                                    :disabled-date="disabledEndDate">
                                </el-date-picker>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </div>
                <el-skeleton v-else class="fcal_settings_section_body" :rows="1" animated/>
            </div>
            <div class="fcal_settings_section">
                <div class="fcal_settings_section_header">
                    {{ $t('Additional Information') }}
                </div>
                <div v-if="!loading" class="fcal_settings_section_body">
                    <el-form-item :label="$t('Status')">
                        <el-select v-model="coupon.status" popper-class="fcal_select">
                            <el-option :label="$t('Active')" value="active" :disabled="isActiveDisabled"/>
                            <el-option :label="$t('Inactive')" value="inactive"/>
                            <el-option v-if="isStatus('scheduled')" :label="$t('Scheduled')" value="scheduled"/>
                            <el-option v-if="isStatus('expired')" :label="$t('Expired')" value="expired"/>
                        </el-select>
                    </el-form-item>
                    <el-form-item :label="$t('Internal Notes')">
                        <el-input type="textarea" rows="3" v-model="coupon.internal_note" :placeholder="$t('Enter Notes')"/>
                    </el-form-item>
                    <el-form-item :label="$t('Failed Message')">
                        <el-radio-group v-model="failedMessageType">
                            <el-radio label="invalid">{{ $t('Invalid') }}</el-radio>
                            <el-radio label="expired">{{ $t('Expired') }}</el-radio>
                            <el-radio label="stackable">{{ $t('Stackable') }}</el-radio>
                            <el-radio label="min_purchase">{{ $t('Minimum Purchase') }}</el-radio>
                            <el-radio label="limit_reached">{{ $t('Limit Reached') }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item>
                        <el-input v-model="coupon.failed_message[failedMessageType]" :placeholder="$t('Enter Failed Message')"/>
                    </el-form-item>
                    <with-label :field="{
                            label: $t('Stackable'),
                            label_class: 'fcal_form_label',
                            help: $t('Can this coupon code be used with other coupon code')
                        }">
                        <el-radio-group v-model="coupon.stackable">
                            <el-radio label="yes">{{ $t('Yes') }}</el-radio>
                            <el-radio label="no">{{ $t('No') }}</el-radio>
                        </el-radio-group>
                    </with-label>
                    <el-button class="fcal_primary_btn" @click="saveCoupon" :loading="saving || loading">
                        {{ $t('Save Coupon') }}
                    </el-button>
                </div>
                <el-skeleton v-else class="fcal_settings_section_body" :rows="4" animated/>
            </div>
        </el-form>
    </div>
</template>

<script>
import { ArrowRight } from '@element-plus/icons-vue';
import { markRaw } from 'vue';
import WithLabel from '@/Components/FormBuilder/WithLabel.vue';
import { copyToClipBoard } from '@/Bits/data_config';

export default {
    components: { WithLabel },
    name: 'AddOrEditCoupon',
    props: ['coupon', 'loading', 'eventLists'],
    emits: ['updated'],
    data() {
        return {
            saving: false,
            failedMessageType: 'invalid',
            ArrowRight: markRaw(ArrowRight)
        }
    },
    watch: {
        'coupon.start_date': {
            handler() {
                this.maybeUpdateStatus();
            },
            immediate: true
        },
        'coupon.end_date': {
            handler() {
                this.maybeUpdateStatus();
            },
            immediate: true
        }
    },
    computed: {
        getDiscountLabel() {
            return this.$t('Discount') + (this.coupon.discount_type === 'percentage' ? ' (%)' : '');
        },
        isActiveDisabled() {
            return ['scheduled', 'expired'].includes(this.coupon.status);
        }
    },
    methods: {
        isStatus(status) {
            return this.coupon.status === status;
        },
        copyContent(content) {
            copyToClipBoard(content);
            this.$handleSuccess(this.$t('Copied to clipboard'));
        },
        maybeUpdateStatus() {
            const currentDate = new Date();
            const startDate = this.coupon.start_date
                ? new Date(this.coupon.start_date)
                : null;
            const endDate = this.coupon.end_date
                ? new Date(this.coupon.end_date)
                : null;

            if (this.coupon.status === "inactive") {
                return;
            }

            if (!startDate && !endDate) {
                this.coupon.status = "active";
            } else if (startDate && currentDate < startDate) {
                this.coupon.status = "scheduled";
            } else if (endDate && currentDate > endDate) {
                this.coupon.status = "expired";
            } else {
                this.coupon.status = "active";
            }
        },
        disabledStartDate(date) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return date && date.getTime() < today.getTime();
        },
        disabledEndDate(date) {
            const today = this.coupon.start_date
                ? new Date(this.coupon.start_date)
                : new Date();
            today.setHours(0, 0, 0, 0);
            return date && date.getTime() < today.getTime();
        },
        generateRandomCode() {
            const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            let couponCode = "";
            for (let i = 0; i < 8; i++) {
                couponCode += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            this.coupon.coupon_code = couponCode;
        },
        saveCoupon() {
            if (this.coupon.id) {
                this.updateCoupon();
            } else {
                this.addNewCoupon();
            }
        },
        validateCoupon() {
            if (!this.coupon.coupon_code) {
                this.$handleError(this.$t('Coupon Code is required'));
                return false;
            }
            if (!this.coupon.title) {
                this.$handleError(this.$t('Title is required'));
                return false;
            }
            if (!this.coupon.discount || this.coupon.discount == 0) {
                this.$handleError(this.$t('Discount is required'));
                return false;
            }
            return true;
        },
        addNewCoupon() {
            if (!this.validateCoupon()) {
                return;
            }

            this.saving = true;
            this.$post('coupons', {
                coupon: this.coupon
            })
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.$emit('updated');
                    this.$router.push({ name: 'payment_coupons' });
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        updateCoupon() {
            if (!this.validateCoupon()) {
                return;
            }

            this.saving = true;
            this.$put('coupons/' + this.coupon.id, {
                coupon: this.coupon
            })
                .then(response => {
                    this.$handleSuccess(response.message);
                    this.$emit('updated');
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        validateDiscount(value) {
            if (isNaN(value) || value === "" || value < 0) {
                this.coupon.discount = "";
                return;
            }
            if (this.coupon.discount_type === 'percentage') {
                value = Math.min(value, 100);
            } else {
                value = Math.min(999999, value);
            }
            this.coupon.discount = value;
        },
        validateInput(name, value) {
            value = parseInt(value);
            if (isNaN(value) || value === "" || value < 0) {
                this.coupon[name] = "";
                return;
            }
            this.coupon[name] = Math.min(999999, value);
        }
    }
}
</script>
