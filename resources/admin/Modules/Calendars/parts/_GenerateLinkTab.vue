<template>
    <div class="fcal_create_calendar_body">
        <p>{{ $t('Generate booking link with URL parameters') }}</p>
        <el-form v-if="!loading" label-position="top">
            <div class="fcal_select_page">
                <el-form-item :label="$t('Page Type')">
                    <el-radio-group v-model="pageType">
                        <el-radio label="landing_page" :disabled="!slot.public_url">
                            {{ $t('Landing Page') }}
                        </el-radio>
                        <el-radio label="other_page">
                            {{ $t('Other Page') }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item
                    v-if="pageType == 'other_page'"
                    :label="$t('Select Page')">
                    <el-select
                        v-model="pageTitle"
                        :placeholder="$t('Select Page')"
                        filterable
                        popper-class="fcal_select"
                        placement="bottom">
                        <el-option v-for="page in pages" :key="page.id" :label="page.title" :value="page.name">
                            <span style="float: left">{{ page.title }}</span>
                            <span style="float: right">#{{ page.id }}</span>
                        </el-option>
                    </el-select>
                </el-form-item>
            </div>
            <el-form-item :label="$t('Add Parameters')">
                <div class="fcal_param_fields">
                    <table class="fcal_table">
                        <thead>
                            <tr>
                                <th>{{ $t('Field') }}</th>
                                <th>{{ $t('Value') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(field, indx) in paramFields" :key="'field_' + indx">
                                <td>
                                    <el-select
                                        v-model="field.name"
                                        filterable
                                        popper-class="fcal_select"
                                        :placeholder="$t('Select')">
                                        <el-option
                                            v-for="option in fieldTypes"
                                            :disabled="alreadyExists(option.value)"
                                            :key="option.value"
                                            :label="option.label"
                                            :value="option.value">
                                        </el-option>
                                    </el-select>
                                </td>
                                <td>
                                    <template v-if="field.name == 'date'">
                                        <el-date-picker
                                            v-model="field.value"
                                            :placeholder="field.placeholder"
                                            type="date"
                                            size="small"
                                            :default-value="new Date()"
                                            value-format="YYYY-MM-DD"
                                        />
                                    </template>
                                    <template v-else-if="field.name == 'time'">
                                        <el-time-select
                                            v-model="field.value"
                                            start="00:00"
                                            :step="'00:05'"
                                            end="23:59"
                                            :placeholder="$t('Start Time')"
                                            popper-class="fcal_select"
                                        />
                                    </template>
                                    <template v-else-if="bookingField(field.name)?.type == 'checkbox'">
                                        <el-select v-model="field.value"
                                                   popper-class="fcal_select">
                                            <el-option :value="true" :label="$t('True')"></el-option>
                                            <el-option :value="false" :label="$t('False')"></el-option>
                                        </el-select>
                                    </template>
                                    <template v-else-if="['dropdown', 'radio', 'multi-select'].includes(bookingField(field.name)?.type)">
                                        <el-select
                                            v-model="field.value"
                                            :multiple="bookingField(field.name)?.type == 'multi-select'"
                                            :placeholder="field.placeholder"
                                            popper-class="fcal_select"
                                        >
                                            <el-option
                                                v-for="option in bookingField(field.name)?.options"
                                                :key="option"
                                                :label="option"
                                                :value="option">
                                            </el-option>
                                        </el-select>
                                    </template>
                                    <template v-else-if="field.name == 'booking_coupons'">
                                        <el-select
                                            v-model="field.value"
                                            multiple
                                            :placeholder="$t('Select Coupon')"
                                            popper-class="fcal_select"
                                        >
                                            <el-option v-for="coupon in couponCodes" :key="coupon" :label="coupon" :value="coupon"/>
                                        </el-select>
                                    </template>
                                    <template v-else>
                                        <el-input
                                            v-model="field.value"
                                            :type="getFieldType(field.name)"
                                            :placeholder="field.placeholder">
                                        </el-input>
                                    </template>
                                </td>
                                <td class="fcal_table_action">
                                    <p @click="addField"> + </p>
                                    <p v-if="paramFields.length > 1" @click="removeField(indx)"> - </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </el-form-item>
            <el-form-item :label="$t('Generated Link')">
                <el-input
                    v-model="generatedLink">
                    <template #append>
                        <a target="_blank" :href="generatedLink">
                            <el-button type="default">
                                <el-icon><Link /></el-icon>
                            </el-button>
                        </a>
                        <el-button type="default" @click="copyGeneratedLink">
                            <el-icon><CopyDocument /></el-icon>
                        </el-button>
                    </template>
                </el-input>
            </el-form-item>
        </el-form>
        <el-skeleton v-else :row="3" animated/>
    </div>
</template>

<script>
import { Link } from '@element-plus/icons-vue';
import { copyToClipBoard } from '@/Bits/data_config.js';
export default {
    name: 'GenerateLinkTab',
    props: ['slot'],
    components: {
        Link
    },
    data() {
        return {
            loading: false,
            pageTitle: '',
            pages: [],
            pageTitle: '',
            generatedLink: '',
            bookingFields: [],
            fieldTypes: [],
            paramFields: [{ name: '', value: '' }],
            pageType: this.slot.public_url ? 'landing_page' : 'other_page',
            isCouponEnabled: this.appVars.pref_settings?.coupon?.enabled == 'yes' && this.slot?.type == 'paid',
            couponCodes: []
        }
    },
    watch: {
        pageType() {
            this.updateLink();
        },
        pageTitle() {
            this.updateLink();
        },
        paramFields: {
            deep: true,
            handler() {
                this.updateLink();
            }
        }
    },
    computed: {
        alreadyExists () {
            return (value) => {
                return this.paramFields.some(field => field.name == value);
            }
        },
        bookingField() {
            return (name) => {
                return this.bookingFields.find(field => field.name == name);
            }
        },
        getFieldType() {
            return (name) => {
                const fieldType = this.bookingField(name)?.type;
                return fieldType == 'hidden' ? 'text' : fieldType;
            }
        }
    },
    methods: {
        addField() {
            this.paramFields.push({ name: '', value: '' });
            this.updateBookingFields();
        },
        removeField(indx) {
            this.paramFields.splice(indx, 1);
            this.updateBookingFields();
        },
        updateLink() {
            let link = this.slot.public_url;
            if (this.pageType == 'other_page') {
                link = this.appVars.site_url + this.pageTitle;
            }
            const url = new URL(link, window.location.origin);
            this.paramFields.forEach(field => {
                if (field.name && field.value) {
                    const fixedFields = ['date', 'time', 'booking_coupons', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
                    const param = field.name.replace(/custom_/i, '').replace(/-/g, '_');
                    const prefix = fixedFields.includes(field.name) ? '' : 'invitee_';
                    url.searchParams.set(prefix + param, field.value);
                }
            });
            this.generatedLink = url.toString();
        },
        copyGeneratedLink() {
            copyToClipBoard(this.generatedLink);
            this.$handleSuccess(this.$t('Copied to clipboard'));
        },
        updateBookingFields() {
            const allowTypes = ['text', 'email', 'phone', 'number', 'hidden', 'dropdown', 'radio', 'checkbox', 'multi-select'];
            this.fieldTypes = [
                ...this.bookingFields
                    .filter(field => {
                        return field.enabled  && field.name !== 'location' && (allowTypes.includes(field.type) || field.name == 'message');
                    })
                    .map(field => {
                        return {
                            label: field.label,
                            value: field.name
                        }
                    }),
                ...this.getOtherFields()
            ];
        },
        getOtherFields() {
            const fields = [
                { label: 'Date', value: 'date' },
                { label: 'Time', value: 'time' },
                { label: 'UTM Source', value: 'utm_source' },
                { label: 'UTM Medium', value: 'utm_medium' },
                { label: 'UTM Campaign', value: 'utm_campaign' },
                { label: 'UTM Term', value: 'utm_term' },
                { label: 'UTM Content', value: 'utm_content' }
            ];
            if (this.isCouponEnabled) {
                fields.push({ label: 'Coupon', value: 'booking_coupons' });
            }
            return fields;
        },
        getPages() {
            this.loading = true;
            this.$get('settings/pages')
                .then(response => {
                    this.pages = response.pages;
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        fetchFields() {
            this.loading = true;
            this.$get('calendars/' + this.slot.calendar_id + '/events/' + this.slot.id + '/booking-fields', {
                calendar_id: this.slot.calendar_id
            })
                .then(response => {
                    this.bookingFields = response.fields;
                    this.updateBookingFields();
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        getCoupons() {
            this.loading = true;
            this.$get('coupons/event/' + this.slot.id)
                .then(response => {
                    this.couponCodes = response.coupon_codes;
                    this.updateBookingFields();
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    mounted() {
        this.updateLink();
        this.getPages();
        this.fetchFields();
        if (this.isCouponEnabled) {
            this.getCoupons();
        }
    }
}
</script>