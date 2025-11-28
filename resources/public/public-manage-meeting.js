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
                        errorElement.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-900 dark:text-blue-200" data-testid="info" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg> ${message}
                        `;
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
