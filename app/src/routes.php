<?php
declare(strict_types = 1);

use ISTSI\Middleware\Auth;
use ISTSI\Middleware\CSRF;

$app->get('/', ISTSI\Controllers\Front::class . ':showHome');

$app->group('/user', function () use ($app) {
    $app->get('/account', ISTSI\Controllers\User::class . ':showAccount')
        ->add(new Auth($app->getContainer()));
    $app->get('/dashboard', ISTSI\Controllers\User::class . ':showDashboard')
        ->add(new Auth($app->getContainer()));
    //TODO: should be $app->put, but see this https://github.com/slimphp/Slim/issues/1396
    $app->post('/update', ISTSI\Controllers\User::class . ':update')
        ->add(new CSRF($app->getContainer()))
        ->add(new Auth($app->getContainer()));
});

$app->group('/fenix', function () use ($app) {
    $app->get('/login', ISTSI\Controllers\Fenix::class . ':login');
    $app->get('/callback', ISTSI\Controllers\Fenix::class . ':callback');
    $app->get('/logout', ISTSI\Controllers\Fenix::class . ':logout')
        ->add(new CSRF($app->getContainer()));
});

$app->group('/submission', function () use ($app) {
    $app->get('/get/list', ISTSI\Controllers\Submission::class . ':getList');
    $app->get('/get/data/{proposal}', ISTSI\Controllers\Submission::class . ':getData');
    $app->get('/get/file/{proposal}/{file}', ISTSI\Controllers\Submission::class . ':getFile');
    $app->post('/create/{proposal}', ISTSI\Controllers\Submission::class . ':create');
    $app->post('/update/{proposal}', ISTSI\Controllers\Submission::class . ':update');
    $app->delete('/delete/{proposal}', ISTSI\Controllers\Submission::class . ':delete');
})->add(new CSRF($app->getContainer()))
  ->add(new Auth($app->getContainer()));
