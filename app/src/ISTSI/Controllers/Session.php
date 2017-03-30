<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Session
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function expired(Request $request, Response $response, $args)
    {
        $settingsProgram = $this->c->get('settings')['program'];

        $templateArgs = [
            'programName'  => $settingsProgram['name'],
            'programYear'  => $settingsProgram['year'],
            'title'        => 'Sessão expirou',
            'message'      => 'Por favor, inicie sessão novamente. Será redirecionado brevemente.'
        ];

        return $this->c->get('renderer')->render($response, 'session/expired.twig', $templateArgs);
    }
}
