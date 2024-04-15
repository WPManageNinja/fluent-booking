import {request} from './request';
import { i18 } from './util';
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

                // Add Class to fcal_cancel_btn class element
                const cancelBtn = document.querySelector('.fcal_cancel_btn');
                cancelBtn.classList.add('fcal_cancel_btn_loading');
                cancelBtn.setAttribute('disabled', 'disabled');

                // remove error message if exists
                const errorMessage = document.querySelector('.fcal_error_message');
                if (errorMessage) {
                    errorMessage.remove();
                }

                // send ajax request to server to action url
                request('POST', form.action, formDataObject)
                    .then(function (response) {
                        if(response.message) {
                            // replace the form with success message
                            form.innerHTML = response.message;
                        }
                    })
                    .catch((errors) => {
                        let message = errors.response?.message || i18('Something is wrong!');
                        // add error message to the form bottom
                        const errorElement = document.createElement('div');
                        errorElement.classList.add('fcal_error_message');
                        errorElement.innerHTML = message;
                        form.appendChild(errorElement);
                    })
                    .finally(() => {
                        cancelBtn.classList.remove('fcal_cancel_btn_loading');
                        cancelBtn.removeAttribute('disabled');
                    });
            });

        }
    };

    formActions.register();

});
