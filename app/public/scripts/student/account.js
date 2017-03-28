$(document).ready(function () {
    'use strict';

    registerForm('#infoform', '#infoformSubmit',
        {}, {},
        function () {return '/student/update';}, 'POST',
        function () {}, function () {window.location.replace('/user/dashboard');}
    );
});
