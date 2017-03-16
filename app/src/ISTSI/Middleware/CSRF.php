<?php
declare(strict_types = 1);

namespace ISTSI\Middleware;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CSRF
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $session = $this->c->get('session');

        if (!$session->hasValidToken($request->getParam('csrf_token')) &&
            !$session->hasValidToken($request->getParam('state')) &&
            !$session->hasValidToken($request->getHeaderLine('X-CSRF-Token'))
        ) {
            if ($request->isXhr()) {
                return $response->withJson([
                    'status' => 'fail',
                    'data'   => 'csrf'
                ]);
            }
            //TODO
            $response->getBody()->write('E_CSRF_TOKEN_INVALID');
            return $response;
        }

        return $next($request, $response);
    }
}
