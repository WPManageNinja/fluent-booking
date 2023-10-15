import * as dayjs from 'dayjs';
import {request} from "./request";

const utc = require('dayjs/plugin/utc')
const timezone = require('dayjs/plugin/timezone')
dayjs.extend(utc);
dayjs.extend(timezone);

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
    }
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

export const getErrorText = function (response) {
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

    console.log(errorMessage, response);

    return errorMessage;
}

export const i18 = function (str) {
    let transString = window.fluentCalendarPublicVars?.i18[str];
    let slug = str.toLowerCase();
    slug = slug.replace(/\s+/g, '-');
    if (transString) {
        return transString;
    } else if (window.fluentCalendarPublicVars?.i18[slug]) {
        return str;
    }
    return str;
}
