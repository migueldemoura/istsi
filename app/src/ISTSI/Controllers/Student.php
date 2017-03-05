<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use ISTSI\Helpers\DateTime;
use ISTSI\Identifiers\Info;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Student
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function showAccount(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        $studentMapper = $database->mapper('\ISTSI\Entities\Student');
        $uid = $session->getUid();
        $student = $studentMapper->get($uid);

        $templateArgs = [
            'programName'  => $settingsProgram['name'],
            'programYear'  => $settingsProgram['year'],
            'uid'          => $uid,
            'logout'       => '/auth/fenix/logout',
            'email'        => $student->email,
            'token'        => $session->getToken()
        ];

        return $this->c->get('renderer')->render($response, 'student/account.twig', $templateArgs);
    }

    public function showDashboard(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        // Check if student has already provided its phone
        $studentMapper = $database->mapper('\ISTSI\Entities\Student');
        $uid = $session->getUid();
        $student = $studentMapper->get($uid);

        if ($student->phone === null) {
            return $response->withStatus(302)->withHeader('Location', '/student/account');
        }

        $templateArgs = [
            'programName' => $settingsProgram['name'],
            'programYear' => $settingsProgram['year'],
            'uid'         => $uid,
            'logout'      => '/auth/fenix/logout',
            'token'       => $session->getToken(),
            'onPeriod'    => DateTime::isBetween(
                $settingsProgram['period']['start'],
                $settingsProgram['period']['end']
            )
        ];

        return $this->c->get('renderer')->render($response, 'student/dashboard.twig', $templateArgs);
    }

    public function update(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');

        $studentMapper = $database->mapper('\ISTSI\Entities\Student');
        $uid = $session->getUid();

        $student = $studentMapper->get($uid);
        $student->email = $request->getParsedBody()['email'];
        $student->phone = $request->getParsedBody()['phone'];
        if (!$studentMapper->update($student)) {
            die('INVALID_DATA');
        }

        $logger->addRecord(Info::ACCOUNT_INFO, ['uid' => $uid]);

        $data = [
            'status' => 'success',
            'data'   => ''
        ];
        return $response->withJson($data);
    }
}
