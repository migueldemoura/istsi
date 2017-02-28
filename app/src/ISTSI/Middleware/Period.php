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
    private $inRange;

    public function __construct(ContainerInterface $c, $inRange = true)
    {
        $this->c = $c;
        $this->inRange = $inRange;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $settingsProgram = $this->c->get('settings')['program'];
        $periodStart = $settingsProgram['period']['start'];
        $periodEnd = $settingsProgram['period']['end'];


        if ($this->inRange && !DateTime::isBetween($periodStart, $periodEnd) ||
            !$this->inRange && !DateTime::isAfter($periodEnd)
        ) {
            //TODO: Better handling
            $response->getBody()->write('E_PERIOD_OUTSIDE');
            return $response;
        }

        return $next($request, $response);
    }
}
