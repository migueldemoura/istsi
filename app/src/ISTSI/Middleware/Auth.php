<?php
declare(strict_types = 1);

namespace ISTSI\Middleware;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Auth
{
    protected $c;
    private $method;

    public function __construct(ContainerInterface $c, $method)
    {
        $this->c = $c;
        $this->method = $method;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $session = $this->c->get('session');

        if (!$session->isLogged($this->method)) {
            if ($request->isXhr()) {
                return $response->withJson([
                    'status' => 'fail',
                    'data'   => 'auth'
                ]);
            }

            return $response->withStatus(302)->withHeader('Location', '/session/expired');
        }

        return $next($request, $response);
    }
}
