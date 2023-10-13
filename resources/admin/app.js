import {createApp} from 'vue'
import {createRouter, createWebHashHistory} from 'vue-router';
import { routes } from './routes';
import DashboardApplication from "./Application.vue";
import Rest from './Bits/Rest.js';
import {ElNotification, ElLoading, ElMessageBox} from 'element-plus'
import Storage from '@/Bits/Storage';
import * as dayjs from 'dayjs'
import { Plus, Delete, Location } from "@element-plus/icons-vue";
import Errors from '@common/Errors';
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

const app = createApp(DashboardApplication);

const Icons = [Plus, Delete, Location];
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
                errorMessage = 'Something is wrong!';
            }

            this.$notify({
                type: 'error',
                title: 'Error',
                offset: 20,
                message: errorMessage,
                dangerouslyUseHTMLString: true
            });
        },
        $handleSuccess(response) {
            let successMsg = 'Success';
            if (typeof response === 'string') {
                successMsg = response;
            } else if (response && response.message) {
                successMsg = response.message;
            } else {
                successMsg = convertToText(response);
            }

            this.$notify({
                type: 'success',
                title: 'Success',
                offset: 20,
                message: successMsg,
                dangerouslyUseHTMLString: true
            });
        },
        toCurrentTimezone(date, format) {
            return dayjs(date).utc('z').local().tz(this.currentTimezone).format(format);
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
            return dayjs(date).format(format);
        },
        getTextFromSlug(slug) {
            return slug.split(/-|_/).map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },
        hasSupport(feature) {
            return !!window.fluentFrameworkAdmin.supported_features[feature];
        },
        $t(str) {
            // let transString = window.FluentCalendarApp.form_settings_str[str];
            // if (transString) {
            //     return transString;
            // }
            return str;
        },
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
    jQuery('.fframe_menu li').removeClass('active_item');
    jQuery('.fframe_menu li.fframe_item_' + activeMenu).addClass('active_item');

    jQuery('.toplevel_page_fluent_frame li').removeClass('current'); // change fluent_frame with your plugin slug
    jQuery('.toplevel_page_fluent_frame li.fluent_frame_' + activeMenu).addClass('current'); // change fluent_frame with your plugin slug

    if(to.meta.title) {
        jQuery('head title').text(to.meta.title + ' - FluentBooking'); // Change it with your app name
    }

});
