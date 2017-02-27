<?php
declare(strict_types = 1);

use ISTSI\Middleware\Auth;
use ISTSI\Middleware\CSRF;
use ISTSI\Middleware\Period;

$c = $app->getContainer();

$app->get('/', 'ISTSI\Controllers\Front:showHome');

$app->group('/user', function () use ($app, $c) {
    $app->get('/account', 'ISTSI\Controllers\User:showAccount')
        ->add(new Auth($c));
    $app->get('/dashboard', 'ISTSI\Controllers\User:showDashboard')
        ->add(new Auth($c));
    //TODO: should be $app->put, but see this https://github.com/slimphp/Slim/issues/1396
    $app->post('/update', 'ISTSI\Controllers\User:update')
        ->add(new CSRF($c))
        ->add(new Auth($c));
});

$app->group('/fenix', function () use ($app, $c) {
    $app->get('/connect', 'ISTSI\Controllers\Fenix:connect');
    $app->get('/login', 'ISTSI\Controllers\Fenix:login');
    $app->get('/logout', 'ISTSI\Controllers\Fenix:logout')
        ->add(new CSRF($c));
});

$app->group('/submission', function () use ($app, $c) {
    $app->get('/get/list', 'ISTSI\Controllers\Submission:getList');
    $app->get('/get/data/{proposal}', 'ISTSI\Controllers\Submission:getData');
    $app->get('/get/file/{proposal}/{file}', 'ISTSI\Controllers\Submission:getFile');
    $app->post('/create/{proposal}', 'ISTSI\Controllers\Submission:create')->add(new Period($c));
    $app->post('/update/{proposal}', 'ISTSI\Controllers\Submission:update')->add(new Period($c));
    $app->delete('/delete/{proposal}', 'ISTSI\Controllers\Submission:delete')->add(new Period($c));
})->add(new CSRF($c))
  ->add(new Auth($c));
