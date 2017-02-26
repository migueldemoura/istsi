<?php
declare(strict_types = 1);

namespace ISTSI\Middleware;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Auth
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $session = $this->c->get('session');

        if (!$session->isLogged()) {
            //TODO:
            $response->getBody()->write('E_FENIX_INVALID');
            return $response;
        }

        return $next($request, $response);
    }
}
