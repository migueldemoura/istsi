$(document).ready(function () {
    'use strict';

    var user = 'company';

    registerForm('#infoform', '#infoformSubmit',
        {}, {},
        function () {return '/' + user + '/update';}, 'POST',
        user, function () {}, function () {window.location.replace('/' + user + '/dashboard');}
    );
});