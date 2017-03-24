$(document).ready(function () {
    'use strict';

    var user = 'student';

    registerForm('#infoform', '#infoformSubmit',
        {}, {},
        function () {return '/' + user + '/update';}, 'POST',
        user, function () {}, function () {window.location.replace('/' + user + '/dashboard');}
    );
});
