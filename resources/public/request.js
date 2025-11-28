function buildFormData(formData, data, parentKey) {
    if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File) && !(data instanceof Blob)) {
        Object.keys(data).forEach(key => {
            buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
        });
    } else {
        const value = data == null ? '' : data;

        formData.append(parentKey, value);
    }
    return formData;
}

export const request = function (method, url, data = {} = false) {
    let formData = new FormData();

    if (method === 'GET') {
        // add query_timestamp to url check if it already has nay get params
        url = url.indexOf('?') > -1 ? url : url + '?';
        url += 'query_timestamp=' + Date.now();
        Object.keys(data).forEach(key => {
            url += `&${key}=${data[key]}`;
        });
    } else {
        data.query_timestamp = Date.now();
        formData = buildFormData(formData, data);
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
