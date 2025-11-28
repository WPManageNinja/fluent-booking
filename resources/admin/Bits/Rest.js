const request = function (method, route, data = {}, isFile = false) {
    const url = `${window.fluentFrameworkAdmin.rest.url}/${route}`;
    const headers = {'X-WP-Nonce': window.fluentFrameworkAdmin.rest.nonce};

    if (['PUT', 'PATCH', 'DELETE'].indexOf(method.toUpperCase()) !== -1 && !isFile) {
        headers['X-HTTP-Method-Override'] = method;
        method = 'POST';
    }

    data.query_timestamp = Date.now();

    const ajaxData = {
        url: url,
        type: method,
        headers: headers,
        data: data
    };

    if (isFile) {
        ajaxData.processData = false;
        ajaxData.contentType = false;
    }

    return new Promise((resolve, reject) => {
        window.jQuery.ajax(ajaxData)
            .then(response => resolve(response))
            .fail(errors => reject(errors.responseJSON));
    });
}

export default {
    get(route, data = {}) {
        return request('GET', route, data);
    },
    post(route, data = {}) {
        return request('POST', route, data);
    },
    delete(route, data = {}) {
        return request('DELETE', route, data);
    },
    put(route, data = {}) {
        return request('PUT', route, data);
    },
    patch(route, data = {}) {
        return request('PATCH', route, data);
    },
    uploadFile(route, data = {}) {
        return request('POST', route, data, true);
    }
};

jQuery(($) => {
    (() => {
        $.ajaxSetup({
            success: function(response, status, xhr) {
                const nonce = xhr.getResponseHeader('X-WP-Nonce');
                if (nonce) {
                    window.fluentFrameworkAdmin.rest.nonce = nonce;
                }
            }
        });
    })();
});
