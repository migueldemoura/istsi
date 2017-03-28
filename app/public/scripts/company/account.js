$(document).ready(function () {
    'use strict';

    registerForm('#infoform', '#infoformSubmit',
        {}, {},
        function () {return '/company/update';}, 'POST',
        function () {}, function () {window.location.replace('/user/dashboard');}
    );
});