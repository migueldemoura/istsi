<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Front
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function showHome(Request $request, Response $response, $args)
    {
        $settingsProgram = $this->c->get('settings')['program'];

        $templateArgs = [
            'programName' => $settingsProgram['name'],
            'programYear' => $settingsProgram['year'],
            'email'       => $settingsProgram['email'],
            'facebook'    => $settingsProgram['facebook']
        ];

        return $this->c->get('renderer')->render($response, 'home/home.twig', $templateArgs);
    }
}
