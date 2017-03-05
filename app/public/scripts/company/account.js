$(document).ready(function () {
    'use strict';

    $('.dropdown a[href="#"]').on('click', function (event) {
        event.preventDefault();
    });

    function getTokenValue() {
        return $('#token').text();
    }

    $(document).bind('ajaxSend', function (elm, xhr) {
        xhr.setRequestHeader('X-CSRF-Token', getTokenValue());
    });

    $('#submit').on('click', function () {
        $('#infoform').submit();
    });

    $('#infoform').validate({
        rules: {
            name: {
                required: true
            },
            representative: {
                required: true
            },
            email: {
                required: true
            },
            phone: {
                required: true,
                digits: true
            }
        },
        messages: {
            name: {
                required: 'Coloque o nome da empresa.'
            },
            representative: {
                required: 'Coloque o nome do responsável pelo estágio.'
            },
            email: {
                required: 'Coloque o email do responsável pelo estágio.'
            },
            phone: {
                required: 'Coloque o número de telemóvel do responsável pelo estágio.',
                digits: 'Coloque apenas dígitos'
            }
        },
        submitHandler: function (form, e) {
            e.preventDefault();

            $.ajax({
                url: '/company/update',
                type: 'POST',
                data: $(form).serialize(),
                async: false,
                cache: false,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        window.location.replace('/company/dashboard');
                    } else {
                        window.alert('Erro');
                    }
                },
                error: function () {
                    window.alert('Erro');
                }
            });
        }
    });
});
