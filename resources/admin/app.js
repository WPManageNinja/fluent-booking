import { createApp } from 'vue'
import { createRouter, createWebHashHistory } from 'vue-router';
import { routes } from './routes';
import DashboardApplication from "./Application.vue";
import Rest from './Bits/Rest.js';
import { ElNotification, ElLoading, ElMessageBox } from 'element-plus'
import Storage from '@/Bits/Storage';
import * as dayjs from 'dayjs';
import { Plus, Delete, Location, More, Operation, UserFilled, InfoFilled, Lock, Edit, EditPen, CopyDocument }  from "@element-plus/icons-vue";
import Errors from '@common/Errors';
import { applyFilters, addFilter } from '@wordpress/hooks';

global.Errors = Errors;

const utc = require('dayjs/plugin/utc')
const timezone = require('dayjs/plugin/timezone')
dayjs.extend(utc);
dayjs.extend(timezone);

window.dayjs = dayjs;

function convertToText(obj) {
    const string = [];
    if (typeof (obj) === 'object' && (obj.join === undefined)) {
        for (const prop in obj) {
            string.push(convertToText(obj[prop]));
        }
    } else if (typeof (obj) === 'object' && !(obj.join === undefined)) {
        for (const prop in obj) {
            string.push(convertToText(obj[prop]));
        }
    } else if (typeof (obj) === 'function') {

    } else if (typeof (obj) === 'string') {
        string.push(obj)
    }

    return string.join('<br />')
}

function getDateTimeStringI18(str, type) {
    if (!str) return str;

    const config = window.fluentFrameworkAdmin.i18.date_time_config;

    switch (type) {
        case 'day':
            return config.weekdays[str] || config.weekdaysShort[str] || str;
        case 'month':
            return config.months[str] || config.monthsShort[str] || str;
        case 'mNumber':
            const numbers = config.numericSystem.split('_');
            return str.toString().split('').map(s => numbers[s] || s).join('');
        default:
            return str;
    }
}

const app = createApp(DashboardApplication);

const Icons = [Plus, Delete, Location, More, Operation, UserFilled, InfoFilled, Lock, Edit, EditPen, CopyDocument];
Icons.forEach((icon) => {
    app.component(icon.name, icon);
});

app.config.globalProperties.appVars = window.fluentFrameworkAdmin;

app.mixin({
    data() {
        return {
            Storage,
            currentTimezone: dayjs.tz.guess()
        }
    },
    methods: {
        $get: Rest.get,
        $post: Rest.post,
        $put: Rest.put,
        $del: Rest.delete,
        dayjs: dayjs,
        formatNumber(amount, hideEmpty = false) {
            if (!amount && hideEmpty) {
                return '';
            }

            if (!amount) {
                amount = '0';
            }

            return new Intl.NumberFormat('en-US').format(amount)
        },
        $changeTitle(title) {
            jQuery('head title').text(title + ' - FluentBooking');
        },
        $handleError(response) {
            let errorMessage = '';
            if (typeof response === 'string') {
                errorMessage = response;
            } else if (response && response.message) {
                errorMessage = response.message;
            } else {
                errorMessage = convertToText(response);
            }
            if (!errorMessage) {
                errorMessage = this.$t('Something is wrong!');
            }

            this.$notify({
                type: 'error',
                title: this.$t('Error'),
                offset: 20,
                message: errorMessage,
                dangerouslyUseHTMLString: true
            });

            return errorMessage;
        },
        $handleSuccess(response) {
            let successMsg = this.$t('Success');
            if (typeof response === 'string') {
                successMsg = response;
            } else if (response && response.message) {
                successMsg = response.message;
            } else {
                successMsg = convertToText(response);
            }

            this.$notify({
                type: 'success',
                title: this.$t('Success'),
                offset: 20,
                message: successMsg,
                dangerouslyUseHTMLString: true
            });

            return successMsg;
        },
        toCurrentTimezone(date, format) {
            const i18nConfig = window.fluentFrameworkAdmin.i18.date_time_config;
            const convertedDate = dayjs(date).locale({
                name: 'fluent_date_time',
                weekdays: Object.values(i18nConfig.weekdays),
                weekdaysShort: Object.values(i18nConfig.weekdaysShort),
                months: Object.values(i18nConfig.months),
                monthsShort: Object.values(i18nConfig.monthsShort),
                ordinal: (n) => {
                    return n;
                }
            }).utc('z').local().tz(this.currentTimezone).format(format);
            return getDateTimeStringI18(convertedDate, 'mNumber');
        },
        isToday(date) {
            return dayjs(date).isSame(dayjs(), 'day');
        },
        isYesterday(date) {
            return dayjs(date).isSame(dayjs().subtract(1, 'day'), 'day');
        },
        isTomorrow(date) {
            return dayjs(date).isSame(dayjs().add(1, 'day'), 'day');
        },
        toDateFormat(date, format) {
            const i18nConfig = window.fluentFrameworkAdmin.i18.date_time_config;
            const formattedDate = dayjs(date).locale({
                name: 'fluent_date_time',
                weekdays: Object.values(i18nConfig.weekdays),
                weekdaysShort: Object.values(i18nConfig.weekdaysShort),
                months: Object.values(i18nConfig.months),
                monthsShort: Object.values(i18nConfig.monthsShort),
                ordinal: (n) => {
                    return n;
                }
            }).format(format);

            return getDateTimeStringI18(formattedDate, 'mNumber');
        },
        getTextFromSlug(slug) {
            return slug.split(/-|_/).map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },
        hasSupport(feature) {
            return !!window.fluentFrameworkAdmin.supported_features[feature];
        },
        $t(str) {
            // for testing
            if (!window.fluentFrameworkAdmin.trans[str]) {
                console.log(str);
            }
            
            return window.fluentFrameworkAdmin.trans[str] || str;
        },
        ucFirst(str) {
            if (!str) {
                return '';
            }
            return str.charAt(0).toUpperCase() + str.slice(1);
        },
        $currencyFormat(amount, isCents = false) {
            const currency_sign = window.fluentFrameworkAdmin.currency_sign;
            const {currency_position, decimal_points, number_format} = window.fluentFrameworkAdmin.currency_settings;

            if (!amount) {
                amount = 0;
            }

            if (isCents) {
                amount = amount / 100;
            }

            amount = parseFloat(amount).toFixed(decimal_points);

            const numberFormat = number_format == 'comma_separated'? 'en-US' : 'es-ES';

            amount = new Intl.NumberFormat(numberFormat, { minimumFractionDigits: decimal_points }).format(amount);

            const formatters = {
                'left': () => currency_sign + amount,
                'left_space': () => currency_sign + ' ' + amount,
                'right': () => amount + currency_sign,
                'right_space': () => amount + ' ' + currency_sign
            };
            
            return formatters[currency_position] ? formatters[currency_position]() : amount;
        },
        hasAccess(permission) {
            if (window.fluentFrameworkAdmin.me.is_admin) {
                return true;
            }

            // check if is array
            if (Array.isArray(permission)) {
                let hasAccess = false;
                permission.forEach((perm) => {
                    if (!window.fluentFrameworkAdmin.me.permissions.includes(perm)) {
                        hasAccess = true;
                    }
                });

                return hasAccess;
            }

            return window.fluentFrameworkAdmin.me.permissions.includes(permission);
        },
        applyFilters,
        addFilter
    }
});

app.config.globalProperties.$notify = ElNotification;
app.config.globalProperties.$confirm = ElMessageBox.confirm;
app.config.globalProperties.$alert = ElMessageBox.alert;
app.config.globalProperties.$prompt = ElMessageBox.prompt;

const router = createRouter({
    routes,
    history: createWebHashHistory()
});

app.use(router);
app.use(ElLoading);

app.mount('#fluent-framework-app');

router.afterEach((to, from) => {
    const activeMenu = to.meta.active_menu;
    jQuery('.fcal_nav li').removeClass('active_item');
    jQuery('.fcal_nav li.fframe_item_' + activeMenu).addClass('active_item');

    jQuery('.toplevel_page_fluent-booking li').removeClass('current');
    jQuery(".toplevel_page_fluent-booking li").find(`a[href*='#/${activeMenu}']`).parent().addClass("current");

    if (activeMenu == 'dashboard') {
        jQuery(".toplevel_page_fluent-booking li.wp-first-item").find("a[href*='fluent-booking']").parent().addClass("current");
    }

    if (to.meta.title) {
        jQuery('head title').text(to.meta.title + ' - FluentBooking'); // Change it with your app name
    }

});

if(_.noConflict) {
    window.lodash = _.noConflict();
}
