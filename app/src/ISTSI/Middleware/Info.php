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

        $valid = false;
        $uid = $session->getUid();
        if ($this->method === Auth::FENIX) {
            $studentMapper = $database->mapper('\ISTSI\Entities\Student');
            $student = $studentMapper->get($uid);

            if ($student->phone !== null) {
                $valid = true;
            }
        } elseif ($this->method === Auth::PASSWORDLESS) {
            $companyMapper = $database->mapper('\ISTSI\Entities\Company');
            $company = $companyMapper->first(['email' => $uid]);

            if ($company->name !== null && $company->representative !== null && $company->phone !== null) {
                $valid = true;
            }
        } else {
            $valid = true;
        }

        if (!$valid) {
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
