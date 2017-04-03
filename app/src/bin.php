<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/settings.php';
$app = new \Slim\App($settings);

require __DIR__ . '/dependencies.php';

$c = $app->getContainer();

if ($argv[1] === 'migrations') {
    $migrations = new \ISTSI\Database\Migrations\Migrations($c);
    $migrations->migrate();
}
