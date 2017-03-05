<?php
declare(strict_types = 1);

$c = $app->getContainer();

$c['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];

    $renderer = new Slim\Views\Twig($settings['templatePath'], [
        'cache' => $settings['cachePath'],
        'auto_reload' => true,
        'strict_variables' => true,
        'autoescape' => 'html'
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $renderer->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));

    return $renderer;
};

$c['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];

    return new ISTSI\Services\Logger($settings['name'], $settings['path'], $settings['level']);
};

if (!$c->get('settings')['displayErrorDetails']) {
    $c['errorHandler'] = $c['phpErrorHandler'] = function ($c) {
        return function ($request, $response, $exception) use ($c) {
            $c->get('logger')->addRecord(
                ['ERROR', $exception->getCode() . ' - ' . $exception->getMessage()],
                ['trace' => $exception->getTrace()]
            );

            return $c['response']->withStatus(500)
                ->withHeader('Content-Type', 'text/html')
                ->write('Something went wrong!');
        };
    };
}

$c['session'] = function () {
    return new ISTSI\Services\Session();
};

$c['fenix'] = function ($c) {
    return new ISTSI\Services\Fenix($c);
};

$c['database'] = function ($c) {
    $settings = $c->get('settings')['database'];
    $config = new Spot\Config();

    $config->addConnection('mysql', [
        'driver'   => $settings['driver'],
        'host'     => $settings['host'],
        'dbname'   => $settings['database'],
        'user'     => $settings['username'],
        'password' => $settings['password'],
        'charset'  => $settings['charset']
    ]);

    return new Spot\Locator($config);
};

$c['filemanager'] = function ($c) {
    return new ISTSI\Services\FileManager($c);
};

$c['mailer'] = function ($c) {
    $settings = $c->get('settings')['mailer'];

    return new ISTSI\Services\Mailer(
        $settings['host'],
        $settings['port'],
        $settings['username'],
        $settings['password'],
        $settings['fromName'],
        $settings['fromEmail']
    );
};

$c['validator'] = function () {
    return Valitron\Validator::class;
};
