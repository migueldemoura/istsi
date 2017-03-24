$(document).ready(function () {
    'use strict';

    var user = 'company';

    var $message = $('.message');

    registerForm('#loginform', '#loginformsubmit',
        {}, {},
        function () {return '/auth/passwordless/generate';}, 'POST',
        user, function () {
            $message.text('A processar...').addClass('text-success').removeClass('text-danger');
        }, function () {
            $message.text('Uma mensagem com link de login foi enviada.')
                         .addClass('text-success').removeClass('text-danger');
        }, function (data) {
            switch (data) {
                case 'email':
                    $message.text('Não existe uma conta com o email dado.')
                        .addClass('text-danger').removeClass('text-success');
                    break;
                case 'duplicate':
                    $message.text(
                        'Um link de login foi criado há menos de 30 minutos. ' +
                        'Utilize o link enviado.'
                    ).addClass('text-danger').removeClass('text-success');
                    break;
                default:
                    window.alert('Ocorreu um erro.');
            }
        }
    );
});
