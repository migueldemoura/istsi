$(document).ready(function () {
    'use strict';

    $('#submit').on('click', function () {
        $('.message').text('...').addClass('text-success').removeClass('text-danger');
        $('#loginform').submit();
    });

    $('#loginform').validate({
        submitHandler: function (form, e) {
            e.preventDefault();

            $.ajax({
                url: '/auth/passwordless/generate',
                type: 'POST',
                data: $(form).serialize(),
                cache: false,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('.message').text('Uma mensagem com link de login foi enviada.')
                                     .addClass('text-success').removeClass('text-danger');
                    } else if (response.status === 'fail') {
                        if (response.data === 'email') {
                            $('.message').text('Não existe uma conta com o email dado.')
                                         .addClass('text-danger').removeClass('text-success');
                        } else if (response.data === 'duplicate') {
                            $('.message').text(
                                'Um link de login foi criado há menos de 15 minutos. ' +
                                'Utilize o link enviado.'
                            ).addClass('text-danger').removeClass('text-success');
                        } else {
                            window.alert('Erro');
                        }
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
