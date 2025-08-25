<template>
    <div v-if="!isAddOrEditCoupon" class="fcal_settings_body_inner fcal_settings_general">
        <div class="fcal_configure_integration_card">
            <div class="fcal_configure_integration_card_header">
                <div class="left">
                    <div class="content">
                        <h3>{{ $t('Coupons') }}</h3>
                        <p>{{ $t('Manage your payment coupons and create discount codes for your customers here.') }}</p>
                    </div>
                </div>
            </div>
            <template v-if="!disabled">
                <el-skeleton animated v-if="loading"></el-skeleton>
                <div v-else-if="isCouponEnabled" class="fcal_configure_integration_body">
                    <div class="fcal_settings_body_subheader">
                        <div class="fcal_subheader_left">
                            <h4>{{ $t('Available Coupons') }}</h4>
                        </div>
                        <div class="fcal_subheader_right">
                            <el-button :disabled="saving" v-loading="saving" @click="redirectToAddCoupon" class="fcal_primary_btn2">
                                <span>+</span> {{ $t('Add Coupon') }}
                            </el-button>
                        </div>
                    </div>
                    <div class="fcal_settings_table">
                        <el-table class="fcal_table" stripe :data="coupons" v-loading="loading">
                            <el-table-column :label="$t('Title')">
                                <template #default="scope">
                                    <span @click="editCoupon(scope.row)" style="cursor: pointer;">
                                        {{ scope.row.title }}
                                    </span>
                                </template>
                            </el-table-column>
                            <el-table-column :label="$t('Coupon Code')" width="180">
                                <template #default="scope">
                                    <span
                                        class="fcal_text_with_copy"
                                        @click="copyContent(scope.row.coupon_code)">
                                        {{ scope.row.coupon_code }} <el-icon><CopyDocument /></el-icon>
                                    </span>
                                </template>
                            </el-table-column>
                            <el-table-column :label="$t('Discount')" width="120">
                                <template #default="scope">
                                    <span v-if="scope.row.discount_type === 'percentage'">
                                        {{ scope.row.discount + '%' }}
                                    </span>
                                    <span v-else>
                                        {{ $currencyFormat(scope.row.discount) }}
                                    </span>
                                </template>
                            </el-table-column>
                            <el-table-column :label="$t('Usage')" width="140">
                                <template #default="scope">
                                    {{ scope.row.usage_count }} / {{ getTotalLimit(scope.row.total_limit) }}
                                </template>
                            </el-table-column>
                            <el-table-column :label="$t('Status')" width="140">
                                <template #default="scope">
                                    <div class="fcal_badge" :class="scope.row.status">
                                        {{ scope.row.status }}
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column :label="$t('Actions')" width="120" fixed="right">
                                <template #default="scope">
                                    <div class="fcal_table_action">
                                        <el-button class="fcal_action edit_btn" @click="editCoupon(scope.row)">
                                            <el-icon><EditPen /></el-icon>
                                        </el-button>
                                        <el-button class="fcal_action danger_btn" @click="deleteCoupon(scope.row)">
                                            <el-icon><Delete /></el-icon>
                                        </el-button>
                                    </div>
                                </template>
                            </el-table-column>
                        </el-table>
                        <div class="fcal_right fcal_tm20">
                            <pagination popper-class="fcal_select" :pagination="pagination" @fetch="getCoupons"/>
                        </div>
                    </div>
                </div>
                <div v-else class="fcal_module_enable_promo_card">
                    <h3 class="promo_card_title">
                        {{ $t('Coupon Module') }}
                    </h3>
                    <p class="promo_card_description">
                        {{ $t('CouponModule/disabled_description') }}
                    </p>
                    <el-button class="promo_card_btn fcal_primary_btn" @click="enableCouponModule">
                        <el-icon><CircleCheckFilled /></el-icon> {{ $t('Enable Coupon Module') }}
                    </el-button>
                </div>
            </template>
            <ProNotice v-else-if="disabled"/>
        </div>
    </div>
    <router-view v-else-if="coupon" :coupon="coupon" :loading="loading" :event-lists="eventLists" @updated="updated"/>
</template>

<script>
import ProNotice from "@/Components/Common/ProNotice.vue";
import AddOrEditCoupon from "./_AddOrEditCoupon.vue";
import { CircleCheckFilled } from '@element-plus/icons-vue';
import Pagination from '@/Pieces/Pagination.vue';
import { copyToClipBoard } from '@/Bits/data_config';

export default {
    name: 'PaymentCouponsSettings',
    props: ['disabled'],
    components: {
        ProNotice,
        AddOrEditCoupon,
        CircleCheckFilled,
        Pagination
    },
    data() {
        return {
            saving: false,
            loading: false,
            pagination: {
                current_page: 1,
                total: 0,
                per_page: this.appVars.default_paginations?.coupons || 10
            },
            coupons: [],
            eventLists: [],
            coupon: null,
            isCouponEnabled: this.appVars.pref_settings?.coupon?.enabled == 'yes'
        }
    },
    computed: {
        isAddOrEditCoupon() {
            return ['add_payment_coupon', 'edit_payment_coupon'].includes(this.$route.name);
        }
    },
    methods: {
        getTotalLimit(limit) {
            if (!limit || limit == 0) {
                return this.$t('Unlimited');
            }
            return limit;
        },
        getCoupon(couponId) {
            this.loading = true;
            this.$get('coupons/' + couponId, {
                with: ['event_lists']
            })
                .then(response => {
                    this.coupon = response.coupon;
                    this.eventLists = response.event_lists;
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        getCoupons() {
            this.loading = true;
            this.$get('coupons', {
                with: ['event_lists'],
                page: this.pagination.current_page,
                per_page: this.pagination.per_page
            })
                .then(response => {
                    this.coupons = response.coupons;
                    this.pagination.total = response.total_coupons;
                    this.eventLists = response.event_lists;
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        editCoupon(coupon) {
            this.coupon = coupon;
            this.$router.push({
                name: 'edit_payment_coupon',
                params: { coupon_id: this.coupon.id }
            });
        },
        deleteCoupon(coupon) {
            this.$confirm(this.$t('Are you sure you want to delete this coupon?'), this.$t('Delete Coupon'), {
                confirmButtonText: this.$t('Delete'),
                cancelButtonText: this.$t('Cancel'),
            }).then(() => {
                this.$del('coupons/' + coupon.id)
                    .then(response => {
                        this.$handleSuccess(response.message);
                        this.getCoupons();
                    })
                    .catch(error => {
                        this.$handleError(error);
                    });
            });
        },
        enableCouponModule() {
            this.saving = true;
            this.$post('settings/addons-settings', {
                settings: {
                    coupon: { enabled: 'yes' }
                }
            })
                .then(response => {
                    this.isCouponEnabled = true;
                    this.$handleSuccess(this.$t('Coupon Module is enabled'));
                    location.reload(true);
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        redirectToAddCoupon() {
            this.coupon = this.appVars.default_coupon;
            this.$router.push({
                name: 'add_payment_coupon'
            });
        },
        updated() {
            this.getCoupons();
        },
        copyContent(content) {
            copyToClipBoard(content);
            this.$handleSuccess(this.$t('Copied to clipboard'));
        }
    },
    mounted() {
        if (!this.disabled && this.isCouponEnabled) {
            if (this.$route.params.coupon_id) {
                this.getCoupon(this.$route.params.coupon_id);
            } else {
                this.coupon = this.appVars.default_coupon;
            }
            this.getCoupons();
        }
    }
}
</script>