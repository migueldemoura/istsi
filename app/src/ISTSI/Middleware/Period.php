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
    private $period;

    public function __construct(ContainerInterface $c, $period)
    {
        $this->c = $c;
        $this->period = $period;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $settingsProgram = $this->c->get('settings')['program'];

        $validate = function ($periodStart, $periodEnd) {
            switch ($this->period) {
                case DateTime::BEFORE:
                    return DateTime::isBefore($periodEnd);
                case DateTime::BETWEEN:
                    return DateTime::isBetween($periodStart, $periodEnd);
                case DateTime::AFTER:
                    return DateTime::isAfter($periodEnd);
                default:
                    return false;
            }
        };

        if (!$validate($settingsProgram['period']['start'], $settingsProgram['period']['end'])) {
            if ($request->isXhr()) {
                return $response->withJson([
                    'status' => 'fail',
                    'data'   => 'period'
                ]);
            }
            //TODO
            $response->getBody()->write('PERIOD_OUTSIDE');
            return $response;
        }

        return $next($request, $response);
    }
}
