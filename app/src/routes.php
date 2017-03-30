<?php
declare(strict_types = 1);

use ISTSI\Identifiers\Auth as IdentifiersAuth;
use ISTSI\Middleware\Auth;
use ISTSI\Middleware\CSRF;
use ISTSI\Middleware\Info;
use ISTSI\Middleware\Period;

$c = $app->getContainer();

$app->get('/', 'ISTSI\Controllers\Front:showHome');

$app->group('/student', function () use ($app, $c) {
    $app->get('/account', 'ISTSI\Controllers\Student:showAccount');
    $app->get('/dashboard', 'ISTSI\Controllers\Student:showDashboard')
        ->add(new Info($c, IdentifiersAuth::FENIX));
    //TODO: should be $app->put, but see this https://github.com/slimphp/Slim/issues/1396
    $app->post('/update', 'ISTSI\Controllers\Student:update')
        ->add(new CSRF($c));
})->add(new Auth($c, IdentifiersAuth::FENIX));

$app->group('/company', function () use ($app, $c) {
    $app->group('', function () use ($app, $c) {
        $app->get('/account', 'ISTSI\Controllers\Company:showAccount');
        $app->get('/dashboard', 'ISTSI\Controllers\Company:showDashboard')
            ->add(new Info($c, IdentifiersAuth::PASSWORDLESS));
        //TODO: should be $app->put, but see this https://github.com/slimphp/Slim/issues/1396
        $app->post('/update', 'ISTSI\Controllers\Company:update')
            ->add(new CSRF($c));
    })->add(new Auth($c, IdentifiersAuth::PASSWORDLESS));

    $app->get('/login', 'ISTSI\Controllers\Company:showLogin');
});

$app->group('/auth', function () use ($app, $c) {
    $app->group('/fenix', function () use ($app, $c) {
        $app->get('/connect', 'ISTSI\Controllers\Auth\Fenix:connect');
        $app->get('/login', 'ISTSI\Controllers\Auth\Fenix:login');
        $app->get('/logout', 'ISTSI\Controllers\Auth\Fenix:logout');
    })->add(new CSRF($c));

    $app->group('/passwordless', function () use ($app, $c) {
        $app->post('/generate', 'ISTSI\Controllers\Auth\PasswordLess:generate');
        $app->get('/login', 'ISTSI\Controllers\Auth\PasswordLess:login');
        $app->get('/logout', 'ISTSI\Controllers\Auth\PasswordLess:logout')
            ->add(new CSRF($c));
    });
});

$app->group('/submission', function () use ($app, $c) {
    $app->get('/get/list', 'ISTSI\Controllers\Submission:getList');
    $app->get('/get/data/{proposal}', 'ISTSI\Controllers\Submission:getData');
    $app->get('/get/file/{proposal}/{file}', 'ISTSI\Controllers\Submission:getFile');
    $app->post('/create/{proposal}', 'ISTSI\Controllers\Submission:create')->add(new Period($c));
    //TODO: should be $app->put, but see this https://github.com/slimphp/Slim/issues/1396
    $app->post('/update/{proposal}', 'ISTSI\Controllers\Submission:update')->add(new Period($c));
    $app->delete('/delete/{proposal}', 'ISTSI\Controllers\Submission:delete')->add(new Period($c));
})->add(new Info($c, IdentifiersAuth::FENIX))
  ->add(new CSRF($c))
  ->add(new Auth($c, IdentifiersAuth::FENIX));

$app->group('/proposal', function () use ($app, $c) {
    $app->group('', function () use ($app, $c) {
        $app->get('/get/list', 'ISTSI\Controllers\Proposal:getList');
        $app->post('/create', 'ISTSI\Controllers\Proposal:create')
            ->add(new Period($c, false));
        //TODO: should be $app->put, but see this https://github.com/slimphp/Slim/issues/1396
        $app->post('/update/{proposal}', 'ISTSI\Controllers\Proposal:update')
            ->add(new Period($c, false));
        $app->delete('/delete/{proposal}', 'ISTSI\Controllers\Proposal:delete')
            ->add(new Period($c, false));
    })->add(new Info($c, IdentifiersAuth::PASSWORDLESS))
      ->add(new CSRF($c))
      ->add(new Auth($c, IdentifiersAuth::PASSWORDLESS));

    $app->get('/get/data/{proposal}', 'ISTSI\Controllers\Proposal:getData');
});

$app->get('/user/{page:account|dashboard|update}', 'ISTSI\Controllers\User:showPage')
    ->add(new Auth($c, IdentifiersAuth::ALL));

$app->get('/course/get', 'ISTSI\Controllers\Course:get')
    ->add(new CSRF($c))
    ->add(new Auth($c, IdentifiersAuth::PASSWORDLESS));

$app->get('/session/expired', 'ISTSI\Controllers\Session:expired');
