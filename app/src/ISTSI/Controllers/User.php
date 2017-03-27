<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use ISTSI\Helpers\DateTime;
use ISTSI\Identifiers\Auth;
use ISTSI\Identifiers\Info;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class User
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function showPage(Request $request, Response $response, $args)
    {
        $session = $this->c->get('session');

        $page = $args['page'];

        switch ($session->getAuthType()) {
            case Auth::FENIX:
                $path = '/student/' . $page;
                break;
            case Auth::PASSWORDLESS:
                $path = '/company/' . $page;
                break;
            default:
                $path = '/session/expired';
        }
        return $response->withStatus(302)->withHeader('Location', $path);
    }
}
