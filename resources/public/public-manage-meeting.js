import {request} from './request';
// document on load
document.addEventListener('DOMContentLoaded', function () {

    const formActions = {
        register() {
            this.initCancellation();
        },
        initCancellation() {
            // select form by id
            const form = document.getElementById('fcal_cancellation_form');
            // check if form exists
            if (!form) {
                return false;
            }

            // register form submit event
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                // get the form data
                const formData = new FormData(e.target);

                // Convert FormData to a plain object for easier logging
                const formDataObject = {};
                formData.forEach((value, key) => {
                    formDataObject[key] = value;
                });

                // send ajax request to server to action url
                request('POST', form.action, formDataObject)
                    .then(function (response) {

                    });
            });

        }
    };

    formActions.register();

});
