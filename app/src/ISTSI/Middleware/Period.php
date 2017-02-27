<?php
declare(strict_types = 1);

namespace ISTSI\Middleware;

use ISTSI\Helpers\DateTime;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Period
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $settingsProgram = $this->c->get('settings');

        if (!DateTime::isBetween($settingsProgram['period']['start'], $settingsProgram['period']['end'])) {
            //TODO: Better handling
            $response->getBody()->write('E_PERIOD_OUTSIDE');
            return $response;
        }

        return $next($request, $response);
    }
}
