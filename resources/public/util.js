import * as dayjs from 'dayjs';

const utc = require('dayjs/plugin/utc')
const timezone = require('dayjs/plugin/timezone')
dayjs.extend(utc);
dayjs.extend(timezone);

const request = function (method, url, data = {} = false) {
    const formData = new FormData();

    if (method === 'GET') {
        url += '?query_timestamp=' + Date.now();
        Object.keys(data).forEach(key => {
            url += `&${key}=${data[key]}`;
        });
    } else {
        data.query_timestamp = Date.now();
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });
    }

    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.responseType = 'json';
        xhr.open(method, url);

        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                resolve(xhr.response);
            } else {
                reject({
                    status: xhr.status,
                    statusText: xhr.statusText,
                    response: xhr.response
                });
            }
        };
        xhr.onerror = function () {
            reject({
                status: xhr.status,
                statusText: xhr.statusText
            });
        };
        xhr.send(formData);
    });
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
