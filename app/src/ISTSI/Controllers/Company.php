<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Company
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function showAccount(Request $request, Response $response, $args)
    {
        die(var_dump($_SESSION));
    }

    public function showDashboard(Request $request, Response $response, $args)
    {

    }

    public function update(Request $request, Response $response, $args)
    {

    }
}
