import * as dayjs from 'dayjs';
const utc = require('dayjs/plugin/utc')
const timezone = require('dayjs/plugin/timezone')
dayjs.extend(utc);
dayjs.extend(timezone);

const request = function (method, route, data = {}) {
    let url = `${window.fluentCalendarPublicVars.rest.url}/${route}`;

    const headers = {
        'X-WP-Nonce': window.fluentCalendarPublicVars.rest.nonce
    };

    if (['PUT', 'PATCH', 'DELETE'].indexOf(method.toUpperCase()) !== -1) {
        headers['X-HTTP-Method-Override'] = method;
        method = 'POST';
    }

    const formData = new FormData();

    if(method === 'GET') {
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

        for (let key in headers) {
            xhr.setRequestHeader(key, headers[key]);
        }

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
    $get: function (route, data = {}) {
        return request('GET', route, data);
    },
    $post: function (route, data = {}) {
        return request('POST', route, data);
    },
    $del: function (route, data = {}) {
        return request('DELETE', route, data);
    },
    $put: function (route, data = {}) {
        return request('PUT', route, data);
    },
    $patch: function (route, data = {}) {
        return request('PATCH', route, data);
    },
    toDate: function (date, format) {
        return dayjs(date).format(format);
    }
}
