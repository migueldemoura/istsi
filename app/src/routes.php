<?php
declare(strict_types = 1);

use ISTSI\Middleware\Auth;
use ISTSI\Middleware\CSRF;

$app->get('/', 'ISTSI\Controllers\Front:showHome');

$app->group('/user', function () use ($app) {
    $app->get('/account', 'ISTSI\Controllers\User:showAccount')
        ->add(new Auth($app->getContainer()));
    $app->get('/dashboard', 'ISTSI\Controllers\User:showDashboard')
        ->add(new Auth($app->getContainer()));
    //TODO: should be $app->put, but see this https://github.com/slimphp/Slim/issues/1396
    $app->post('/update', 'ISTSI\Controllers\User:update')
        ->add(new CSRF($app->getContainer()))
        ->add(new Auth($app->getContainer()));
});

$app->group('/fenix', function () use ($app) {
    $app->get('/connect', 'ISTSI\Controllers\Fenix:connect');
    $app->get('/login', 'ISTSI\Controllers\Fenix:login');
    $app->get('/logout', 'ISTSI\Controllers\Fenix:logout')
        ->add(new CSRF($app->getContainer()));
});

$app->group('/submission', function () use ($app) {
    $app->get('/get/list', 'ISTSI\Controllers\Submission:getList');
    $app->get('/get/data/{proposal}', 'ISTSI\Controllers\Submission:getData');
    $app->get('/get/file/{proposal}/{file}', 'ISTSI\Controllers\Submission:getFile');
    $app->post('/create/{proposal}', 'ISTSI\Controllers\Submission:create');
    $app->post('/update/{proposal}', 'ISTSI\Controllers\Submission:update');
    $app->delete('/delete/{proposal}', 'ISTSI\Controllers\Submission:delete');
})->add(new CSRF($app->getContainer()))
  ->add(new Auth($app->getContainer()));
