'use strict';

$('a').on('click', function(e){
    if ($(this).is('[disabled]')) {
        e.preventDefault();
    }
});

function getToken() {
    return $('#token').text();
}

function parseResponse(response, callback) {
    switch (response.status) {
        case 'success':
            return true;
        case 'fail':
            switch (response.data) {
                case 'auth':
                case 'csrf':
                    window.location.replace('/session/expired');
                    break;
                case 'period':
                    window.alert('Fora do período.');
                    break;
                case 'info':
                    window.location.replace('/user/account');
                    break;
                default:
                    (callback === undefined) ? window.alert('Ocorreu um erro.') : callback(response.data);
            }
            break;
        case 'error':
            window.alert('Ocorreu um erro.');
    }
    return false;
}

function registerForm(form, submitTrigger, valRules, valMessages, getEndpoint, verb, onTrigger, onSuccess, onFail) {
    var $form = $(form);

    $.validator.addMethod('filesize', function (value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    });

    $form.parents('.modal').on('hidden.bs.modal', function () {
        $form[0].reset();
        $form.validate().resetForm();
        $form.find('span.label').empty();
    });

    $(submitTrigger).on('click', function () {
        $form.submit();
    });
    $form.validate({
        ignore: '',
        rules: valRules,
        messages: valMessages,
        submitHandler: function (form, e) {
            e.preventDefault();
            onTrigger();
            ajaxRequest(getEndpoint(), verb, false, form, onSuccess, onFail);
        }
    });
}

function ajaxRequest(endpoint, verb, async, form, onSuccess, onFail) {
    var output = null;
    var data = {
        url: endpoint,
        type: verb,
        async: async,
        cache: false,
        dataType: 'json',
        beforeSend: function (request) {
            request.setRequestHeader('X-CSRF-Token', getToken());
        },
        success: function (response) {
            if (parseResponse(response, onFail)) {
                onSuccess(response);
                output = response.data;
            }
        },
        error: function () {
            window.alert('Ocorreu um erro.');
        }
    };

    if (form !== null) {
        data = $.extend(data, {
            data: new FormData($(form)[0]),
            contentType: false,
            processData: false
        });
    }

    $.ajax(data);

    return output;
}
