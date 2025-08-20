import * as dayjs from 'dayjs';
import {request} from "./request";

const utc = require('dayjs/plugin/utc')
const timezone = require('dayjs/plugin/timezone')
dayjs.extend(utc);
dayjs.extend(timezone);

function dateTimeI18(dateTime, format = 'dddd, MMM DD') {
    const date = dayjs(dateTime).locale({
        name: 'fluent_date_time',
        weekdays: Object.values(window.fluentCalendarPublicVars.i18.date_time_config.weekdays),
        weekdaysShort: Object.values(window.fluentCalendarPublicVars.i18.date_time_config.weekdaysShort),
        months: Object.values(window.fluentCalendarPublicVars.i18.date_time_config.months),
        monthsShort: Object.values(window.fluentCalendarPublicVars.i18.date_time_config.monthsShort)
    }).format(format);

    return getDateTimeStringI18(date, 'mNumber');
}

export const util = {
    dayjs: dayjs,
    $get: function (url, data = {} = false) {
        return request('GET', url, data);
    },
    $post: function (url, data = {}, withUrl = false) {
        return request('POST', url, data);
    },
    $del: function (url, data = {}, withUrl = false) {
        return request('DELETE', url, data);
    },
    $put: function (url, data = {}, withUrl = false) {
        return request('PUT', url, data);
    },
    $patch: function (url, data = {}, withUrl = false) {
        return request('PATCH', url, data);
    },
    toDate: function (date, format) {
        return dayjs(date).format(format);
    },
    toTimezone: function (date, timezone, format) {
        return dayjs(date).utc('z').local().tz(timezone).format(format);
    },
    dateTimeI18
}

export const convertToText = function (obj) {
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

export const getErrorText = function (response, defaultMessage = 'Something is wrong!') {
    let errorMessage = '';
    if (typeof response === 'string') {
        errorMessage = response;
    } else if (response && response.message) {
        errorMessage = response.message;
    } else {
        errorMessage = convertToText(response);
    }
    if (!errorMessage) {
        errorMessage = i18(defaultMessage);
    }

    console.log(errorMessage, response);

    return errorMessage;
}

export const i18 = function (str) {
    let transString = window.fluentCalendarPublicVars?.i18[str];
    if (transString) {
        return transString;
    }

    let slug = str.toLowerCase();
    slug = slug.replace(/\s+/g, '-');

    return window.fluentCalendarPublicVars?.i18[slug] || str;
}

export const getDateTimeStringI18 = function (str, type) {
    if(!str) {
        return str;
    }
    const config = window.fluentCalendarPublicVars.i18.date_time_config;
    if (type == 'day') {
        return config.weekdays[str] || config.weekdaysShort[str] || str;
    }

    if (type == 'month') {
        return config.months[str] || config.monthsShort[str] || str;
    }
    if (type == 'mNumber') {
        str = str.toString();
        const numbers = config.numericSystem;
        const numberArr = numbers.split('_');
        const number = str.split('').map((s) => {
            return numberArr[s] || s;
        });

        return number.join('');
    }

    return str;
}

export const getCurrencyFormat = function (amount) {
    const { currency_sign, currency_position, decimal_points, number_format } = window.fluentCalendarPublicVars.currency_settings;
    if (!amount) {
        amount = 0;
    }

    amount = parseFloat(amount).toFixed(decimal_points);

    const numberFormat = number_format === 'comma_separated' ? 'en-US' : 'es-ES';

    amount = new Intl.NumberFormat(numberFormat, { minimumFractionDigits: decimal_points }).format(amount);

    const formatters = {
        'left': () => currency_sign + amount,
        'left_space': () => currency_sign + ' ' + amount,
        'right': () => amount + currency_sign,
        'right_space': () => amount + ' ' + currency_sign
    };

    return formatters[currency_position] ? formatters[currency_position]() : amount;
}

export {dateTimeI18};
