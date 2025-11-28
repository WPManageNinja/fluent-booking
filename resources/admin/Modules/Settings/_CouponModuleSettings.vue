<template>
    <div class="fcal_feature_module">
        <div class="fcal_module_desc">
            <div>
                <h4>
                    <span v-if="appVars.has_pro">{{ $t('Coupon Module') }}</span>
                    <span v-else>{{ $t('Coupon Module (Pro Required)') }}</span>
                    <span v-if="appVars.has_pro && featureModules.coupon?.enabled == 'yes'" class="fcal_addon_installed">{{ $t('Enabled') }}</span>
                    <span v-else class="fcal_addon_installed fcal_addon_disabled">{{ $t('Disabled') }}</span>
                </h4>
                <p>{{ $t('Create and manage coupons for your bookings') }}
                    <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/how-to-create-and-manage-payment-coupons/">
                        {{ $t('Learn') }} {{ $t('more') }}
                    </a> {{ $t('about this feature') }}.
                </p>
            </div>
        </div>
        <div v-if="appVars.has_pro" class="fcal_module_actions">
            <el-popover
                :persistent="false"
                placement="bottom"
                :title="$t('Coupon Module')"
                :width="400"
                trigger="click">
                <div style="margin-bottom: 10px;">
                    <el-checkbox
                        v-model="featureModules.coupon.enabled"
                        true-label="yes"
                        false-label="no"
                        :label="$t('Enable Coupon Module')"
                        size="large"
                        class="fcal_checkbox"
                    />
                </div>
                <el-button class="fcal_primary_btn2" @click="saveSettings">{{ $t('Save') }}</el-button>
                <template #reference>
                    <el-button class="fcal_plain_btn">{{ $t('Settings') }}</el-button>
                </template>
            </el-popover>
        </div>
    </div>

</template>
<script>
export default {
    name: 'CouponModuleSettings',
    props: ['featureModules'],
    emits: ['save'],
    methods: {
        saveSettings() {
            this.$emit('save');
        }
    }
}
</script>