<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Course
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function get(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');

        $courseMapper = $database->mapper('\ISTSI\Database\Entities\Course');

        $courses = $courseMapper->all()->toArray();
        $data['acronym'] = array_column($courses, 'acronym');
        $data['name'] = array_column($courses, 'name');

        return $response->withJson([
            'status' => 'success',
            'data'   => $data
        ]);
    }
}
