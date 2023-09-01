import BookingApp from './BookingApp.svelte';
import './styles.scss';
import './saas.scss';

const calendarApps = document.querySelectorAll('.fluentform_calendar_app');

const $ = jQuery;
const showErrorMessages = function (res) {
    const el = $('#' + res.id);
    el.nextAll('div.error').remove();
    el.addClass('ff-el-is-error')
      .find(':first-child')
      .addClass('border-danger');

    let errors = res.messages;
    if (typeof errors == 'string') {
        errors = [errors];
    }
    $.each(errors, function (index, messages) {
        if (typeof messages == 'string') {
            messages = [messages];
        }
        $.each(messages, function (key, message) {
            const msgDiv = $('<div/>', {
                class: 'error text-danger',
                text: message
            });
            el.after(msgDiv);
        });
    });
}

const scrollToFirstError = function (formInstance, animDuration) {
    const firstError = $('.' + formInstance).find('.ff-el-is-error').first();
    if (firstError.length) {
        $('html, body').delay(animDuration).animate({
            scrollTop: firstError.offset().top - (!!$('#wpadminbar') ? 32 : 0) - 20
        }, animDuration);
    }
};

const handleSubmissionFailed = function (formInstance) {
    $('.' + formInstance).on('fluentform_submission_failed', function(event, data) {
        $('.' + formInstance + ' div.border-danger').removeClass('border-danger');
        const response = data.response;
        if (response.responseJSON.id) {
            showErrorMessages(response.responseJSON);
            scrollToFirstError(formInstance, 150);
        }
    });
}


if (calendarApps.length) {
    calendarApps.forEach((item) => {
        const elem = item;
        const elemId = elem.dataset.element_id;
        if (elem.dataset.app_booted) {
            return;
        }
        if (elemId) {
            elem.innerHTML = '';
            const appData = window['fcal_public_vars_' + elemId];
            appData.is_fluentform = true;
            appData.id = elemId;

            new BookingApp({
                target: elem,
                props: {
                    appData: appData,
                }
            });

            elem.classList.remove('fcal_loading');
            elem.dataset.app_booted = true;
            
            handleSubmissionFailed(appData.form_instance);
        }
    });
}
