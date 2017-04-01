<?php
declare(strict_types = 1);

namespace ISTSI\Middleware;

use ISTSI\Identifiers\Auth;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Info
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
        $database = $this->c->get('database');
        $session = $this->c->get('session');

        $uid = $session->getUid();

        $validate = function ($database, $uid) {
            switch ($this->method) {
                case Auth::FENIX:
                    $studentMapper = $database->mapper('\ISTSI\Entities\Student');
                    $student = $studentMapper->get($uid);
                    return $student->phone !== null;
                case Auth::PASSWORDLESS:
                    $companyMapper = $database->mapper('\ISTSI\Entities\Company');
                    $company = $companyMapper->first(['email' => $uid]);
                    return !in_array(null, [$company->name, $company->representative, $company->phone], true);
                default:
                    return true;
            }
        };

        if (!$validate($database, $uid)) {
            if ($request->isXhr()) {
                return $response->withJson([
                    'status' => 'fail',
                    'data'   => 'info'
                ]);
            }
            return $response->withStatus(302)->withHeader('Location', '/user/account');
        }

        return $next($request, $response);
    }
}
